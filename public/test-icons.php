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
                echo "<img src='{$path}' width='48' height='48' alt='{$size}'>";
                echo "<div>";
                echo "<strong>{$status} icon-{$size}.png</strong><br>";
                echo "Size: {$fileSize} KB<br>";
                echo "Path: {$fullPath}";
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

        <h2>4. Next Steps</h2>
        <ol>
            <?php if ($successCount < 7): ?>
                <li>Upload missing icon files to <code>public/icons/</code></li>
            <?php endif; ?>
            <?php if (!isset($iconCount) || $iconCount < 7): ?>
                <li>Check manifest file has correct icons array</li>
            <?php endif; ?>
            <li>Test in DevTools: F12 → Application → Manifest</li>
            <li>Delete this test file after verification</li>
        </ol>

        <p><small>Test file: <?php echo __FILE__; ?></small></p>
    </div>
</body>
</html>
