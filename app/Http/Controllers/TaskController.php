<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Get all tasks (filtered by role)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Task::with(['creator']);

        if ($user->isChild()) {
            // Children see only tasks assigned to them
            return TaskAssignment::with(['task'])
                ->where('child_id', $user->id)
                ->get()
                ->map(function ($assignment) {
                    $task = $assignment->task;
                    $task->status = $assignment->status;
                    $task->due_date = $assignment->due_date;
                    return $task;
                });
        } elseif ($user->isParent()) {
            // Parents see tasks they created and system tasks
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('is_recurring', true);
            });
        }
        // Admins see all tasks

        return $query->get();
    }

    /**
     * Create a new task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:chores,homework,extra_credit,behavior',
            'points' => 'required|numeric|min:0',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'required_if:is_recurring,true|nullable|string'
        ]);

        $task = Task::create([
            ...$validated,
            'created_by' => $request->user()->id
        ]);

        return response()->json($task, 201);
    }

    /**
     * Assign a task to a child
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'child_id' => 'required|exists:users,id',
            'due_date' => 'required|date|after:now'
        ]);

        $user = $request->user();
        $child = User::findOrFail($validated['child_id']);

        // Verify parent-child relationship
        if ($user->isParent() && $child->parent_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized. Not parent of this child.'], 403);
        }

        $assignment = TaskAssignment::create([
            'task_id' => $validated['task_id'],
            'child_id' => $validated['child_id'],
            'assigned_by' => $user->id,
            'status' => 'assigned',
            'due_date' => $validated['due_date']
        ]);

        return response()->json($assignment->load(['task', 'child']), 201);
    }

    /**
     * Mark a task as complete (child only)
     */
    public function complete(Request $request, TaskAssignment $task)
    {
        if ($task->child_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($task->status, ['assigned', 'in_progress'])) {
            return response()->json(['message' => 'Task cannot be completed'], 400);
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string'
        ]);

        $task->complete($validated['completion_notes'] ?? null);

        return response()->json($task->load('task'));
    }

    /**
     * Verify a completed task (parent only)
     */
    public function verify(Request $request, TaskAssignment $task)
    {
        $user = $request->user();
        
        // Check if user is parent of the child
        if (!$user->isParent() || $task->child->parent_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($task->status !== 'completed') {
            return response()->json(['message' => 'Task is not ready for verification'], 400);
        }

        $validated = $request->validate([
            'action' => 'required|in:verify,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string'
        ]);

        if ($validated['action'] === 'verify') {
            $task->verify();
            
            // Create allowance transaction
            DB::transaction(function () use ($task, $user) {
                $task->child->allowanceTransactions()->create([
                    'parent_id' => $user->id,
                    'amount' => $task->points_earned,
                    'type' => 'credit',
                    'description' => "Completed task: {$task->task->name}",
                    'task_assignments' => [$task->id]
                ]);
            });
        } else {
            $task->reject($validated['rejection_reason']);
        }

        return response()->json($task->load(['task', 'child']));
    }

    /**
     * Get tasks assigned to the authenticated child
     */
    public function assigned(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isChild()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $assignments = TaskAssignment::with(['task'])
            ->where('child_id', $user->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->orderBy('due_date')
            ->get();

        return response()->json($assignments);
    }

    /**
     * Update task details
     */
    public function update(Request $request, Task $task)
    {
        $user = $request->user();

        // Only creator or admin can update task
        if (!$user->isAdmin() && $task->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'category' => 'in:chores,homework,extra_credit,behavior',
            'points' => 'numeric|min:0',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'required_if:is_recurring,true|nullable|string'
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    /**
     * Delete a task
     */
    public function destroy(Request $request, Task $task)
    {
        $user = $request->user();

        // Only creator or admin can delete task
        if (!$user->isAdmin() && $task->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if task has any verified assignments
        if ($task->assignments()->verified()->exists()) {
            return response()->json(['message' => 'Cannot delete task with verified assignments'], 400);
        }

        $task->assignments()->delete();
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
