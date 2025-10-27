<?php

namespace App\Imports;

use App\Models\Subject;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class SubjectImport implements
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

        if (empty($normalized['subject_code'])) {
            throw new InvalidArgumentException('Mapping for "subject_code" is required.');
        }

        $this->columnMap = $normalized;
        $this->headingRow = max(1, $headingRow);
    }

    /**
     * @inheritDoc
     */
    public function model(array $row): ?Subject
    {
        $attributes = [
            'subject_code' => $this->getValueFromRow($row, 'subject_code'),
            'name' => $this->getValueFromRow($row, 'name'),
            'credit' => $this->getValueFromRow($row, 'credit'),
        ];

        $attributes = array_filter(
            $attributes,
            fn ($value) => !is_null($value) && $value !== ''
        );

        if (empty($attributes['subject_code'])) {
            // Skip rows without the required identifier.
            return null;
        }

        // Convert credit to integer if present
        if (isset($attributes['credit'])) {
            $attributes['credit'] = (int) $attributes['credit'];
        }

        return new Subject($attributes);
    }

    /**
     * Ensure existing subjects are updated based on their code.
     */
    public function uniqueBy(): string
    {
        return 'subject_code';
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
