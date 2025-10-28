<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = Project::withCount(['tasks', 'completedTasks'])
            ->orderBy('position')
            ->get();

        $recentTasks = Task::with(['project', 'tags'])
            ->latest()
            ->limit(10)
            ->get();

        $overdueTasks = Task::with(['project', 'tags'])
            ->overdue()
            ->orderBy('due_date')
            ->get();

        $todayTasks = Task::with(['project', 'tags'])
            ->whereDate('due_date', today())
            ->where('status', '!=', 'completed')
            ->get();

        $stats = [
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::completed()->count(),
            'in_progress_tasks' => Task::inProgress()->count(),
            'overdue_tasks' => Task::overdue()->count(),
        ];

        return view('dashboard', compact('projects', 'recentTasks', 'overdueTasks', 'todayTasks', 'stats'));
    }
}
