<div class="hero">
    <h1>File Browser</h1>
    <p>Browse your file system using PHP</p>
</div>

<div class="content">
    <?php
    $currentDir = $_GET['dir'] ?? getcwd();
    
    $currentDir = realpath($currentDir);
    if ($currentDir === false) {
        $currentDir = getcwd();
    }
    
    echo "<h2>Current Directory</h2>";
    echo "<p style='background: #f0f0f0; padding: 15px; border-radius: 8px; font-family: monospace;'>" . 
         htmlspecialchars($currentDir) . "</p>";
    
    $parentDir = dirname($currentDir);
    
    echo "<div style='margin: 20px 0;'>";
    if ($currentDir !== '/') {
        echo "<a href='/files.php?dir=" . urlencode($parentDir) . "' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; margin-right: 10px;'>Parent Directory</a>";
    }
    echo "<a href='/files.php?dir=" . urlencode(getcwd()) . "' style='display: inline-block; padding: 10px 20px; background: #764ba2; color: white; text-decoration: none; border-radius: 6px;'>Project Root</a>";
    echo "</div>";
    
    try {
        $items = scandir($currentDir);
        
        echo "<h2>Contents</h2>";
        echo "<div style='background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
        
        $dirs = [];
        $files = [];
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $currentDir . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($fullPath)) {
                $dirs[] = $item;
            } else {
                $files[] = $item;
            }
        }
        
        sort($dirs);
        sort($files);
        
        foreach ($dirs as $dir) {
            $fullPath = $currentDir . DIRECTORY_SEPARATOR . $dir;
            $encoded = urlencode($fullPath);
            
            echo "<div style='padding: 15px; border-bottom: 1px solid #eee; display: flex; align-items: center; transition: background 0.2s;' onmouseover='this.style.background=\"#f9f9f9\"' onmouseout='this.style.background=\"white\"'>";
            echo "<span style='font-size: 24px; margin-right: 15px;'>📁</span>";
            echo "<div style='flex: 1;'>";
            echo "<a href='/files.php?dir=" . $encoded . "' style='color: #667eea; text-decoration: none; font-weight: 500; font-size: 16px;'>" . htmlspecialchars($dir) . "</a>";
            echo "<div style='color: #999; font-size: 13px; margin-top: 3px;'>Directory</div>";
            echo "</div>";
            echo "</div>";
        }
        
        foreach ($files as $file) {
            $fullPath = $currentDir . DIRECTORY_SEPARATOR . $file;
            $size = filesize($fullPath);
            $modified = date("Y-m-d H:i:s", filemtime($fullPath));
            
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $icon = match($extension) {
                'php' => '🐘',
                'txt', 'md' => '📄',
                'jpg', 'jpeg', 'png', 'gif', 'svg' => '🖼️',
                'pdf' => '📕',
                'zip', 'tar', 'gz' => '📦',
                'js' => '📜',
                'css' => '🎨',
                'html', 'htm' => '🌐',
                'json', 'xml' => '📋',
                default => '📄'
            };
            
            $sizeFormatted = $size < 1024 ? $size . ' B' :
                           ($size < 1024 * 1024 ? round($size / 1024, 2) . ' KB' :
                           round($size / (1024 * 1024), 2) . ' MB');
            
            echo "<div style='padding: 15px; border-bottom: 1px solid #eee; display: flex; align-items: center; transition: background 0.2s;' onmouseover='this.style.background=\"#f9f9f9\"' onmouseout='this.style.background=\"white\"'>";
            echo "<span style='font-size: 24px; margin-right: 15px;'>$icon</span>";
            echo "<div style='flex: 1;'>";
            echo "<div style='color: #333; font-weight: 500; font-size: 16px;'>" . htmlspecialchars($file) . "</div>";
            echo "<div style='color: #999; font-size: 13px; margin-top: 3px;'>$sizeFormatted • Modified: $modified</div>";
            echo "</div>";
            echo "</div>";
        }
        
        echo "</div>";
        
        $totalItems = count($dirs) + count($files);
        echo "<div style='margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; color: #666;'>";
        echo "<strong>Statistics:</strong> ";
        echo count($dirs) . " directories, " . count($files) . " files";
        echo " (Total: $totalItems items)";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='padding: 20px; background: #fee; color: #c33; border-radius: 8px;'>";
        echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    ?>
</div>

<div class="content" style="margin-top: 40px;">
    <h2>PHP File System Capabilities</h2>
    <div class="grid">
        <div class="card">
            <h3>Read Directories</h3>
            <p>Use <code>scandir()</code>, <code>opendir()</code>, and <code>readdir()</code> to list directory contents and navigate the file system.</p>
        </div>
        
        <div class="card">
            <h3>File Information</h3>
            <p>Get file sizes with <code>filesize()</code>, modification times with <code>filemtime()</code>, and check permissions with <code>is_readable()</code>.</p>
        </div>
        
        <div class="card">
            <h3>Read/Write Files</h3>
            <p>Use <code>file_get_contents()</code>, <code>file_put_contents()</code>, <code>fopen()</code>, and related functions for file I/O operations.</p>
        </div>
    </div>
</div>
