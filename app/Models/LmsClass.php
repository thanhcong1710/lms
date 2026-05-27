<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = ['cls_name', 'teacher_id_lms', 'pre_teacher_id_lms', 'level_name', 'cls_type', 'cls_status', 'branch_id_lms', 'class_seq'];
}
