<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculties extends Model
{
    use HasFactory;

    protected $table = 'faculties';
    protected $primaryKey = 'falculty_code';
    public $incrementing = false; // vì khóa chính không phải auto increment
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
    ];

    /* ========================
     |   ĐỊNH NGHĨA QUAN HỆ
     ======================== */

    // Một khoa có nhiều lớp
    public function classes()
    {
        return $this->hasMany(ClassModel::class, 'faculty_code', 'faculty_code');
    }

    // Một khoa có nhiều giảng viên
    public function lecturers()
    {
        return $this->hasMany(Lecturer::class, 'faculty_code', 'faculty_code');
    }
}
