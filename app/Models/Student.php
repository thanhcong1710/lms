<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'date_of_birth', 'gender', 'accounting_id', 'id_lms'];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
