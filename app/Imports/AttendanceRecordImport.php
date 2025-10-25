<?php

namespace App\Imports;

use App\Models\AttendanceRecord;
use App\Models\ExamSchedule;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
     * @param  array<string, string|null>  $columnMap
     */
    public function __construct(array $columnMap, int $headingRow = 1)
    {
        $normalized = collect($columnMap)
            ->filter(fn ($column) => filled($column))
            ->map(fn ($column) => $this->normalizeColumnKey($column))
            ->toArray();

        // Required mappings for natural key lookup + student
        foreach (['subject_code', 'exam_date', 'exam_time', 'room', 'student_code'] as $required) {
            if (empty($normalized[$required])) {
                throw new InvalidArgumentException("Mapping for \"{$required}\" is required.");
            }
        }

        $this->columnMap = $normalized;
        $this->headingRow = max(1, $headingRow);
    }

    /**
     * @inheritDoc
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if ($row instanceof Collection) {
                $row = $row->toArray();
            }

            if (!is_array($row)) {
                continue;
            }

            // Normalize incoming row keys to snake_case for safe lookups
            $row = collect($row)
                ->mapWithKeys(fn ($value, $key) => [$this->normalizeColumnKey((string) $key) => $value])
                ->toArray();

            $subjectCode = $this->getValueFromRow($row, 'subject_code');
            $examDate = $this->normalizeDate($this->getValueFromRow($row, 'exam_date'));
            $examTime = $this->normalizeTime($this->getValueFromRow($row, 'exam_time'));
            $room = $this->getValueFromRow($row, 'room');
            $studentCode = $this->getValueFromRow($row, 'student_code');

            // Skip rows missing required data after normalization
            if (!$subjectCode || !$examDate || !$examTime || !$room || !$studentCode) {
                continue;
            }

            // Lookup ExamSchedule by natural key
            $examSchedule = ExamSchedule::where([
                ['subject_code', '=', $subjectCode],
                ['exam_date', '=', $examDate],
                ['exam_time', '=', $examTime],
                ['room', '=', $room],
            ])->first();

            if (!$examSchedule) {
                // Skip if exam session not found
                continue;
            }

            // Optional payload fields if mapped
            // NOTE: attendance_time should NOT be imported, it should only be set during actual attendance marking
            
            $payload = array_filter([
                'captured_image_url' => $this->getValueFromRow($row, 'captured_image_url'),
                'rekognition_result' => $this->getValueFromRow($row, 'rekognition_result'),
                'confidence' => $this->getValueFromRow($row, 'confidence'),
                // attendance_time is intentionally excluded from import
            ], fn ($value) => !is_null($value));

            // Upsert attendance record using natural key-derived exam_schedule_id + student_code
            AttendanceRecord::updateOrCreate(
                [
                    'exam_schedule_id' => $examSchedule->id,
                    'student_code' => $studentCode,
                ],
                $payload
            );
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

    /**
     * Safely get a value from a normalized row using the configured column map.
     */
    protected function getValueFromRow(array $row, string $attribute): mixed
    {
        $columnKey = $this->columnMap[$attribute] ?? null;

        if (!$columnKey) {
            return null;
        }

        if (!Arr::exists($row, $columnKey)) {
            return null;
        }

        $value = $row[$columnKey];

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Normalize user-provided heading/column keys to snake_case.
     */
    protected function normalizeColumnKey(string $column): string
    {
        return Str::slug($column, '_');
    }

    /**
     * Normalize various date representations to Y-m-d.
     */
    protected function normalizeDate(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * Normalize various time representations to H:i:s.
     */
    protected function normalizeTime(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toTimeString();
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toTimeString();
            } catch (\Throwable) {
                return null;
            }
        }

        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value)->toTimeString();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * Normalize various datetime representations to Y-m-d H:i:s.
     */
    protected function normalizeDateTime(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateTimeString();
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateTimeString();
            } catch (\Throwable) {
                return null;
            }
        }

        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value)->toDateTimeString();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
