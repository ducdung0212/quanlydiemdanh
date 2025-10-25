<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSupervisor extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'exam_supervisors';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'exam_schedule_id',
        'lecturer_code',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'lecturer_name',
    ];

    /**
     * Get the exam schedule this supervisor is assigned to.
     */
    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    /**
     * Get the lecturer that supervises the exam.
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_code', 'lecturer_code');
    }

    /**
     * Scope supervisors by exam schedule id.
     */
    public function scopeForSchedule($query, int $examScheduleId)
    {
        return $query->where('exam_schedule_id', $examScheduleId);
    }

    /**
     * Scope supervisors by lecturer code.
     */
    public function scopeOfLecturer($query, string $lecturerCode)
    {
        return $query->where('lecturer_code', $lecturerCode);
    }

    /**
     * Get the lecturer's name.
     *
     * @return string
     */
    public function getLecturerName(): string
    {
        return $this->lecturer?->full_name ?? '';
    }

    /**
     * Accessor for lecturer_name attribute.
     *
     * @return string
     */
    public function getLecturerNameAttribute(): string
    {
        return $this->getLecturerName();
    }
}
