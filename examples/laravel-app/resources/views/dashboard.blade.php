@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-3xl">📝</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Tasks</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $stats['total_tasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-3xl">✅</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                            <dd class="text-3xl font-semibold text-green-600">{{ $stats['completed_tasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-3xl">⚡</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                            <dd class="text-3xl font-semibold text-blue-600">{{ $stats['in_progress_tasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-3xl">⚠️</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Overdue</dt>
                            <dd class="text-3xl font-semibold text-red-600">{{ $stats['overdue_tasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Grid -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Your Projects</h3>
                <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    + New Project
                </a>
            </div>
            
            @if($projects->isEmpty())
                <p class="text-gray-500 text-center py-8">No projects yet. Create your first project to get started!</p>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($projects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="block hover:shadow-lg transition-shadow">
                        <div class="border-l-4 bg-gray-50 p-4 rounded-r-lg" style="border-color: {{ $project->color }}">
                            <h4 class="text-lg font-semibold text-gray-900">{{ $project->name }}</h4>
                            @if($project->description)
                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($project->description, 80) }}</p>
                            @endif
                            <div class="mt-3">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>{{ $project->completed_tasks_count }} / {{ $project->tasks_count }} tasks</span>
                                    <span>{{ $project->progress_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: {{ $project->progress_percentage }}%; background-color: {{ $project->color }}"></div>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Due Today -->
        @if($todayTasks->isNotEmpty())
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">📅 Due Today</h3>
                <ul class="space-y-3">
                    @foreach($todayTasks as $task)
                    <li class="border-l-4 pl-3 py-2" style="border-color: {{ $task->project->color }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $task->title }}</p>
                                <p class="text-sm text-gray-500">{{ $task->project->name }}</p>
                            </div>
                            <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $task->priority === 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                                {{ $task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $task->priority === 'low' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Overdue Tasks -->
        @if($overdueTasks->isNotEmpty())
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">⚠️ Overdue Tasks</h3>
                <ul class="space-y-3">
                    @foreach($overdueTasks as $task)
                    <li class="border-l-4 border-red-500 pl-3 py-2 bg-red-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $task->title }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $task->project->name }} • Due {{ $task->due_date->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <!-- Recent Tasks -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">🕒 Recent Tasks</h3>
            @if($recentTasks->isEmpty())
                <p class="text-gray-500 text-center py-4">No tasks yet.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach($recentTasks as $task)
                    <li class="py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <p class="font-medium text-gray-900 {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                                        {{ $task->title }}
                                    </p>
                                    <span class="ml-2 text-sm text-gray-500">in {{ $task->project->name }}</span>
                                </div>
                                @if($task->tags->isNotEmpty())
                                <div class="mt-1 flex gap-1">
                                    @foreach($task->tags as $tag)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                          style="background-color: {{ $tag->color }}22; color: {{ $tag->color }}">
                                        {{ $tag->name }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            <div class="ml-4 text-sm text-gray-500">
                                {{ $task->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
