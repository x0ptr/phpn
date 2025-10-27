<?php

$uri = $_SERVER['REQUEST_URI'] ?? '/index.php';
$page = 'home';
$viewFile = 'home.php';

if (strpos($uri, 'features.php') !== false) {
    $page = 'features';
    $viewFile = 'features.php';
    $title = 'Features - PHPN';
} elseif (strpos($uri, 'about.php') !== false) {
    $page = 'about';
    $viewFile = 'about.php';
    $title = 'About - PHPN';
} else {
    $page = 'home';
    $viewFile = 'home.php';
    $title = 'Home - PHPN';
}

ob_start();
require __DIR__ . '/../views/' . $viewFile;
$content = ob_get_clean();

require __DIR__ . '/../views/layout.php';

