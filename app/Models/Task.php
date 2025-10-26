<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'points',
        'is_recurring',
        'recurrence_pattern',
        'created_by'
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class);
    }

    // Scopes
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeOneTime($query)
    {
        return $query->where('is_recurring', false);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}

