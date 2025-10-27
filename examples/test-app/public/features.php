<?php

$uri = $_SERVER['REQUEST_URI'] ?? '/features.php';
$page = 'features';
$title = 'Features - PHPN';
$viewFile = 'features.php';

ob_start();
require __DIR__ . '/../views/' . $viewFile;
$content = ob_get_clean();

require __DIR__ . '/../views/layout.php';

