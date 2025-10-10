<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam_Rosters extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exam_rosters';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_schedule_id',
        'student_code',
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
     * Get the student in the exam roster.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_code', 'student_code');
    }

    /**
     * Get the exam schedule for this roster entry.
     */
    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    /**
     * Scope a query to only include rosters for a specific student.
     */
    public function scopeOfStudent($query, string $studentCode)
    {
        return $query->where('student_code', $studentCode);
    }

    /**
     * Scope a query to only include rosters for a specific exam.
     */
    public function scopeOfExam($query, int $examScheduleId)
    {
        return $query->where('exam_schedule_id', $examScheduleId);
    }

    /**
     * Check if the student has attended this exam.
     */
    public function hasAttended(): bool
    {
        return AttendanceRecord::where('exam_schedule_id', $this->exam_schedule_id)
            ->where('student_code', $this->student_code)
            ->exists();
    }

    /**
     * Get the attendance record for this roster entry.
     */
    public function getAttendanceRecord()
    {
        return AttendanceRecord::where('exam_schedule_id', $this->exam_schedule_id)
            ->where('student_code', $this->student_code)
            ->first();
    }

    /**
     * Get the attendance status.
     */
    public function getAttendanceStatusAttribute(): string
    {
        $attendance = $this->getAttendanceRecord();
        
        if (!$attendance) {
            return 'Chưa điểm danh';
        }
        
        return match($attendance->rekognition_result) {
            'match' => 'Đã điểm danh',
            'not_match' => 'Không khớp',
            'unknown' => 'Không xác định',
            default => 'N/A',
        };
    }
}