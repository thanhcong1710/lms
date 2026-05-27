<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'class_id', 'branch_id', 'enrolment_start_date', 'enrolment_last_date', 'valid_cd', 'status', 'remark'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lmsClass()
    {
        return $this->belongsTo(LmsClass::class, 'class_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
