<?php

namespace App\Imports;

use App\Models\Lecturer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class LecturersImport implements
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

        if (empty($normalized['lecturer_code'])) {
            throw new InvalidArgumentException('Mapping for "lecturer_code" is required.');
        }

        $this->columnMap = $normalized;
        $this->headingRow = max(1, $headingRow);
    }

    /**
     * @inheritDoc
     */
    public function model(array $row): ?Lecturer
    {
        $attributes = [
            'lecturer_code' => $this->getValueFromRow($row, 'lecturer_code'),
            'faculty_code' => $this->getValueFromRow($row, 'faculty_code'),
            'full_name' => $this->getValueFromRow($row, 'full_name'),
            'email' => $this->getValueFromRow($row, 'email'),
            'phone' => $this->getValueFromRow($row, 'phone'),
        ];

        $attributes = array_filter(
            $attributes,
            fn ($value) => !is_null($value) && $value !== ''
        );

        if (empty($attributes['lecturer_code'])) {
            return null;
        }

        return new Lecturer($attributes);
    }

    public function uniqueBy(): string
    {
        return 'lecturer_code';
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
