<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    public function incompleteTasks(): HasMany
    {
        return $this->tasks()->where('status', '!=', 'completed');
    }

    public function completedTasks(): HasMany
    {
        return $this->tasks()->where('status', 'completed');
    }

    public function getTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    public function getCompletedTasksCountAttribute(): int
    {
        return $this->completedTasks()->count();
    }

    public function getProgressPercentageAttribute(): int
    {
        $total = $this->tasks_count;
        if ($total === 0) {
            return 0;
        }
        return (int) round(($this->completed_tasks_count / $total) * 100);
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (is_null($project->position)) {
                $project->position = static::max('position') + 1 ?? 0;
            }
        });
    }
}
