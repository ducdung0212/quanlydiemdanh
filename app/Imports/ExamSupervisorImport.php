<?php

namespace App\Imports;

use App\Models\ExamSchedule;
use App\Models\ExamSupervisor;
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

class ExamSupervisorImport implements
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

        foreach (['subject_code', 'exam_date', 'exam_time', 'room', 'lecturer_code'] as $required) {
            if (empty($normalized[$required])) {
                throw new InvalidArgumentException("Mapping for \"{$required}\" is required.");
            }
        }

        $this->columnMap = $normalized;
        $this->headingRow = max(1, $headingRow);
    }

    public function collection(Collection $rows): void
    {
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $rowIndex => $row) {
            if ($row instanceof Collection) {
                $row = $row->toArray();
            }

            if (!is_array($row)) {
                continue;
            }

            $row = collect($row)
                ->mapWithKeys(fn ($value, $key) => [$this->normalizeColumnKey((string) $key) => $value])
                ->toArray();

            // Lấy dữ liệu thô để log nếu cần
            $rawDate = $this->getValueFromRow($row, 'exam_date');
            
            $subjectCode = $this->getValueFromRow($row, 'subject_code');
            $examDate = $this->normalizeDate($rawDate);
            $examTime = $this->normalizeTime($this->getValueFromRow($row, 'exam_time'));
            $room = $this->getValueFromRow($row, 'room');
            $lecturerCode = $this->getValueFromRow($row, 'lecturer_code');

            // LOGGING: Kiểm tra dữ liệu bị thiếu hoặc sai định dạng
            if (!$subjectCode || !$examDate || !$examTime || !$room || !$lecturerCode) {
                $skipped++;
                Log::warning("ExamSupervisor Import: Dòng " . ($rowIndex + $this->headingRow + 1) . " bị bỏ qua - Thiếu dữ liệu hoặc sai định dạng ngày", [
                    'raw_date' => $rawDate,
                    'parsed_date' => $examDate,
                    'subject_code' => $subjectCode,
                    'lecturer_code' => $lecturerCode
                ]);
                continue;
            }

            // Tìm Lịch thi (Natural Key Lookup)
            $examSchedule = ExamSchedule::where([
                ['subject_code', '=', $subjectCode],
                ['exam_date', '=', $examDate],
                ['exam_time', '=', $examTime],
                ['room', '=', $room],
            ])->first();

            if (!$examSchedule) {
                $skipped++;
                Log::warning("ExamSupervisor Import: Dòng " . ($rowIndex + $this->headingRow + 1) . " bị bỏ qua - Không tìm thấy ca thi tương ứng trong DB", [
                    'subject' => $subjectCode,
                    'date' => $examDate,
                    'time' => $examTime,
                    'room' => $room
                ]);
                continue;
            }

            $payload = array_filter([
                'role' => $this->getValueFromRow($row, 'role'),
                'note' => $this->getValueFromRow($row, 'note'),
            ], fn ($value) => !is_null($value));

            try {
                ExamSupervisor::updateOrCreate(
                    [
                        'exam_schedule_id' => $examSchedule->id,
                        'lecturer_code' => $lecturerCode,
                    ],
                    $payload
                );
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                Log::error("ExamSupervisor Import: Lỗi SQL tại dòng " . ($rowIndex + $this->headingRow + 1), [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("ExamSupervisor Import hoàn tất batch", [
            'imported' => $imported,
            'skipped' => $skipped,
        ]);
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
            // Ưu tiên format Việt Nam: d/m/Y
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