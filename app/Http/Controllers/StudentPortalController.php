<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\FaceRegistrationWindow;
use App\Models\Student;
use App\Models\Student_Photos;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPortalController extends Controller
{
    public function myExamSchedules(Request $request)
    {
        $student = $this->resolveCurrentStudent();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin sinh viên liên kết với tài khoản hiện tại.',
            ], 404);
        }

        $limit = max(1, min(100, (int) $request->query('limit', 10)));
        $q = trim((string) $request->query('q', ''));

        $query = $student->examSchedules()
            ->with('subject')
            ->orderByDesc('exam_date')
            ->orderByDesc('exam_time');

        if ($q !== '') {
            $query->where(function ($inner) use ($q) {
                $inner->where('subject_code', 'like', "%{$q}%")
                    ->orWhere('room', 'like', "%{$q}%")
                    ->orWhereHas('subject', function ($subjectQuery) use ($q) {
                        $subjectQuery->where('name', 'like', "%{$q}%");
                    });
            });
        }

        return response()->json([
            'success' => true,
            'student_code' => $student->student_code,
            'data' => $query->paginate($limit),
            'message' => 'Lịch thi của sinh viên',
        ]);
    }

    public function myAttendanceResults(Request $request)
    {
        $student = $this->resolveCurrentStudent();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin sinh viên liên kết với tài khoản hiện tại.',
            ], 404);
        }

        $limit = max(1, min(100, (int) $request->query('limit', 10)));

        $query = AttendanceRecord::query()
            ->with(['examSchedule.subject'])
            ->where('student_code', $student->student_code)
            ->orderByDesc('created_at');

        return response()->json([
            'success' => true,
            'student_code' => $student->student_code,
            'data' => $query->paginate($limit),
            'message' => 'Kết quả điểm danh của sinh viên',
        ]);
    }

    public function currentFaceRegistrationWindow()
    {
        $window = FaceRegistrationWindow::activeNow()->first();

        if (!$window) {
            return response()->json([
                'success' => true,
                'can_register' => false,
                'window' => null,
                'message' => 'Hiện chưa có đợt cho phép đổi ảnh cá nhân.',
            ]);
        }

        return response()->json([
            'success' => true,
            'can_register' => true,
            'window' => $window,
            'message' => 'Đang trong thời gian cho phép đổi ảnh.',
        ]);
    }

    public function generateSelfUploadUrl(Request $request)
    {
        $student = $this->resolveCurrentStudent();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin sinh viên liên kết với tài khoản hiện tại.',
            ], 404);
        }

        $window = FaceRegistrationWindow::activeNow()->first();
        if (!$window) {
            return response()->json([
                'success' => false,
                'message' => 'Admin chưa mở thời gian đổi ảnh cá nhân.',
            ], 403);
        }

        $validated = $request->validate([
            'file_name' => 'required|string|max:120',
            'file_type' => 'required|string|in:image/jpeg,image/png',
        ]);

        $fileName = $validated['file_name'];
        $fileType = $validated['file_type'];

        $studentCodeFromName = strtoupper(pathinfo($fileName, PATHINFO_FILENAME));
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Ảnh chỉ hỗ trợ định dạng jpg, jpeg hoặc png.',
            ], 422);
        }

        if ($studentCodeFromName !== strtoupper($student->student_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Tên ảnh phải đúng mã sinh viên của bạn.',
            ], 422);
        }

        $bucket = config('filesystems.disks.s3.bucket');

        try {
            $s3Client = new S3Client([
                'region' => config('filesystems.disks.s3.region'),
                'version' => 'latest',
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            $safeFileName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
            $s3Key = "images_to_register/self_service/{$safeFileName}";

            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket' => $bucket,
                'Key' => $s3Key,
                'ContentType' => $fileType,
            ]);

            $presignedRequest = $s3Client->createPresignedRequest($cmd, '+15 minutes');

            return response()->json([
                'success' => true,
                'upload_url' => (string) $presignedRequest->getUri(),
                's3_key' => $s3Key,
                'message' => 'Tạo URL tải ảnh thành công.',
            ]);
        } catch (AwsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo URL tải ảnh: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function confirmSelfUpload(Request $request)
    {
        $student = $this->resolveCurrentStudent();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin sinh viên liên kết với tài khoản hiện tại.',
            ], 404);
        }

        $window = FaceRegistrationWindow::activeNow()->first();
        if (!$window) {
            return response()->json([
                'success' => false,
                'message' => 'Admin chưa mở thời gian đổi ảnh cá nhân.',
            ], 403);
        }

        $validated = $request->validate([
            'file_name' => 'required|string|max:120',
        ]);

        $fileName = $validated['file_name'];
        $studentCodeFromName = strtoupper(pathinfo($fileName, PATHINFO_FILENAME));

        if ($studentCodeFromName !== strtoupper($student->student_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Tên ảnh phải đúng mã sinh viên của bạn.',
            ], 422);
        }

        $bucket = config('filesystems.disks.s3.bucket');
        $region = config('filesystems.disks.s3.region');
        $safeFileName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
        $s3Key = "images_to_register/self_service/{$safeFileName}";
        $s3Url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$s3Key}";

        Student_Photos::where('student_code', $student->student_code)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        Student_Photos::create([
            'student_code' => $student->student_code,
            'image_url' => $s3Url,
            'uploaded_by_user_id' => Auth::id(),
            'approved_by_user_id' => null,
            'approved_at' => null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký ảnh cá nhân thành công.',
            'data' => [
                'student_code' => $student->student_code,
                'image_url' => $s3Url,
            ],
        ]);
    }

    protected function resolveCurrentStudent(): ?Student
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $studentCode = strtoupper((string) ($user->student_code ?? ''));
        if ($studentCode !== '') {
            return Student::where('student_code', $studentCode)->first();
        }

        if (!empty($user->email)) {
            return Student::where('email', $user->email)->first();
        }

        return null;
    }
}
