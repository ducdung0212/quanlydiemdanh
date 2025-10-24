<?php

namespace App\Imports;

use App\Models\ExamSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExamSupervisorImport implements ToCollection, WithHeadingRow
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
            $lecturerCode = $rowArray[$this->mapping['lecturer_code']] ?? null;

            // Validate dữ liệu
            if (!$subjectCode || !$examDate || !$examTime || !$room || !$lecturerCode) {
                throw new InvalidArgumentException(
                    'Dữ liệu không đầy đủ: subject_code, exam_date, exam_time, room, lecturer_code bắt buộc.'
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

            // Thêm hoặc cập nhật giám sát thi
            $examSchedule->supervisors()->updateOrCreate(
                ['lecturer_code' => trim($lecturerCode)],
                ['lecturer_code' => trim($lecturerCode)]
            );
        }
    }
}
