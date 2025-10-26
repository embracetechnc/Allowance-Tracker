<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TaskAssignment;
use App\Models\AllowanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AllowanceController extends Controller
{
    /**
     * Calculate allowance for a child based on completed tasks
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $user = $request->user();
        $child = User::findOrFail($validated['child_id']);

        // Verify parent-child relationship
        if ($child->parent_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized. Not parent of this child.'], 403);
        }

        // Get completed and verified tasks within date range
        $completedTasks = TaskAssignment::with('task')
            ->where('child_id', $child->id)
            ->where('status', 'verified')
            ->whereBetween('verified_at', [$validated['start_date'], $validated['end_date']])
            ->get();

        // Calculate points and amounts
        $totalPoints = $completedTasks->sum('points_earned');
        $baseAllowance = $child->weekly_allowance_rate;
        $bonusAmount = $totalPoints * 0.25; // $0.25 per point

        $calculation = [
            'period_start' => $validated['start_date'],
            'period_end' => $validated['end_date'],
            'tasks_completed' => $completedTasks->count(),
            'total_points' => $totalPoints,
            'base_allowance' => $baseAllowance,
            'bonus_amount' => $bonusAmount,
            'total_amount' => $baseAllowance + $bonusAmount,
            'tasks' => $completedTasks->map(function ($task) {
                return [
                    'name' => $task->task->name,
                    'completed_at' => $task->completed_at,
                    'verified_at' => $task->verified_at,
                    'points_earned' => $task->points_earned
                ];
            })
        ];

        return response()->json($calculation);
    }

    /**
     * Process allowance payment
     */
    public function payout(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'payment_method' => 'required|string'
        ]);

        $user = $request->user();
        $child = User::findOrFail($validated['child_id']);

        // Verify parent-child relationship
        if ($child->parent_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized. Not parent of this child.'], 403);
        }

        // Get tasks for this period
        $tasks = TaskAssignment::where('child_id', $child->id)
            ->where('status', 'verified')
            ->whereBetween('verified_at', [$validated['period_start'], $validated['period_end']])
            ->get();

        try {
            DB::transaction(function () use ($validated, $user, $child, $tasks) {
                // Create allowance transaction
                $transaction = AllowanceTransaction::create([
                    'child_id' => $child->id,
                    'parent_id' => $user->id,
                    'amount' => $validated['amount'],
                    'type' => 'credit',
                    'description' => sprintf(
                        'Allowance for period %s to %s',
                        Carbon::parse($validated['period_start'])->format('M d'),
                        Carbon::parse($validated['period_end'])->format('M d')
                    ),
                    'task_assignments' => $tasks->pluck('id')->toArray(),
                    'payment_method' => $validated['payment_method'],
                    'paid_at' => now()
                ]);

                // Update child's balance
                $child->increment('allowance_balance', $validated['amount']);
            });

            return response()->json([
                'message' => 'Allowance paid successfully',
                'new_balance' => $child->allowance_balance
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to process allowance payment'], 500);
        }
    }

    /**
     * Get allowance history for a child
     */
    public function history(Request $request)
    {
        $user = $request->user();
        
        // If child, get own history
        if ($user->isChild()) {
            $transactions = $user->allowanceTransactions()
                ->with('parent')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        // If parent, must provide child_id
        else {
            $validated = $request->validate([
                'child_id' => 'required|exists:users,id'
            ]);

            $child = User::findOrFail($validated['child_id']);
            
            if ($child->parent_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized. Not parent of this child.'], 403);
            }

            $transactions = $child->allowanceTransactions()
                ->with('parent')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'transactions' => $transactions,
            'summary' => [
                'total_earned' => $transactions->where('type', 'credit')->sum('amount'),
                'total_spent' => $transactions->where('type', 'debit')->sum('amount'),
                'current_balance' => $transactions->where('type', 'credit')->sum('amount') - 
                                   $transactions->where('type', 'debit')->sum('amount')
            ]
        ]);
    }

    /**
     * Update weekly allowance rate
     */
    public function updateRate(Request $request)
    {
        $validated = $request->validate([
            'child_id' => 'required|exists:users,id',
            'weekly_rate' => 'required|numeric|min:0'
        ]);

        $user = $request->user();
        $child = User::findOrFail($validated['child_id']);

        if ($child->parent_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized. Not parent of this child.'], 403);
        }

        $child->update([
            'weekly_allowance_rate' => $validated['weekly_rate']
        ]);

        return response()->json([
            'message' => 'Weekly allowance rate updated successfully',
            'new_rate' => $child->weekly_allowance_rate
        ]);
    }
}
