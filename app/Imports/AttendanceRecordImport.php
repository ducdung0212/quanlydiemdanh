<?php

namespace App\Imports;

use App\Models\AttendanceRecord;
use App\Models\ExamSchedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AttendanceRecordImport implements ToCollection, WithHeadingRow
{
    private array $mapping;
    private int $headingRow;

    public function __construct(array $mapping, int $headingRow = 1)
    {
        $this->mapping = $mapping;
        $this->headingRow = $headingRow;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            // Map cột từ Excel
            $subjectCode = $rowArray[$this->mapping['subject_code']] ?? null;
            $examDate = $rowArray[$this->mapping['exam_date']] ?? null;
            $examTime = $rowArray[$this->mapping['exam_time']] ?? null;
            $room = $rowArray[$this->mapping['room']] ?? null;
            $studentCode = $rowArray[$this->mapping['student_code']] ?? null;

            // Validate dữ liệu
            if (!$subjectCode || !$examDate || !$examTime || !$room || !$studentCode) {
                throw new InvalidArgumentException(
                    'Dữ liệu không đầy đủ: subject_code, exam_date, exam_time, room, student_code bắt buộc.'
                );
            }

            // Parse ngày
            try {
                $parsedDate = Carbon::createFromFormat('d/m/Y', $examDate)->format('Y-m-d');
            } catch (\Exception $e) {
                throw new InvalidArgumentException(
                    "Ngày thi không hợp lệ: {$examDate}. Dùng định dạng dd/mm/yyyy"
                );
            }

            // Tra cứu exam_schedule_id dựa trên Khóa Tự Nhiên
            $examSchedule = ExamSchedule::where([
                ['subject_code', '=', trim($subjectCode)],
                ['exam_date', '=', $parsedDate],
                ['exam_time', '=', trim($examTime)],
                ['room', '=', trim($room)],
            ])->first();

            if (!$examSchedule) {
                throw new InvalidArgumentException(
                    "Không tìm thấy ca thi: {$subjectCode} - {$examDate} - {$examTime} - {$room}"
                );
            }

            // Tra cứu student_code
            $student = Student::where('student_code', '=', trim($studentCode))->first();

            if (!$student) {
                throw new InvalidArgumentException(
                    "Không tìm thấy sinh viên: {$studentCode}"
                );
            }

            // Thêm hoặc cập nhật bản ghi điểm danh
            AttendanceRecord::updateOrCreate(
                [
                    'exam_schedule_id' => $examSchedule->id,
                    'student_code' => trim($studentCode),
                ],
                [
                    'exam_schedule_id' => $examSchedule->id,
                    'student_code' => trim($studentCode),
                ]
            );
        }
    }
}
