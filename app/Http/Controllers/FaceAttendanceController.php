<?php

namespace App\Http\Controllers;

use App\Services\FaceRecognitionService;
use Illuminate\Support\Facades\Auth;
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
                'user_id' => Auth::id()
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

            Log::info('Attendance saved successfully', [
                'student_code' => $studentCode,
                'exam_schedule_id' => $examScheduleId,
                'confidence' => $confidence
            ]);

            $commit = $request->has('commit') ? (bool)$request->input('commit') : true;

            return $this->handleAttendanceByStudent(
                $student,
                (int) $examScheduleId,
                (float) $confidence,
                'face',
                $commit
            );
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
     * Xác thực QR và lưu điểm danh
     * POST /api/attendance/qr-scan
     */
    public function authenticateQr(Request $request)
    {
        try {
            $validated = $request->validate([
                'qr_content' => 'required|string',
                'exam_schedule_id' => 'required|exists:exam_schedules,id',
            ]);

            $examScheduleId = (int) $validated['exam_schedule_id'];
            $qrContent = trim($validated['qr_content']);

            $examSchedule = ExamSchedule::find($examScheduleId);
            if (!$examSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ca thi không tồn tại'
                ], 404);
            }

            $studentCode = $this->extractStudentCodeFromQr($qrContent);
            if (!$studentCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nội dung QR không hợp lệ. Định dạng đúng: DHxxxxxxx_HoVaTen_NgaySinh'
                ], 422);
            }

            $student = Student::where('student_code', $studentCode)->first();
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => "Sinh viên {$studentCode} không tồn tại trong hệ thống"
                ], 404);
            }

            Log::info('QR attendance started', [
                'exam_schedule_id' => $examScheduleId,
                'student_code' => $studentCode,
                'user_id' => Auth::id(),
            ]);

            $commit = $request->has('commit') ? (bool)$request->input('commit') : true;

            return $this->handleAttendanceByStudent(
                $student,
                $examScheduleId,
                null,
                'qr_code',
                $commit
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('QR attendance error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    private function extractStudentCodeFromQr(string $qrContent): ?string
    {
        $parts = explode('_', trim($qrContent));
        if (count($parts) < 1) {
            return null;
        }

        $studentCode = strtoupper(trim($parts[0] ?? ''));
        if (!preg_match('/^DH[0-9A-Z]+$/', $studentCode)) {
            return null;
        }

        return $studentCode;
    }

    private function handleAttendanceByStudent(Student $student, int $examScheduleId, ?float $confidence, string $attendanceMethod, bool $commit)
    {
        $attendanceRecord = AttendanceRecord::where('exam_schedule_id', $examScheduleId)
            ->where('student_code', $student->student_code)
            ->first();

        if (!$attendanceRecord) {
            return response()->json([
                'success' => false,
                'message' => "Sinh viên {$student->full_name} không có trong danh sách điểm danh của ca thi này."
            ], 404);
        }

        if ($attendanceRecord->rekognition_result === 'match') {
            if ($commit) {
                return response()->json([
                    'success' => false,
                    'message' => "Sinh viên {$student->full_name} đã điểm danh 'Có mặt' rồi",
                    'data' => [
                        'student' => $student,
                        'attendance' => $attendanceRecord,
                    ]
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sinh viên đã điểm danh trước đó',
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
                        'attendance_method' => $attendanceRecord->attendance_method,
                    ],
                    'confidence' => $confidence,
                    'rekognition_result' => $attendanceRecord->rekognition_result,
                ]
            ], 200);
        }

        if ($commit) {
            $updatePayload = [
                'attendance_time' => Carbon::now(),
                'rekognition_result' => 'match',
                'captured_image_url' => null,
                'attendance_method' => $attendanceMethod,
            ];

            if ($confidence !== null) {
                $updatePayload['confidence'] = $confidence;
            }

            $attendanceRecord->update($updatePayload);
            $attendanceRecord->refresh();
        }

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
                    'attendance_method' => $attendanceRecord->attendance_method,
                ],
                'confidence' => $confidence,
                'rekognition_result' => 'match',
                'attendance_method' => $attendanceMethod,
            ]
        ], 200);
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
