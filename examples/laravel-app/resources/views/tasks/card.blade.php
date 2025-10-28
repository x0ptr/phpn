<div class="bg-white rounded-lg shadow p-4 cursor-move" draggable="true" data-task-id="{{ $task->id }}">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-start gap-2">
                <input type="checkbox" 
                       onchange="toggleComplete({{ $task->id }})"
                       {{ $task->status === 'completed' ? 'checked' : '' }}
                       class="mt-1 h-4 w-4 text-blue-600 rounded border-gray-300">
                <div class="flex-1">
                    <h4 class="font-medium text-gray-900 {{ $task->status === 'completed' ? 'line-through text-gray-500' : '' }}">
                        {{ $task->title }}
                    </h4>
                    @if($task->description)
                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($task->description, 100) }}</p>
                    @endif
                </div>
            </div>
            
            <div class="mt-3 flex flex-wrap gap-2 items-center">
                <!-- Priority Badge -->
                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                    {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $task->priority === 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                    {{ $task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $task->priority === 'low' ? 'bg-green-100 text-green-800' : '' }}">
                    {{ ucfirst($task->priority) }}
                </span>
                
                <!-- Due Date -->
                @if($task->due_date)
                    <span class="text-xs text-gray-500 {{ $task->is_overdue ? 'text-red-600 font-semibold' : '' }}">
                        📅 {{ $task->due_date->format('M d') }}
                        @if($task->is_overdue)
                            (Overdue!)
                        @endif
                    </span>
                @endif
                
                <!-- Tags -->
                @foreach($task->tags as $tag)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                          style="background-color: {{ $tag->color }}22; color: {{ $tag->color }}">
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        </div>
        
        <button onclick="deleteTask({{ $task->id }})" class="ml-2 text-gray-400 hover:text-red-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
