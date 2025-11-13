<!DOCTYPE html>
<html>
<head>
    <title>Icon Test - SiGAP</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .icon-test { display: flex; align-items: center; margin: 15px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .icon-test img { margin-right: 15px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        h1 { color: #333; }
        .manifest { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; overflow-x: auto; }
        pre { margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 SiGAP Icon Test</h1>

        <h2>0. Server Configuration</h2>
        <div class="manifest">
            <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?><br>
            <strong>Current Script Path:</strong> <?php echo __DIR__; ?><br>
            <strong>Icons Directory:</strong> <?php echo __DIR__ . '/icons'; ?><br>
            <br>
            <?php
            $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
            $publicPath = realpath(__DIR__);

            if ($docRoot === $publicPath) {
                echo '<span style="color: green;">✓ Document root correctly points to public folder</span>';
            } else {
                echo '<span style="color: red;">⚠ WARNING: Document root mismatch!</span><br>';
                echo '<span style="color: red;">Document root should be: ' . $publicPath . '</span><br>';
                echo '<span style="color: red;">But currently is: ' . $docRoot . '</span><br>';
                echo '<br><strong>Solution:</strong> Update web server config to point to /public folder<br>';
                echo '<strong>For cPanel:</strong> Set document root to: public<br>';
                echo '<strong>For Apache:</strong> DocumentRoot "/path/to/sigap/public"<br>';
                echo '<strong>For Nginx:</strong> root /path/to/sigap/public;';
            }
            ?>
        </div>

        <h2>1. Testing Icon Files</h2>
        <?php
        $icons = [
            '48x48' => '/icons/icon-48x48.png',
            '72x72' => '/icons/icon-72x72.png',
            '96x96' => '/icons/icon-96x96.png',
            '144x144' => '/icons/icon-144x144.png',
            '180x180' => '/icons/icon-180x180.png',
            '192x192' => '/icons/icon-192x192.png',
            '512x512' => '/icons/icon-512x512.png',
        ];

        $successCount = 0;
        foreach ($icons as $size => $path) {
            $fullPath = __DIR__ . $path;
            $exists = file_exists($fullPath);
            $class = $exists ? 'success' : 'error';
            $status = $exists ? '✓' : '✗';

            if ($exists) $successCount++;

            echo "<div class='icon-test {$class}'>";
            if ($exists) {
                $fileSize = round(filesize($fullPath) / 1024, 1);
                $permissions = substr(sprintf('%o', fileperms($fullPath)), -4);
                $isReadable = is_readable($fullPath);

                echo "<img src='{$path}?v=4.0' width='48' height='48' alt='{$size}' onerror='this.style.border=\"2px solid red\"'>";
                echo "<div>";
                echo "<strong>{$status} icon-{$size}.png</strong><br>";
                echo "Size: {$fileSize} KB<br>";
                echo "Permissions: {$permissions} " . ($isReadable ? '✓' : '✗ NOT READABLE') . "<br>";
                echo "Path: {$fullPath}<br>";
                echo "<a href='{$path}?v=4.0' target='_blank'>Test URL</a>";
                echo "</div>";
            } else {
                echo "<div>";
                echo "<strong>{$status} icon-{$size}.png - NOT FOUND</strong><br>";
                echo "Expected path: {$fullPath}";
                echo "</div>";
            }
            echo "</div>";
        }
        ?>

        <h2>2. Manifest Content</h2>
        <?php
        $manifestPath = __DIR__ . '/site.webmanifest';
        if (file_exists($manifestPath)) {
            $manifest = file_get_contents($manifestPath);
            $json = json_decode($manifest, true);
            $iconCount = isset($json['icons']) ? count($json['icons']) : 0;

            echo "<div class='manifest'>";
            echo "<strong>✓ Manifest file exists</strong><br>";
            echo "Icons in manifest: <strong>{$iconCount}</strong><br>";
            echo "File size: " . round(filesize($manifestPath) / 1024, 1) . " KB<br>";
            echo "<br><strong>Manifest content:</strong>";
            echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
            echo "</div>";
        } else {
            echo "<div class='manifest error'>";
            echo "<strong>✗ Manifest file NOT FOUND</strong><br>";
            echo "Expected: {$manifestPath}";
            echo "</div>";
        }
        ?>

        <h2>3. Summary</h2>
        <div class="manifest">
            <strong>Icons Status:</strong> <?php echo $successCount; ?> / 7 files found<br>
            <?php if ($successCount === 7): ?>
                <span style="color: green;">✓ All icons uploaded successfully!</span>
            <?php else: ?>
                <span style="color: red;">✗ Missing icons - need to upload <?php echo (7 - $successCount); ?> files</span>
            <?php endif; ?>
        </div>

        <h2>4. .htaccess Check</h2>
        <?php
        $htaccessPath = __DIR__ . '/.htaccess';
        if (file_exists($htaccessPath)) {
            echo '<div class="manifest success">';
            echo '<strong>✓ .htaccess file exists</strong><br>';
            echo 'Permissions: ' . substr(sprintf('%o', fileperms($htaccessPath)), -4);
            echo '</div>';
        } else {
            echo '<div class="manifest error">';
            echo '<strong>✗ .htaccess file NOT FOUND</strong><br>';
            echo 'Expected: ' . $htaccessPath;
            echo '</div>';
        }
        ?>

        <h2>5. Next Steps</h2>
        <ol>
            <?php
            $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
            $publicPath = realpath(__DIR__);

            if ($docRoot !== $publicPath) {
                echo '<li style="color: red; font-weight: bold;">⚠ CRITICAL: Fix document root configuration first!</li>';
                echo '<li>Update web server to point to /public folder (see section 0 above)</li>';
                echo '<li>Restart web server after changing configuration</li>';
            }
            ?>
            <?php if ($successCount < 7): ?>
                <li>Upload missing icon files to <code>public/icons/</code></li>
                <li>Ensure icon files have 644 permissions: <code>chmod 644 public/icons/*.png</code></li>
            <?php endif; ?>
            <?php if (!isset($iconCount) || $iconCount < 7): ?>
                <li>Check manifest file has correct icons array</li>
            <?php endif; ?>
            <li>Test in DevTools: F12 → Application → Manifest</li>
            <li>Clear browser cache: Ctrl+Shift+R (or Cmd+Shift+R on Mac)</li>
            <li>Delete this test file after verification: <code>rm public/test-icons.php</code></li>
        </ol>

        <h2>6. Quick Fixes for Common Issues</h2>
        <div class="manifest">
            <strong>If icons still show 404 after upload:</strong><br>
            <pre>cd /path/to/sigap.wahanabandhawakencana.co.id
chmod 755 public/icons
chmod 644 public/icons/*.png
php artisan storage:link</pre>
            <br>
            <strong>If using cPanel:</strong><br>
            1. Go to "Setup Python App" or "Domains"<br>
            2. Find your domain settings<br>
            3. Set Document Root to: <code>public</code> (not full path)<br>
            4. Save and restart application
        </div>

        <p><small>Test file: <?php echo __FILE__; ?></small></p>
    </div>
</body>
</html>
