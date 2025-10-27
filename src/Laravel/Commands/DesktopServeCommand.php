<?php

namespace PHPN\Laravel\Commands;

use Illuminate\Console\Command;

class DesktopServeCommand extends Command
{
    protected $signature = 'desktop:serve 
                            {entry=public/index.php : The entry point file}
                            {--width=1200 : Window width}
                            {--height=800 : Window height}
                            {--title= : Window title}';
    
    protected $description = 'Run Laravel as a native desktop application';

    public function handle()
    {
        $entryFile = $this->argument('entry');
        $fullPath = base_path($entryFile);
        
        if (!file_exists($fullPath)) {
            $this->error("Entry file not found: $fullPath");
            return 1;
        }
        
        $phpnPath = base_path('vendor/bin/phpn');
        
        if (!file_exists($phpnPath)) {
            $this->error('PHPN not found. Install it with: composer require --dev phpn/phpn');
            return 1;
        }
        
        $width = $this->option('width') ?: config('phpn.window.width', 1200);
        $height = $this->option('height') ?: config('phpn.window.height', 800);
        $title = $this->option('title') ?: config('phpn.window.title', config('app.name', 'PHPN Application'));
        
        $this->info('Starting Laravel desktop application...');
        $this->line('Entry point: ' . $entryFile);
        $this->line('Window size: ' . $width . 'x' . $height);
        if ($title) {
            $this->line('Window title: ' . $title);
        }
        $this->newLine();
        
        $cmd = sprintf(
            '%s run %s --width=%d --height=%d',
            escapeshellarg($phpnPath),
            escapeshellarg($fullPath),
            $width,
            $height
        );
        
        if ($title) {
            $cmd .= ' --title=' . escapeshellarg($title);
        }
        
        passthru($cmd, $exitCode);
        
        return $exitCode;
    }
}
