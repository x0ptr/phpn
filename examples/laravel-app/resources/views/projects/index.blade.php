@extends('layouts.app')

@section('title', 'All Projects')

@section('content')
<div class="space-y-6">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">All Projects</h2>
                <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    + New Project
                </a>
            </div>
            
            @if($projects->isEmpty())
                <p class="text-gray-500 text-center py-8">No projects yet. Create your first project to get started!</p>
            @else
                <div class="space-y-4">
                    @foreach($projects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="block hover:shadow-lg transition-shadow">
                        <div class="border-l-4 bg-gray-50 p-6 rounded-r-lg" style="border-color: {{ $project->color }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="text-xl font-semibold text-gray-900">{{ $project->name }}</h4>
                                    @if($project->description)
                                        <p class="text-sm text-gray-600 mt-2">{{ $project->description }}</p>
                                    @endif
                                </div>
                                <div class="ml-4 flex gap-2">
                                    <a href="{{ route('projects.edit', $project) }}" 
                                       onclick="event.stopPropagation()"
                                       class="text-gray-400 hover:text-blue-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>{{ $project->completed_tasks_count }} / {{ $project->tasks_count }} tasks completed</span>
                                    <span class="font-semibold">{{ $project->progress_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full transition-all duration-300" 
                                         style="width: {{ $project->progress_percentage }}%; background-color: {{ $project->color }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
