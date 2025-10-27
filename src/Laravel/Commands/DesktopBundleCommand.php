<?php

namespace PHPN\Laravel\Commands;

use Illuminate\Console\Command;

class DesktopBundleCommand extends Command
{
    protected $signature = 'desktop:bundle 
                            {name? : Application name}
                            {--icon= : Path to .icns icon file}';
    
    protected $description = 'Bundle Laravel app as a macOS .app';

    public function handle()
    {
        $appName = $this->argument('name') ?: config('app.name', 'Laravel App');
        $basePath = base_path();
        
        $phpnPath = base_path('vendor/bin/phpn');
        
        if (!file_exists($phpnPath)) {
            $this->error('PHPN not found. Install it with: composer require --dev phpn/phpn');
            return 1;
        }
        
        $this->info("Bundling Laravel as: {$appName}.app");
        
        $cmd = sprintf(
            '%s bundle %s %s',
            escapeshellarg($phpnPath),
            escapeshellarg($basePath),
            escapeshellarg($appName)
        );
        
        if ($icon = $this->option('icon')) {
            if (!file_exists($icon)) {
                $this->error("Icon file not found: {$icon}");
                return 1;
            }
            $cmd .= ' --icon=' . escapeshellarg($icon);
        }
        
        passthru($cmd, $exitCode);
        
        return $exitCode;
    }
}
