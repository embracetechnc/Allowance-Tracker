<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowanceTransaction extends Model
{
    protected $fillable = [
        'child_id',
        'parent_id',
        'amount',
        'type',
        'description',
        'task_assignments',
        'paid_at',
        'payment_method',
        'payment_reference'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'task_assignments' => 'array',
        'paid_at' => 'datetime'
    ];

    // Relationships
    public function child()
    {
        return $this->belongsTo(User::class, 'child_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Scopes
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNull('paid_at');
    }

    // Methods
    public function markAsPaid($method, $reference = null)
    {
        $this->update([
            'paid_at' => now(),
            'payment_method' => $method,
            'payment_reference' => $reference
        ]);

        // Update child's balance
        if ($this->type === 'credit') {
            $this->child->increment('allowance_balance', $this->amount);
        } else {
            $this->child->decrement('allowance_balance', $this->amount);
        }
    }
}
