<?php

namespace App\Imports;

use App\Models\AttendanceRecord;
use App\Models\ExamSchedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AttendanceRecordImport implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    SkipsEmptyRows
{
    protected array $columnMap;
    protected int $headingRow;

    public function __construct(array $columnMap, int $headingRow = 1)
    {
        $normalized = collect($columnMap)
            ->filter(fn ($column) => filled($column))
            ->map(fn ($column) => $this->normalizeColumnKey($column))
            ->toArray();

        foreach (['subject_code', 'exam_date', 'exam_time', 'room', 'student_code'] as $required) {
            if (empty($normalized[$required])) {
                throw new InvalidArgumentException("Mapping for \"{$required}\" is required.");
            }
        }

        $this->columnMap = $normalized;
        $this->headingRow = max(1, $headingRow);
    }

    public function collection(Collection $rows): void
    {
        $toUpsert = [];
        $studentCodes = [];
        $skippedCount = 0;

        foreach ($rows as $index => $row) {
            if ($row instanceof Collection) {
                $row = $row->toArray();
            }

            if (!is_array($row)) {
                continue;
            }

            $row = collect($row)
                ->mapWithKeys(fn ($value, $key) => [$this->normalizeColumnKey((string) $key) => $value])
                ->toArray();

            $rawDate = $this->getValueFromRow($row, 'exam_date');

            $subjectCode = $this->getValueFromRow($row, 'subject_code');
            $examDate = $this->normalizeDate($rawDate);
            $examTime = $this->normalizeTime($this->getValueFromRow($row, 'exam_time'));
            $room = $this->getValueFromRow($row, 'room');
            $studentCode = $this->getValueFromRow($row, 'student_code');

            // Xử lý Attendance Time
            // Lấy giá trị từ file excel, nếu có thì format, nếu không thì để null
            $attendanceTime = $this->normalizeDateTime($this->getValueFromRow($row, 'attendance_time'));

            if (!$subjectCode || !$examDate || !$examTime || !$room || !$studentCode) {
                $skippedCount++;
                Log::warning("Attendance Import: Dòng " . ($index + $this->headingRow + 1) . " bị bỏ qua - Dữ liệu không hợp lệ", [
                    'raw_date' => $rawDate,
                    'parsed_date' => $examDate,
                    'student_code' => $studentCode,
                    'subject_code' => $subjectCode
                ]);
                continue;
            }

            $examSchedule = ExamSchedule::where([
                ['subject_code', '=', $subjectCode],
                ['exam_date', '=', $examDate],
                ['exam_time', '=', $examTime],
                ['room', '=', $room],
            ])->first();

            if (!$examSchedule) {
                $skippedCount++;
                Log::warning("Attendance Import: Dòng " . ($index + $this->headingRow + 1) . " bị bỏ qua - Không tìm thấy ca thi", [
                    'subject' => $subjectCode, 'date' => $examDate, 'room' => $room
                ]);
                continue;
            }

            // Lọc các trường payload khác (image, confidence...)
            $payload = array_filter([
                'captured_image_url' => $this->getValueFromRow($row, 'captured_image_url'),
                'rekognition_result' => $this->getValueFromRow($row, 'rekognition_result'),
                'confidence' => $this->getValueFromRow($row, 'confidence'),
            ], fn ($value) => !is_null($value));

            // [QUAN TRỌNG] Gán attendance_time một cách tường minh.
            // Nếu $attendanceTime là null, nó sẽ gửi null vào DB, ngăn chặn DB tự lấy NOW().
            // Việc gán này nằm ngoài array_filter phía trên.
            $payload['attendance_time'] = $attendanceTime;

            $toUpsert[] = [
                'exam_schedule_id' => $examSchedule->id,
                'student_code' => $studentCode,
                'payload' => $payload,
            ];

            $studentCodes[] = $studentCode;
        }

        $studentCodes = array_values(array_unique(array_filter($studentCodes, fn($v) => $v !== null && $v !== '')));

        if (!empty($studentCodes)) {
            $existing = Student::whereIn('student_code', $studentCodes)->pluck('student_code')->all();
            $missing = array_values(array_diff($studentCodes, $existing));

            if (!empty($missing)) {
                throw new InvalidArgumentException('Các mã sinh viên sau không tồn tại: ' . implode(', ', $missing));
            }
        }

        foreach ($toUpsert as $item) {
            AttendanceRecord::updateOrCreate(
                [
                    'exam_schedule_id' => $item['exam_schedule_id'],
                    'student_code' => $item['student_code'],
                ],
                $item['payload']
            );
        }
        
        if ($skippedCount > 0) {
            Log::info("Attendance Import Batch: Đã bỏ qua {$skippedCount} dòng do lỗi dữ liệu.");
        }
    }

    public function headingRow(): int
    {
        return $this->headingRow;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function batchSize(): int
    {
        return 200;
    }

    protected function getValueFromRow(array $row, string $attribute): mixed
    {
        $columnKey = $this->columnMap[$attribute] ?? null;
        if (!$columnKey || !Arr::exists($row, $columnKey)) return null;
        $value = $row[$columnKey];
        if (is_string($value)) $value = trim($value);
        return ($value === '') ? null : $value;
    }

    protected function normalizeColumnKey(string $column): string
    {
        return Str::slug($column, '_');
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if (empty($value)) return null;

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
            } catch (\Throwable) { return null; }
        }

        if (is_string($value) && $value !== '') {
            $value = trim($value);
            try {
                if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
                    return Carbon::createFromFormat('d/m/Y', $value)->toDateString();
                }
            } catch (\Throwable) {}

            try {
                return Carbon::parse($value)->toDateString();
            } catch (\Throwable) { return null; }
        }

        return null;
    }

    // Thêm hàm này để xử lý ngày giờ đầy đủ
    protected function normalizeDateTime(mixed $value): ?string
    {
        if (empty($value)) return null;

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d H:i:s');
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d H:i:s');
            } catch (\Throwable) { return null; }
        }

        if (is_string($value) && $value !== '') {
            $value = trim($value);
            // Thử format d/m/Y H:i:s
            try {
                return Carbon::createFromFormat('d/m/Y H:i:s', $value)->format('Y-m-d H:i:s');
            } catch (\Throwable) {}
            
            // Thử format d/m/Y H:i
            try {
                return Carbon::createFromFormat('d/m/Y H:i', $value)->format('Y-m-d H:i:s');
            } catch (\Throwable) {}

            // Fallback sang parse thông thường
            try {
                return Carbon::parse($value)->format('Y-m-d H:i:s');
            } catch (\Throwable) { return null; }
        }

        return null;
    }

    protected function normalizeTime(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('H:i:s');
        }
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('H:i:s');
            } catch (\Throwable) { return null; }
        }
        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value)->format('H:i:s');
            } catch (\Throwable) {
                $clean = preg_replace('/[^0-9:]/', '', $value);
                if ($clean && preg_match('/^\d{1,2}:\d{2}(?::\d{2})?$/', $clean)) {
                    try {
                        $format = strlen($clean) === 5 ? 'H:i' : 'H:i:s';
                        return Carbon::createFromFormat($format, $clean)->format('H:i:s');
                    } catch (\Throwable) { return null; }
                }
                return null;
            }
        }
        return null;
    }
}