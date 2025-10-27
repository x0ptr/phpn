<?php

$uri = $_SERVER['REQUEST_URI'] ?? '/about.php';
$page = 'about';
$title = 'About - PHPN';
$viewFile = 'about.php';

ob_start();
require __DIR__ . '/../views/' . $viewFile;
$content = ob_get_clean();

require __DIR__ . '/../views/layout.php';

