<?php

$uri = $_SERVER['REQUEST_URI'] ?? '/files.php';
$page = 'files';
$title = 'File Browser - PHPN';
$viewFile = 'files.php';

ob_start();
require __DIR__ . '/../views/' . $viewFile;
$content = ob_get_clean();

require __DIR__ . '/../views/layout.php';
