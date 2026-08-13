<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'hrm_id',
        'password',
        'role',
        'branch_id',
        'teacher_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeamLeader(): bool
    {
        return $this->role === 'team_leader';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Get branch_id_lms values that this user can access.
     * Admin: all, Team Leader: own branch, Teacher: own branch via teacher
     */
    public function getAccessibleBranchLmsIds(): ?array
    {
        if ($this->isAdmin()) {
            return null; // null means all
        }

        if ($this->isTeamLeader() && $this->branch_id) {
            $branch = Branch::find($this->branch_id);
            return $branch ? [$branch->id_lms] : [];
        }

        if ($this->isTeacher() && $this->teacher_id) {
            $teacher = Teacher::find($this->teacher_id);
            return $teacher ? [$teacher->branch_id_lms] : [];
        }

        return [];
    }

    /**
     * Get teacher_id_lms that this user manages (for teacher role).
     */
    public function getTeacherIdLms(): ?string
    {
        if ($this->isTeacher() && $this->teacher_id) {
            $teacher = Teacher::find($this->teacher_id);
            return $teacher ? $teacher->id_lms : null;
        }
        return null;
    }

    /**
     * Scope a LmsClass query based on user role.
     */
    public function scopeClasses($query)
    {
        if ($this->isAdmin()) {
            return $query;
        }

        if ($this->isTeacher()) {
            $teacherIdLms = $this->getTeacherIdLms();
            return $query->where(function ($q) use ($teacherIdLms) {
                if ($teacherIdLms) {
                    $q->where('teacher_id_lms', $teacherIdLms);
                }
                if ($this->teacher_id) {
                    $q->orWhere('teacher_id', $this->teacher_id);
                }
            });
        }

        if ($this->isTeamLeader()) {
            $branchIds = $this->getAccessibleBranchLmsIds();
            return $query->where(function ($q) use ($branchIds) {
                if ($branchIds !== null) {
                    $q->whereIn('branch_id_lms', $branchIds);
                }
                if ($this->branch_id) {
                    $q->orWhere('branch_id', $this->branch_id);
                }
            });
        }

        return $query;
    }

    /**
     * Scope a Student query based on user role.
     */
    public function scopeStudents($query)
    {
        if ($this->isAdmin()) {
            return $query;
        }

        $classQuery = $this->scopeClasses(LmsClass::query());
        $classIds = $classQuery->pluck('id')->toArray();
        $studentIds = Contract::whereIn('class_id', $classIds)->pluck('student_id')->unique()->toArray();

        return $query->whereIn('id', $studentIds);
    }

    /**
     * Scope a Contract query based on user role.
     */
    public function scopeContracts($query)
    {
        if ($this->isAdmin()) {
            return $query;
        }

        $classQuery = $this->scopeClasses(LmsClass::query());
        $classIds = $classQuery->pluck('id')->toArray();

        return $query->whereIn('class_id', $classIds);
    }
}
