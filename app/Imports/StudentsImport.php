<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class StudentsImport implements
    ToModel,
    WithHeadingRow,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    /**
     * Map of model attributes to heading keys chosen by the user.
     *
     * @var array<string, string>
     */
    protected array $columnMap;

    /**
     * @var int
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

        if (empty($normalized['student_code'])) {
            throw new InvalidArgumentException('Mapping for "student_code" is required.');
        }

        $this->columnMap = $normalized;
        $this->headingRow = max(1, $headingRow);
    }

    /**
     * @inheritDoc
     */
    public function model(array $row): ?Student
    {
        $attributes = [
            'student_code' => $this->getValueFromRow($row, 'student_code'),
            'class_code' => $this->getValueFromRow($row, 'class_code'),
            'full_name' => $this->getValueFromRow($row, 'full_name'),
            'email' => $this->getValueFromRow($row, 'email'),
            'phone' => $this->getValueFromRow($row, 'phone'),
        ];

        $attributes = array_filter(
            $attributes,
            fn ($value) => !is_null($value) && $value !== ''
        );

        if (empty($attributes['student_code'])) {
            // Skip rows without the required identifier.
            return null;
        }

        return new Student($attributes);
    }

    /**
     * Ensure existing students are updated based on their code.
     */
    public function uniqueBy(): string
    {
        return 'student_code';
    }

    public function headingRow(): int
    {
        return $this->headingRow;
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    protected function getValueFromRow(array $row, string $attribute): ?string
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

        return is_scalar($value) ? (string) $value : null;
    }

    protected function normalizeColumnKey(string $column): string
    {
        return Str::slug($column, '_');
    }
}
