<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AttendanceRecord;
use App\Models\ExamSupervisor;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\Subject;

class ExamSchedule extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exam_schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_code',
        'exam_date',
        'exam_time',
        'room',
        'note',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'registered_count',
        'attended_count',
        'attendance_rate',
        'subject_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exam_date' => 'date',
        'exam_time' => 'datetime:H:i:s',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the subject for this exam schedule.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_code', 'subject_code');
    }

    /**
     * Get all attendance records for this schedule.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'exam_schedule_id');
    }

    /**
     * Get all exam supervisors for this schedule.
     */
    public function examSupervisors(): HasMany
    {
        return $this->hasMany(ExamSupervisor::class, 'exam_schedule_id');
    }

    /**
     * Get all students registered for this exam.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            Student::class,
            'attendance_records',
            'exam_schedule_id',
            'student_code',
            'id',
            'student_code'
        );
    }

    /**
     * Get all lecturers supervising this exam.
     */
    public function lecturers(): BelongsToMany
    {
        return $this->belongsToMany(
            Lecturer::class,
            'exam_supervisors',
            'exam_schedule_id',
            'lecturer_code'
        )->withTimestamps();
    }

    /**
     * Scope a query to only include exams for a specific subject.
     */
    public function scopeOfSubject($query, string $subjectCode)
    {
        return $query->where('subject_code', $subjectCode);
    }

    /**
     * Scope a query to only include exams on a specific date.
     */
    public function scopeOnDate($query, string $date)
    {
        return $query->whereDate('exam_date', $date);
    }

    /**
     * Scope a query to only include upcoming exams.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('exam_date', '>=', now()->toDateString())
            ->orderBy('exam_date')
            ->orderBy('exam_time');
    }

    /**
     * Scope a query to only include past exams.
     */
    public function scopePast($query)
    {
        return $query->where('exam_date', '<', now()->toDateString())
            ->orderBy('exam_date', 'desc')
            ->orderBy('exam_time', 'desc');
    }

    /**
     * Scope a query to only include exams in a specific room.
     */
    public function scopeInRoom($query, string $room)
    {
        return $query->where('room', $room);
    }

    /**
     * Get the total number of registered students.
     */
    public function getRegisteredCountAttribute(): int
    {
        return $this->attendanceRecords()->count();
    }
    /**
     * Get the total number of attended students.
     */
    public function getAttendedCountAttribute(): int
    {
        return $this->attendanceRecords()
            ->where('rekognition_result', 'match')
            ->count();
    }

    /**
     * Get the attendance rate percentage.
     */
    public function getAttendanceRateAttribute(): float
    {
        $registered = $this->registered_count;
        if ($registered === 0) {
            return 0;
        }

        return round(($this->attended_count / $registered) * 100, 2);
    }

    /**
     * Get the subject name via the related subject.
     */
    public function getSubjectNameAttribute(): ?string
    {
        return optional($this->subject)->name;
    }

    /**
     * Check if the exam is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->exam_date >= now()->toDateString();
    }

    /**
     * Check if the exam is past.
     */
    public function isPast(): bool
    {
        return $this->exam_date < now()->toDateString();
    }

    /**
     * Check if the exam is today.
     */
    public function isToday(): bool
    {
        return $this->exam_date->isToday();
    }

    /**
     * Get the full exam information.
     */
    public function getFullInfoAttribute(): string
    {
        $subjectName = $this->subject ? $this->subject->name : 'N/A';

        return sprintf(
            '%s - %s - %s - Phòng %s',
            $subjectName,
            $this->exam_date->format('d/m/Y'),
            $this->exam_time->format('H:i'),
            $this->room
        );
    }

    /**
     * Get the exam datetime combined.
     */
    public function getExamDateTimeAttribute(): string
    {
        return $this->exam_date->format('d/m/Y') . ' ' . $this->exam_time->format('H:i');
    }
}
