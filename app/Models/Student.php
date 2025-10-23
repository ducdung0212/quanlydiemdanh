<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'student_code';

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
        'student_code',
        'class_code',
        'full_name',
        'email',
        'phone',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>*/
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the class that the student belongs to.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_code', 'class_code');
    }

    /**
     * Get all photos for the student.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(StudentPhoto::class, 'student_code', 'student_code');
    }

    /**
     * Get all attendance records for the student.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'student_code', 'student_code');
    }


    /**
     * Get all exam schedules that the student is registered for.
     */
    public function examSchedules(): BelongsToMany
    {
        return $this->belongsToMany(
            ExamSchedule::class,
            'attendance_records',
            'student_code',
            'exam_schedule_id',
            'student_code',
            'id'
        );
    }

    /**
     * Get the faculty through the class relationship.
     */
    public function faculty()
    {
        return $this->hasOneThrough(
            Faculty::class,
            Classes::class,
            'class_code', // Foreign key on classes table
            'code',       // Foreign key on faculties table
            'class_code', // Local key on students table
            'faculty_code' // Local key on classes table
        );
    }

    /**
     * Scope a query to only include students from a specific class.
     */
    public function scopeOfClass($query, string $classCode)
    {
        return $query->where('class_code', $classCode);
    }

    /**
     * Scope a query to only include students from a specific faculty.
     */
    public function scopeOfFaculty($query, string $facultyCode)
    {
        return $query->whereHas('class', function ($q) use ($facultyCode) {
            $q->where('faculty_code', $facultyCode);
        });
    }

    /**
     * Scope a query to search students by name or code.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('student_code', 'like', "%{$search}%")
              ->orWhere('full_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Get the student's full information with class and faculty.
     */
    public function getFullInfoAttribute(): string
    {
        return "{$this->student_code} - {$this->full_name}" . 
               ($this->class ? " ({$this->class->class_name})" : "");
    }

    /**
     * Check if student has any photos.
     */
    public function hasPhotos(): bool
    {
        return $this->photos()->exists();
    }

    /**
     * Get attendance status for a specific exam.
     */
    public function getAttendanceForExam(int $examScheduleId)
    {
        return $this->attendanceRecords()
            ->where('exam_schedule_id', $examScheduleId)
            ->first();
    }
}