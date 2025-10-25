<?php

namespace App\Imports;

use App\Models\ExamSchedule;
use App\Models\ExamSupervisor;
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

class ExamSupervisorImport implements
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

        // Required mappings for natural key lookup + supervisor
        foreach (['subject_code', 'exam_date', 'exam_time', 'room', 'lecturer_code'] as $required) {
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
            $lecturerCode = $this->getValueFromRow($row, 'lecturer_code');

            // Skip rows missing required data after normalization
            if (!$subjectCode || !$examDate || !$examTime || !$room || !$lecturerCode) {
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
            $payload = array_filter([
                'role' => $this->getValueFromRow($row, 'role'),
                'note' => $this->getValueFromRow($row, 'note'),
            ], fn ($value) => !is_null($value));

            // Upsert supervisor assignment using natural key-derived exam_schedule_id + lecturer_code
            ExamSupervisor::updateOrCreate(
                [
                    'exam_schedule_id' => $examSchedule->id,
                    'lecturer_code' => $lecturerCode,
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
            return Carbon::instance($value)->format('H:i:s');
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('H:i:s');
            } catch (\Throwable) {
                return null;
            }
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
                    } catch (\Throwable) {
                        return null;
                    }
                }
                return null;
            }
        }

        return null;
    }
}
