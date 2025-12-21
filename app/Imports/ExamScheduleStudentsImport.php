<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\AttendanceRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExamScheduleStudentsImport implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    SkipsEmptyRows
{
    /**
     * Map of model attributes to heading keys chosen by the user.
     *
     * @var array<string, string>
     */
    protected array $columnMap;

    /**
     * The heading row in the worksheet.
     */
    protected int $headingRow;

    /**
     * ID của ca thi
     */
    protected int $scheduleId;

    /**
     * Danh sách sinh viên đã được thêm thành công
     */
    public array $addedStudents = [];

    /**
     * Danh sách sinh viên bị bỏ qua (không tìm thấy hoặc đã tồn tại)
     */
    public array $skippedStudents = [];

    /**
     * @param  int  $scheduleId
     * @param  array<string, string|null>  $columnMap
     * @param  int  $headingRow
     */
    public function __construct(int $scheduleId, array $columnMap, int $headingRow = 1)
    {
        $this->scheduleId = $scheduleId;

        // Chuẩn hóa mapping key về dạng slug gạch dưới (snake_case)
        $normalized = collect($columnMap)
            ->filter(fn($column) => filled($column))
            ->map(fn($column) => $this->normalizeColumnKey($column))
            ->toArray();

        // Chỉ cần student_code là bắt buộc
        if (empty($normalized['student_code'])) {
            throw new InvalidArgumentException("Mapping for \"student_code\" is required.");
        }

        $this->columnMap = $normalized;
        $this->headingRow = max(1, $headingRow);
    }

    /**
     * @inheritDoc
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            if ($row instanceof Collection) {
                $row = $row->toArray();
            }

            if (!is_array($row)) {
                continue;
            }

            // Chuẩn hóa key của row dữ liệu từ Excel để khớp với mapping
            $row = collect($row)
                ->mapWithKeys(fn($value, $key) => [$this->normalizeColumnKey((string) $key) => $value])
                ->toArray();

            $studentCode = trim((string) $this->getValueFromRow($row, 'student_code'));

            if (empty($studentCode)) {
                $this->skippedStudents[] = [
                    'row' => $index + $this->headingRow + 1,
                    'student_code' => '',
                    'reason' => 'Mã sinh viên trống'
                ];
                continue;
            }

            // Tìm sinh viên trong database
            $student = Student::where('student_code', $studentCode)->first();

            if (!$student) {
                $this->skippedStudents[] = [
                    'row' => $index + $this->headingRow + 1,
                    'student_code' => $studentCode,
                    'reason' => 'Không tìm thấy sinh viên'
                ];
                continue;
            }

            // Kiểm tra xem sinh viên đã được thêm vào ca thi chưa
            $exists = AttendanceRecord::where('exam_schedule_id', $this->scheduleId)
                ->where('student_code', $studentCode)
                ->exists();

            if ($exists) {
                $this->skippedStudents[] = [
                    'row' => $index + $this->headingRow + 1,
                    'student_code' => $studentCode,
                    'reason' => 'Sinh viên đã có trong ca thi'
                ];
                continue;
            }

            // Thêm sinh viên vào ca thi
            try {
                AttendanceRecord::create([
                    'exam_schedule_id' => $this->scheduleId,
                    'student_code' => $studentCode,
                    'rekognition_result' => null,
                    'attendance_time' => null,
                ]);

                $this->addedStudents[] = [
                    'row' => $index + $this->headingRow + 1,
                    'student_code' => $studentCode,
                    'full_name' => $student->full_name,
                    'class_code' => $student->class_code,
                ];
            } catch (\Exception $e) {
                $this->skippedStudents[] = [
                    'row' => $index + $this->headingRow + 1,
                    'student_code' => $studentCode,
                    'reason' => 'Lỗi khi thêm: ' . $e->getMessage()
                ];
                Log::error('Error adding student to exam schedule', [
                    'schedule_id' => $this->scheduleId,
                    'student_code' => $studentCode,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get a value from the row using the column mapping.
     */
    protected function getValueFromRow(array $row, string $attribute): mixed
    {
        $columnKey = $this->columnMap[$attribute] ?? null;

        if (!$columnKey) {
            return null;
        }

        return $row[$columnKey] ?? null;
    }

    /**
     * Normalize a column name for comparison (remove special chars, lowercase).
     */
    protected function normalizeColumnKey(string $column): string
    {
        $ascii = Str::lower(Str::ascii($column));
        $slug = preg_replace('/[^a-z0-9]+/i', '_', $ascii);
        return trim((string) $slug, '_');
    }

    /**
     * @inheritDoc
     */
    public function headingRow(): int
    {
        return $this->headingRow;
    }

    /**
     * @inheritDoc
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * @inheritDoc
     */
    public function batchSize(): int
    {
        return 100;
    }
}
