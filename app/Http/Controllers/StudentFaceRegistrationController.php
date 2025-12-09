<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Student_Photos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class StudentFaceRegistrationController extends Controller
{
    /**
     * Tạo S3 Presigned URLs hàng loạt để đăng ký khuôn mặt.
     * Người dùng phải tự đổi tên tệp thành [student_code].jpg
     */
    public function generateUploadUrls(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        // Mong đợi nhận một mảng 'files', mỗi phần tử là một object
        $validated = $request->validate([
            'files' => 'required|array|max:120', // Giới hạn 120 tệp một lúc
            'files.*.file_name' => 'required|string|max:120',
            'files.*.file_type' => 'required|string|in:image/jpeg,image/png'
        ]);

        $bucket = config('filesystems.disks.s3.bucket');
        $uploadUrls = []; // Mảng chứa kết quả trả về

        try {
            // 2. Tạo S3 Client
            $s3Client = new S3Client([
                'region' => config('filesystems.disks.s3.region'),
                'version' => 'latest',
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ]
            ]);
            
            // 3. Lặp qua mỗi tệp được yêu cầu
            foreach ($validated['files'] as $file) {
                $fileName = $file['file_name'];
                $fileType = $file['file_type'];

                // Tách student_code từ tên tệp (ví dụ: "DH52025001.jpg" -> "DH52025001")
                $studentCode = pathinfo($fileName, PATHINFO_FILENAME);
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // Kiểm tra format: DH + 8 chữ số
                if (empty($studentCode) || !preg_match('/^DH\d{8}$/i', $studentCode) || !in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $uploadUrls[] = [
                        'file_name' => $fileName,
                        'success' => false,
                        'message' => 'Tên tệp phải có định dạng DHxxxxxxxx.jpg (x là chữ số) và định dạng ảnh hợp lệ (jpg/jpeg/png).'
                    ];
                    continue; // Bỏ qua tệp này
                }

                // An toàn hóa tên tệp khi lưu trên S3
                $safeFileName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
                $s3Key = "images_to_register/{$safeFileName}";

                // 4. Tạo Presigned Request cho tệp này
                $cmd = $s3Client->getCommand('PutObject', [
                    'Bucket' => $bucket,
                    'Key' => $s3Key,
                    'ContentType' => $fileType
                ]);

                // 5. Tạo URL (hợp lệ trong 15 phút)
                $presignedRequest = $s3Client->createPresignedRequest($cmd, '+15 minutes');
                $presignedUrl = (string) $presignedRequest->getUri();

                // 6. Thêm vào mảng kết quả
                $uploadUrls[] = [
                    'file_name' => $fileName,
                    'success' => true,
                    'upload_url' => $presignedUrl
                ];
            }

            // 7. Trả về mảng các URL
            return response()->json([
                'success' => true,
                'message' => 'Tạo URL tải lên thành công.',
                'data' => $uploadUrls
            ]);

        } catch (AwsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo URL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Callback sau khi upload thành công lên S3
     * Lưu thông tin vào bảng student_photos
     */
    public function confirmUpload(Request $request)
    {
        try {
            $validated = $request->validate([
                'uploads' => 'required|array',
                'uploads.*.student_code' => 'required|string|regex:/^DH\d{8}$/i',
                'uploads.*.file_name' => 'required|string',
            ]);

            $results = [];
            $bucket = config('filesystems.disks.s3.bucket');
            $region = config('filesystems.disks.s3.region');

            foreach ($validated['uploads'] as $upload) {
                $studentCode = strtoupper($upload['student_code']);
                $fileName = $upload['file_name'];
                
                // Kiểm tra sinh viên có tồn tại không
                $student = Student::where('student_code', $studentCode)->first();
                if (!$student) {
                    $results[] = [
                        'student_code' => $studentCode,
                        'file_name' => $fileName,
                        'success' => false,
                        'message' => 'Không tìm thấy sinh viên'
                    ];
                    continue;
                }

                // Tạo S3 URL
                $safeFileName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
                $s3Key = "images_to_register/{$safeFileName}";
                $s3Url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$s3Key}";

                // Lưu vào database
                try {
                    $photo = Student_Photos::create([
                        'student_code' => $studentCode,
                        'image_url' => $s3Url,
                    ]);

                    $results[] = [
                        'student_code' => $studentCode,
                        'file_name' => $fileName,
                        'success' => true,
                        'message' => 'Đã lưu thông tin ảnh'
                    ];

                    Log::info("Saved photo for student {$studentCode}: {$s3Url}");
                } catch (\Exception $e) {
                    $results[] = [
                        'student_code' => $studentCode,
                        'file_name' => $fileName,
                        'success' => false,
                        'message' => 'Lỗi lưu database: ' . $e->getMessage()
                    ];
                    Log::error("Failed to save photo for {$studentCode}: " . $e->getMessage());
                }
            }

            $successCount = collect($results)->where('success', true)->count();
            $totalCount = count($results);

            return response()->json([
                'success' => true,
                'message' => "Đã lưu {$successCount}/{$totalCount} ảnh vào hệ thống",
                'data' => $results
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Confirm upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}