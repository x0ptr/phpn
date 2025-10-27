<div class="hero">
    <h1>About PHPN</h1>
    <p>The story behind the framework</p>
</div>

<div class="content">
    <h2>Project Overview</h2>
    <p>PHPN (PHP Native) was created to fill a gap in the desktop application development ecosystem. While frameworks like Electron have made it easy to build desktop apps with JavaScript, PHP developers have been left without a similar solution.</p>
    
    <p>The project aims to bring the same ease of development that web developers enjoy to the desktop application world, but with the power and familiarity of PHP.</p>
</div>

<div class="grid">
    <div class="card">
        <div class="image-placeholder">🎯</div>
        <h3>Vision</h3>
        <p>Lets PHP developers build native desktop applications without learning new languages. Uses existing PHP knowledge and libraries.</p>
    </div>
    
    <div class="card">
        <div class="image-placeholder">🛠️</div>
        <h3>Technology</h3>
        <p>Built on PHP 8.4+ with embed SAPI, native Objective-C for macOS, and modern CMake build system. Designed for performance and developer experience.</p>
    </div>
    
    <div class="card">
        <div class="image-placeholder">🌟</div>
        <h3>Community</h3>
        <p>Open source and community-driven. We welcome contributions, feedback, and ideas from developers around the world.</p>
    </div>
</div>

<div class="content" style="margin-top: 40px;">
    <h2>Why PHPN?</h2>
    <p>There are several reasons why you might choose PHPN for your next desktop application project:</p>
    
    <p><strong>Familiar Technology:</strong> If you're already a PHP developer, you can start building desktop apps immediately without learning Electron, Qt, or other desktop frameworks.</p>
    
    <p><strong>Lightweight:</strong> PHPN apps are smaller and use less memory than Electron applications because they use the system's native WebKit instead of bundling Chromium.</p>
    
    <p><strong>Native Integration:</strong> Direct access to native OS features through PHP extensions and the C bridge layer means your apps can do anything a native app can do.</p>
    
    <p><strong>Web Technologies:</strong> Use modern HTML5, CSS3, and JavaScript for your UI. Style with Tailwind, Bootstrap, or any CSS framework. Use Vue, React, or vanilla JS for interactivity.</p>
</div>

<div class="content" style="margin-top: 40px;">
    <h2>System Information</h2>
    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
    <p><strong>Operating System:</strong> <?php echo php_uname('s') . ' ' . php_uname('r'); ?></p>
    <p><strong>Architecture:</strong> <?php echo php_uname('m'); ?></p>
    <p><strong>Server API:</strong> <?php echo php_sapi_name(); ?></p>
</div>
