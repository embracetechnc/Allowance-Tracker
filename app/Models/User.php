<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'parent_id',
        'allowance_balance',
        'weekly_allowance_rate'
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'allowance_balance' => 'decimal:2',
        'weekly_allowance_rate' => 'decimal:2',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(TaskAssignment::class, 'child_id');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function allowanceTransactions()
    {
        return $this->hasMany(AllowanceTransaction::class, 'child_id');
    }

    // Role checks
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isParent()
    {
        return $this->role === 'parent';
    }

    public function isChild()
    {
        return $this->role === 'child';
    }
}

