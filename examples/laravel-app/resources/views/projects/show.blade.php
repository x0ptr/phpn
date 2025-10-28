@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="space-y-6">
    <!-- Project Header -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded" style="background-color: {{ $project->color }}"></div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h2>
                    </div>
                    @if($project->description)
                        <p class="mt-2 text-gray-600">{{ $project->description }}</p>
                    @endif
                    <div class="mt-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>{{ $project->completed_tasks_count }} / {{ $project->tasks_count }} tasks completed</span>
                            <span>{{ $project->progress_percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full" style="width: {{ $project->progress_percentage }}%; background-color: {{ $project->color }}"></div>
                        </div>
                    </div>
                </div>
                <div class="ml-4 flex gap-2">
                    <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Edit
                    </a>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Task</h3>
            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <input type="text" name="title" placeholder="Task title" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 border">
                    </div>
                    
                    <div class="sm:col-span-2">
                        <textarea name="description" rows="2" placeholder="Task description (optional)"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 border"></textarea>
                    </div>
                    
                    <div>
                        <select name="priority" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 border">
                            <option value="low">Low Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="high">High Priority</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    
                    <div>
                        <input type="date" name="due_date" 
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 border">
                    </div>
                </div>
                
                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        Add Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- To Do Column -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 To Do</h3>
            <div class="space-y-3" data-status="todo">
                @foreach($tasks->get('todo', collect()) as $task)
                    @include('tasks.card', ['task' => $task])
                @endforeach
            </div>
        </div>

        <!-- In Progress Column -->
        <div class="bg-blue-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ In Progress</h3>
            <div class="space-y-3" data-status="in_progress">
                @foreach($tasks->get('in_progress', collect()) as $task)
                    @include('tasks.card', ['task' => $task])
                @endforeach
            </div>
        </div>

        <!-- Completed Column -->
        <div class="bg-green-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">✅ Completed</h3>
            <div class="space-y-3" data-status="completed">
                @foreach($tasks->get('completed', collect()) as $task)
                    @include('tasks.card', ['task' => $task])
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Drag and drop functionality
let draggedElement = null;

document.addEventListener('DOMContentLoaded', function() {
    initDragAndDrop();
});

function initDragAndDrop() {
    const taskCards = document.querySelectorAll('[draggable="true"]');
    const columns = document.querySelectorAll('[data-status]');
    
    taskCards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });
    
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
    });
}

function handleDragStart(e) {
    draggedElement = this;
    this.classList.add('task-dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

function handleDragEnd(e) {
    this.classList.remove('task-dragging');
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

async function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    if (draggedElement) {
        const newStatus = this.getAttribute('data-status');
        const taskId = draggedElement.getAttribute('data-task-id');
        
        // Move the element in DOM
        this.appendChild(draggedElement);
        
        // Update on server
        try {
            const response = await fetchJSON(`/tasks/${taskId}`, {
                method: 'PUT',
                body: JSON.stringify({ status: newStatus })
            });
            
            if (response.success) {
                // Reload to show updated stats
                window.location.reload();
            }
        } catch (error) {
            console.error('Error updating task:', error);
            alert('Failed to update task status');
        }
    }
    
    return false;
}

// Quick complete toggle
async function toggleComplete(taskId) {
    try {
        const response = await fetchJSON(`/tasks/${taskId}/toggle`, {
            method: 'POST'
        });
        
        if (response.success) {
            window.location.reload();
        }
    } catch (error) {
        console.error('Error toggling task:', error);
    }
}

// Delete task
async function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }
    
    try {
        const response = await fetchJSON(`/tasks/${taskId}`, {
            method: 'DELETE'
        });
        
        if (response.success) {
            window.location.reload();
        }
    } catch (error) {
        console.error('Error deleting task:', error);
    }
}
</script>
@endpush
@endsection
