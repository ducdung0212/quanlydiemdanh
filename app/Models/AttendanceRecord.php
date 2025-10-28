<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_schedule_id',
        'student_code',
        'rekognition_result',
        'confidence',
        'attendance_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confidence' => 'decimal:2',
        'attendance_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'student_name',
    ];

    /**
     * Get the student that the attendance record belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_code', 'student_code');
    }

    /**
     * Get the exam schedule that the attendance record belongs to.
     */
    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function getStudentName(): ?string
    {
        return $this->student?->full_name;
    }

    /**
     * Accessor for student_name attribute.
     *
     * @return string
     */
    public function getStudentNameAttribute(): string
    {
        return $this->getStudentName() ?? '';
    }

    /**
     * Scope a query to only include records for a specific student.
     */
    public function scopeOfStudent($query, string $studentCode)
    {
        return $query->where('student_code', $studentCode);
    }

    /**
     * Scope a query to only include records for a specific exam.
     */
    public function scopeOfExam($query, int $examScheduleId)
    {
        return $query->where('exam_schedule_id', $examScheduleId);
    }

    /**
     * Scope a query to only include matched records.
     */
    public function scopeMatched($query)
    {
        return $query->where('rekognition_result', 'match');
    }

    /**
     * Scope a query to only include not matched records.
     */
    public function scopeNotMatched($query)
    {
        return $query->where('rekognition_result', 'not_match');
    }

    /**
     * Scope a query to only include unknown records.
     */
    public function scopeUnknown($query)
    {
        return $query->where('rekognition_result', 'unknown');
    }

    /**
     * Scope a query to filter by confidence level.
     */
    public function scopeMinConfidence($query, float $minConfidence)
    {
        return $query->where('confidence', '>=', $minConfidence);
    }

    /**
     * Check if the attendance is successful (matched).
     */
    public function isSuccessful(): bool
    {
        return $this->rekognition_result === 'match';
    }

    /**
     * Check if the attendance failed (not matched).
     */
    public function isFailed(): bool
    {
        return $this->rekognition_result === 'not_match';
    }

    /**
     * Check if the attendance is unknown.
     */
    public function isUnknown(): bool
    {
        return $this->rekognition_result === 'unknown';
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->rekognition_result) {
            'match' => 'Khớp',
            'not_match' => 'Không khớp',
            'unknown' => 'Không xác định',
            default => 'N/A',
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->rekognition_result) {
            'match' => 'success',
            'not_match' => 'danger',
            'unknown' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get the full URL of the captured image.
     */
    public function getFullCapturedImageUrlAttribute(): string
    {
        if (str_starts_with($this->captured_image_url, 'http')) {
            return $this->captured_image_url;
        }

        return url($this->captured_image_url);
    }
}
