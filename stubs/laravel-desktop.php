<?php

/**
 * PHPN Desktop Entry Point for Laravel
 * 
 * This file properly bootstraps Laravel for desktop execution.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// Capture the request from PHP superglobals (populated by PHPN runtime)
$request = Request::capture();

// Handle the request and send response
$response = $app->handleRequest($request);

// Send the response
$response->send();
