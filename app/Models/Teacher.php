<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ins_name', 'email', 'phone', 'id_lms', 'branch_id_lms', 'status', 'head', 'remark'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
