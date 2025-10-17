<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classes extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'class_code';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'class_code',
        'class_name',
        'faculty_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the faculty that the class belongs to.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_code', 'faculty_code');
    }

    /**
     * Get all students in the class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_code', 'class_code');
    }

    /**
     * Scope a query to only include classes from a specific faculty.
     */
    public function scopeOfFaculty($query, string $facultyCode)
    {
        return $query->where('faculty_code', $facultyCode);
    }

    /**
     * Scope a query to search classes by name or code.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('class_code', 'like', "%{$search}%")
              ->orWhere('class_name', 'like', "%{$search}%");
        });
    }

    /**
     * Get the total number of students in the class.
     */
    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }

    /**
     * Get the class's full information.
     */
    public function getFullInfoAttribute(): string
    {
        return "{$this->class_code} - {$this->class_name}" . 
               ($this->faculty ? " ({$this->faculty->name})" : "");
    }
}