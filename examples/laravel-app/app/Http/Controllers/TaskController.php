<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Tag;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $task = Task::create($validated);

        if (!empty($validated['tags'])) {
            $task->tags()->attach($validated['tags']);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'task' => $task->load(['tags', 'project'])
            ]);
        }

        return redirect()->route('projects.show', $validated['project_id'])
            ->with('success', 'Task created successfully!');
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'status' => 'sometimes|required|in:todo,in_progress,completed',
            'due_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $task->update($validated);

        if (isset($validated['tags'])) {
            $task->tags()->sync($validated['tags']);
        }

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $task->markAsCompleted();
        } elseif (isset($validated['status']) && $validated['status'] !== 'completed' && $task->completed_at) {
            $task->markAsIncomplete();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'task' => $task->fresh(['tags', 'project'])
            ]);
        }

        return redirect()->route('projects.show', $task->project_id)
            ->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        $projectId = $task->project_id;
        $task->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Task deleted successfully!');
    }

    public function toggleComplete(Task $task)
    {
        if ($task->status === 'completed') {
            $task->markAsIncomplete();
        } else {
            $task->markAsCompleted();
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'task' => $task->fresh(['tags', 'project'])
            ]);
        }

        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.position' => 'required|integer|min:0',
            'tasks.*.status' => 'sometimes|in:todo,in_progress,completed',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            $update = ['position' => $taskData['position']];
            
            if (isset($taskData['status'])) {
                $update['status'] = $taskData['status'];
            }

            Task::where('id', $taskData['id'])->update($update);
        }

        return response()->json(['success' => true]);
    }
}
