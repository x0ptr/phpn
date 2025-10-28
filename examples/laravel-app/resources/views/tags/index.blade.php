@extends('layouts.app')

@section('title', 'Tags')

@section('content')
<div class="space-y-6">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Tags</h2>
                <button onclick="showAddTagForm()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    + New Tag
                </button>
            </div>
            
            <!-- Add Tag Form (Hidden by default) -->
            <div id="add-tag-form" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
                <form action="{{ route('tags.store') }}" method="POST" class="flex gap-3 items-end">
                    @csrf
                    <div class="flex-1">
                        <label for="name" class="block text-sm font-medium text-gray-700">Tag Name</label>
                        <input type="text" name="name" id="name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2 border">
                    </div>
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700">Color</label>
                        <input type="color" name="color" id="color" value="#6b7280"
                               class="mt-1 h-10 w-20 rounded border-gray-300">
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        Add Tag
                    </button>
                    <button type="button" onclick="hideAddTagForm()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                </form>
            </div>
            
            @if($tags->isEmpty())
                <p class="text-gray-500 text-center py-8">No tags yet. Create your first tag to organize your tasks!</p>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($tags as $tag)
                    <div class="border rounded-lg p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded" style="background-color: {{ $tag->color }}"></div>
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $tag->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $tag->tasks_count }} tasks</p>
                            </div>
                        </div>
                        <form action="{{ route('tags.destroy', $tag) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tag?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function showAddTagForm() {
    document.getElementById('add-tag-form').classList.remove('hidden');
}

function hideAddTagForm() {
    document.getElementById('add-tag-form').classList.add('hidden');
}
</script>
@endpush
@endsection
