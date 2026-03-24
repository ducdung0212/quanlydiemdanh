<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class UsersImport implements
    ToModel,
    WithHeadingRow,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    /**
     * @var array<string, string>
     */
    protected array $columnMap;

    /**
     * @var string
     */
    protected string $role;

    /**
     * @var string|null
     */
    protected ?string $defaultPassword;

    /**
     * @var bool
     */
    protected bool $usePasswordColumn;

    /**
     * @var int
     */
    protected int $headingRow;

    /**
     * @param array<string, string|null> $columnMap
     * @param string $role
     * @param string|null $defaultPassword
     * @param bool $usePasswordColumn
     * @param int $headingRow
     */
    public function __construct(
        array $columnMap,
        string $role,
        ?string $defaultPassword = null,
        bool $usePasswordColumn = false,
        int $headingRow = 1
    ) {
        $normalized = collect($columnMap)
            ->filter(fn($column) => filled($column))
            ->map(fn($column) => $this->normalizeColumnKey($column))
            ->toArray();

        if (empty($normalized['email'])) {
            throw new InvalidArgumentException('Mapping for "email" is required.');
        }

        if (!in_array($role, ['admin', 'lecturer', 'student'])) {
            throw new InvalidArgumentException("Invalid role: {$role}");
        }

        if (!$usePasswordColumn && !$defaultPassword) {
            throw new InvalidArgumentException('Either default password or password column must be provided.');
        }

        if ($role === 'student' && empty($normalized['student_code'])) {
            throw new InvalidArgumentException('Mapping for "student_code" is required for student role.');
        }

        if ($usePasswordColumn && empty($normalized['password'])) {
            throw new InvalidArgumentException('Mapping for "password" is required when using password from file.');
        }

        $this->columnMap = $normalized;
        $this->role = $role;
        $this->defaultPassword = $defaultPassword;
        $this->usePasswordColumn = $usePasswordColumn;
        $this->headingRow = max(1, $headingRow);
    }

    /**
     * @param array $row
     * @return User|null
     */
    public function model(array $row): ?User
    {
        $email = $this->getValueFromRow($row, 'email');
        if (!$email) {
            return null; // Skip rows without required identifier.
        }

        $name = $this->getValueFromRow($row, 'name') ?? $email;

        $password = $this->defaultPassword;
        if ($this->usePasswordColumn) {
            $password = $this->getValueFromRow($row, 'password');
            if (!$password) {
                return null;
            }
        }

        $studentCode = null;
        if ($this->role === 'student') {
            $studentCode = $this->getValueFromRow($row, 'student_code');
            if (!$studentCode) {
                return null;
            }
        }

        return new User([
            'email' => $email,
            'name' => $name,
            'role' => $this->role,
            'password' => Hash::make($password),
            'student_code' => $studentCode,
        ]);
    }

    public function uniqueBy(): string
    {
        return 'email';
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

    protected function getValueFromRow(array $row, string $key): ?string
    {
        $columnKey = $this->columnMap[$key] ?? null;

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
