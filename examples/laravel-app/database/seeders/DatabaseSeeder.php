<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Tags
        $tags = [
            ['name' => 'Bug', 'color' => '#ef4444'],
            ['name' => 'Feature', 'color' => '#3b82f6'],
            ['name' => 'Documentation', 'color' => '#8b5cf6'],
            ['name' => 'Design', 'color' => '#ec4899'],
            ['name' => 'Testing', 'color' => '#10b981'],
            ['name' => 'Urgent', 'color' => '#f59e0b'],
        ];

        foreach ($tags as $tagData) {
            Tag::create($tagData);
        }

        // Create Projects with Tasks
        $this->createWebsiteProject();
        $this->createMobileAppProject();
        $this->createMarketingProject();
    }

    private function createWebsiteProject()
    {
        $project = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Complete redesign of company website with modern UI/UX',
            'color' => '#3b82f6',
            'position' => 0,
        ]);

        $tasks = [
            [
                'title' => 'Create wireframes for homepage',
                'description' => 'Design low-fidelity wireframes showing layout and content structure',
                'priority' => 'high',
                'status' => 'completed',
                'due_date' => now()->subDays(5),
                'completed_at' => now()->subDays(3),
                'tags' => ['Design'],
            ],
            [
                'title' => 'Implement responsive navigation',
                'description' => 'Build mobile-friendly navigation with hamburger menu',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => now()->addDays(2),
                'tags' => ['Feature', 'Design'],
            ],
            [
                'title' => 'Setup Tailwind CSS',
                'description' => 'Configure Tailwind CSS with custom color scheme',
                'priority' => 'medium',
                'status' => 'completed',
                'due_date' => now()->subDays(10),
                'completed_at' => now()->subDays(8),
                'tags' => ['Feature'],
            ],
            [
                'title' => 'Write accessibility documentation',
                'description' => 'Document WCAG 2.1 compliance and best practices',
                'priority' => 'low',
                'status' => 'todo',
                'due_date' => now()->addDays(14),
                'tags' => ['Documentation'],
            ],
            [
                'title' => 'Fix mobile menu overlay bug',
                'description' => 'Menu overlay not closing when clicking outside on iOS',
                'priority' => 'urgent',
                'status' => 'todo',
                'due_date' => now()->addDays(1),
                'tags' => ['Bug', 'Urgent'],
            ],
        ];

        $this->createTasksForProject($project, $tasks);
    }

    private function createMobileAppProject()
    {
        $project = Project::create([
            'name' => 'Mobile App Development',
            'description' => 'Native iOS and Android app for customer engagement',
            'color' => '#10b981',
            'position' => 1,
        ]);

        $tasks = [
            [
                'title' => 'Design app icon and splash screen',
                'description' => 'Create app icon in multiple sizes for both platforms',
                'priority' => 'high',
                'status' => 'completed',
                'due_date' => now()->subDays(15),
                'completed_at' => now()->subDays(12),
                'tags' => ['Design'],
            ],
            [
                'title' => 'Implement push notifications',
                'description' => 'Setup Firebase Cloud Messaging for notifications',
                'priority' => 'high',
                'status' => 'in_progress',
                'due_date' => now()->addDays(5),
                'tags' => ['Feature'],
            ],
            [
                'title' => 'Write unit tests for API layer',
                'description' => 'Achieve 80% code coverage for API service classes',
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => now()->addDays(7),
                'tags' => ['Testing'],
            ],
            [
                'title' => 'Setup CI/CD pipeline',
                'description' => 'Configure automated builds and deployments',
                'priority' => 'high',
                'status' => 'todo',
                'due_date' => now()->addDays(3),
                'tags' => ['Feature'],
            ],
        ];

        $this->createTasksForProject($project, $tasks);
    }

    private function createMarketingProject()
    {
        $project = Project::create([
            'name' => 'Q1 Marketing Campaign',
            'description' => 'Launch new product marketing campaign across all channels',
            'color' => '#f59e0b',
            'position' => 2,
        ]);

        $tasks = [
            [
                'title' => 'Create social media content calendar',
                'description' => 'Plan posts for Facebook, Twitter, Instagram, and LinkedIn',
                'priority' => 'high',
                'status' => 'completed',
                'due_date' => now()->subDays(20),
                'completed_at' => now()->subDays(18),
                'tags' => ['Design'],
            ],
            [
                'title' => 'Design email newsletter template',
                'description' => 'Create responsive email template for weekly newsletter',
                'priority' => 'medium',
                'status' => 'in_progress',
                'due_date' => now()->addDays(4),
                'tags' => ['Design'],
            ],
            [
                'title' => 'Write blog post about product launch',
                'description' => 'SEO-optimized blog post highlighting key features',
                'priority' => 'high',
                'status' => 'todo',
                'due_date' => now()->addDays(6),
                'tags' => ['Documentation'],
            ],
            [
                'title' => 'Schedule product demo webinar',
                'description' => 'Organize webinar with Q&A session for prospects',
                'priority' => 'urgent',
                'status' => 'todo',
                'due_date' => now()->addDays(2),
                'tags' => ['Urgent'],
            ],
            [
                'title' => 'Update landing page copy',
                'description' => 'Revise landing page content based on A/B test results',
                'priority' => 'medium',
                'status' => 'todo',
                'due_date' => now()->addDays(10),
                'tags' => ['Feature'],
            ],
            [
                'title' => 'Fix broken links in email campaign',
                'description' => 'Several links in last week\'s email are returning 404 errors',
                'priority' => 'urgent',
                'status' => 'todo',
                'due_date' => now(),
                'tags' => ['Bug', 'Urgent'],
            ],
        ];

        $this->createTasksForProject($project, $tasks);
    }

    private function createTasksForProject(Project $project, array $tasks)
    {
        foreach ($tasks as $index => $taskData) {
            $tagNames = $taskData['tags'] ?? [];
            unset($taskData['tags']);

            $task = $project->tasks()->create([
                ...$taskData,
                'position' => $index,
            ]);

            if (!empty($tagNames)) {
                $tagIds = Tag::whereIn('name', $tagNames)->pluck('id');
                $task->tags()->attach($tagIds);
            }
        }
    }
}
