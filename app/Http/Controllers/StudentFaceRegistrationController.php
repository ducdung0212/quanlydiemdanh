<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

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

                // Tách student_code từ tên tệp (ví dụ: "SV001.jpg" -> "SV001")
                // Quan trọng: Phải khớp với logic Lambda của bạn
                $studentCode = pathinfo($fileName, PATHINFO_FILENAME);

                if (empty($studentCode)) {
                    $uploadUrls[] = [
                        'file_name' => $fileName,
                        'success' => false,
                        'message' => 'Tên tệp không hợp lệ.'
                    ];
                    continue; // Bỏ qua tệp này
                }

                // Đường dẫn trên S3
                $s3Key = "images_to_register/{$fileName}";

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
}