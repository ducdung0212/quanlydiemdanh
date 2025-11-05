<?php

namespace App\Imports;

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

class ExamSchedulesImport implements
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

        foreach (['subject_code', 'exam_date', 'exam_time', 'duration'] as $required) {
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

            $row = collect($row)
                ->mapWithKeys(fn ($value, $key) => [$this->normalizeColumnKey((string) $key) => $value])
                ->toArray();

            $subjectCode = $this->getValueFromRow($row, 'subject_code');
            $examDate = $this->normalizeDate($this->getValueFromRow($row, 'exam_date'));
            $examTime = $this->normalizeTime($this->getValueFromRow($row, 'exam_time'));
            $duration = $this->normalizeDuration($this->getValueFromRow($row, 'duration'));

            if (!$subjectCode || !$examDate || !$examTime || !$duration) {
                continue;
            }

            $room = $this->getValueFromRow($row, 'room');

            $payload = array_filter([
                'room' => $room,
                'note' => $this->getValueFromRow($row, 'note'),
            ], fn ($value) => !is_null($value));

            ExamSchedule::updateOrCreate(
                [
                    'subject_code' => $subjectCode,
                    'exam_date' => $examDate,
                    'exam_time' => $examTime,
                    'duration' => $duration,
                    'room' => $room,
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

    protected function normalizeColumnKey(string $column): string
    {
        return Str::slug($column, '_');
    }

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

    protected function normalizeDuration(mixed $value): ?int
    {
        // Nếu đã là số nguyên dương → trả về trực tiếp
        if (is_int($value) && $value > 0) {
            return $value;
        }

        // Nếu là số thực (từ Excel) → làm tròn
        if (is_numeric($value) && $value > 0) {
            return (int) round($value);
        }

        // Nếu là chuỗi
        if (is_string($value) && $value !== '') {
            $value = trim($value);

            // Trường hợp: "90", "120" → chuyển thành int
            if (ctype_digit($value)) {
                return (int) $value;
            }

            // Trường hợp: "1h30", "2h", "1.5h" → chuyển thành phút
            if (preg_match('/^(\d+(?:\.\d+)?)\s*h(?:our)?s?\s*(\d+)?\s*m(?:in)?s?$/i', $value, $matches)) {
                $hours = (float) $matches[1];
                $minutes = isset($matches[2]) ? (int) $matches[2] : 0;
                return (int) round($hours * 60 + $minutes);
            }
            if (preg_match('/^(\d+(?:\.\d+)?)\s*h(?:our)?s?$/i', $value, $matches)) {
                return (int) round((float) $matches[1] * 60);
            }

            // Trường hợp: "01:30", "1:30" (giờ:phút) → chuyển thành phút
            if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $matches)) {
                $hours = (int) $matches[1];
                $minutes = (int) $matches[2];
                return $hours * 60 + $minutes;
            }

            // Trường hợp: "90m", "120 min", "90 phút" → lấy số phút
            if (preg_match('/^(\d+)\s*(?:m(?:in)?s?|phút)$/i', $value, $matches)) {
                return (int) $matches[1];
            }
        }

        return null;
    }
}
