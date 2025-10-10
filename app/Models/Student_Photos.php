<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student_Photos extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_photos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_code',
        'image_url',
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
     * Get the student that owns the photo.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_code', 'student_code');
    }

    /**
     * Scope a query to only include photos for a specific student.
     */
    public function scopeOfStudent($query, string $studentCode)
    {
        return $query->where('student_code', $studentCode);
    }

    /**
     * Get the full URL of the image.
     */
    public function getFullImageUrlAttribute(): string
    {
        // Nếu image_url đã là full URL (bắt đầu bằng http/https)
        if (str_starts_with($this->image_url, 'http')) {
            return $this->image_url;
        }
        
        // Nếu là relative path, thêm base URL
        return url($this->image_url);
    }

    /**
     * Check if the image file exists.
     */
    public function imageExists(): bool
    {
        if (str_starts_with($this->image_url, 'http')) {
            // URL từ S3 hoặc external storage
            return true; // Giả định URL hợp lệ
        }
        
        // Local file
        return file_exists(public_path($this->image_url));
    }
}