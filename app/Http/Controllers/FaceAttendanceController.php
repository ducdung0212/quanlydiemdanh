<?php

namespace App\Http\Controllers;

use App\Services\FaceRecognitionService;
use App\Models\AttendanceRecord;
use App\Models\ExamSchedule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FaceAttendanceController extends Controller
{
    protected $faceRecognitionService;

    public function __construct(FaceRecognitionService $faceRecognitionService)
    {
        $this->faceRecognitionService = $faceRecognitionService;
    }

    /**
     * Xác thực khuôn mặt và lưu điểm danh
     * POST /api/attendance/face-recognition
     */
    public function authenticate(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'image' => 'required|string',
                'exam_schedule_id' => 'required|exists:exam_schedules,id'
            ]);

            $imageBase64 = $validated['image'];
            $examScheduleId = $validated['exam_schedule_id'];

            // Verify exam schedule exists
            $examSchedule = ExamSchedule::find($examScheduleId);
            if (!$examSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ca thi không tồn tại'
                ], 404);
            }

            Log::info('Face authentication started', [
                'exam_schedule_id' => $examScheduleId,
                'user_id' => auth()->id()
            ]);

            // Call Lambda for face recognition
            $result = $this->faceRecognitionService->authenticateFace(
                $imageBase64,
                (string)$examScheduleId
            );

            if (!$result['success']) {
                Log::warning('Face recognition failed', [
                    'message' => $result['message'],
                    'exam_schedule_id' => $examScheduleId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], $result['status_code'] ?? 400);
            }

            // Extract data from Lambda response
            $lambdaData = $result['data'];
            $studentData = $lambdaData['student'] ?? [];
            $attendanceData = $lambdaData['attendance'] ?? [];
            $confidence = $lambdaData['confidence'] ?? 0;

            $studentCode = $studentData['student_code'] ?? null;

            if (!$studentCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin sinh viên'
                ], 404);
            }

            // Get student from MySQL
            $student = Student::where('student_code', $studentCode)->first();
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => "Sinh viên {$studentCode} không tồn tại trong hệ thống"
                ], 404);
            }

            // --- BẮT ĐẦU KHỐI CODE SỬA ---
            // 1. Tìm bản ghi điểm danh (đã được tạo sẵn)
            $attendanceRecord = AttendanceRecord::where('exam_schedule_id', $examScheduleId)
                ->where('student_code', $studentCode)
                ->first();

            // 2. Nếu không tìm thấy bản ghi (lỗi dữ liệu)
            if (!$attendanceRecord) {
                return response()->json([
                    'success' => false,
                    'message' => "Sinh viên {$student->full_name} không có trong danh sách điểm danh của ca thi này."
                ], 404); // Lỗi 404 Not Found
            }

            // 3. Nếu đã điểm danh 'Có mặt' rồi
            if ($attendanceRecord->rekognition_result === 'match') {
                 return response()->json([
                    'success' => false,
                    'message' => "Sinh viên {$student->full_name} đã điểm danh 'Có mặt' rồi",
                    'data' => [
                        'student' => $student,
                        'attendance' => $attendanceRecord
                    ]
                ], 422); // Lỗi 422 Unprocessable
            }

            // 4. Cập nhật bản ghi (từ 'null'/'absent' -> 'match')
            $attendanceRecord->update([
                'attendance_time' => Carbon::now(),
                'rekognition_result' => 'match',
                'confidence' => $confidence,
                'captured_image_url' => null, // Optional
            ]);
            // --- KẾT THÚC KHỐI CODE SỬA ---

            Log::info('Attendance saved successfully', [
                'student_code' => $studentCode,
                'exam_schedule_id' => $examScheduleId,
                'confidence' => $confidence
            ]);

            return response()->json([
                'success' => true,
                'message' => "Điểm danh thành công cho sinh viên {$student->full_name}",
                'data' => [
                    'student' => [
                        'student_code' => $student->student_code,
                        'full_name' => $student->full_name,
                        'class_name' => $student->class_name,
                        'email' => $student->email,
                    ],
                    'attendance' => [
                        'id' => $attendanceRecord->id,
                        'exam_schedule_id' => $attendanceRecord->exam_schedule_id,
                        'student_code' => $attendanceRecord->student_code,
                        'attendance_time' => $attendanceRecord->attendance_time,
                        'confidence' => $attendanceRecord->confidence,
                        'rekognition_result' => $attendanceRecord->rekognition_result,
                    ],
                    'confidence' => $confidence,
                    'rekognition_result' => 'match'
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Face attendance error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Lambda connection
     * GET /api/attendance/test-lambda
     */
    public function testLambda()
    {
        $result = $this->faceRecognitionService->testConnection();

        return response()->json($result);
    }
}