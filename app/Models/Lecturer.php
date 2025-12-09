<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lecturer extends Model
{
    use HasFactory;
    /** @var string */
    protected $primaryKey = 'lecturer_code';

    /** @var string */
    protected $keyType = 'string';

    /** @var bool */
    public $incrementing = false;

    /** @var array<int,string> */
    protected $fillable = [
        'lecturer_code',
        'user_id',
        'full_name',
        'email',
        'phone',
        'faculty_code',
    ];
    /** @var array<string, string> */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function faculty(): BelongsTo{
        return $this->belongsTo(Faculties::class,'faculty_code','faculty_code');
    }
    public function user(): BelongsTo{
        return $this->belongsTo(User::class,'user_id','id');
    }
}
