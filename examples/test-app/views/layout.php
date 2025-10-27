<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'PHPN Laravel App'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            color: white;
            font-size: 1.5em;
            font-weight: bold;
            padding: 20px 0;
            margin-right: 40px;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 0;
        }
        
        .nav-links a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 20px 20px;
            display: block;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(255,255,255,0.1);
            border-bottom-color: white;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .hero {
            background: white;
            padding: 60px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            text-align: center;
        }
        
        .hero h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        
        .hero p {
            color: #666;
            font-size: 1.2em;
            line-height: 1.6;
        }
        
        .content {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .content h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        .content p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }
        
        .card h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        
        .card p {
            color: #666;
            line-height: 1.6;
        }
        
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #c7254e;
            font-size: 0.9em;
        }
        
        .image-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3em;
            margin-bottom: 20px;
        }
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 30px 20px;
            margin-top: 60px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <div class="logo">PHPN</div>
            <ul class="nav-links">
                <li><a href="/index.php" class="<?php echo $page === 'home' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="/features.php" class="<?php echo $page === 'features' ? 'active' : ''; ?>">Features</a></li>
                <li><a href="/files.php" class="<?php echo $page === 'files' ? 'active' : ''; ?>">Files</a></li>
                <li><a href="/about.php" class="<?php echo $page === 'about' ? 'active' : ''; ?>">About</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <?php echo $content; ?>
    </div>
    
    <footer>
        <p>&copy; 2025 PHPN - Build Native Apps with PHP | Running on PHP <?php echo phpversion(); ?></p>
    </footer>
</body>
</html>
