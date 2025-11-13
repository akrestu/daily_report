<?php
/**
 * Emergency Cache Clear Script
 * Place this in public/ and access via browser
 */

echo "<h1>🧹 SiGAP Cache Cleaner</h1>";
echo "<pre>";

// Check if Laravel is available
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';

    try {
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

        echo "Clearing Laravel caches...\n\n";

        echo "1. Cache Clear: ";
        $kernel->call('cache:clear');
        echo "✓ Done\n";

        echo "2. Config Clear: ";
        $kernel->call('config:clear');
        echo "✓ Done\n";

        echo "3. Route Clear: ";
        $kernel->call('route:clear');
        echo "✓ Done\n";

        echo "4. View Clear: ";
        $kernel->call('view:clear');
        echo "✓ Done\n";

        echo "\n✅ All Laravel caches cleared!\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Laravel not found\n";
}

// Clear OPcache if available
if (function_exists('opcache_reset')) {
    echo "\n5. OPcache Reset: ";
    if (opcache_reset()) {
        echo "✓ Done\n";
    } else {
        echo "⚠ Failed (may need server restart)\n";
    }
} else {
    echo "\n5. OPcache: Not available\n";
}

// Show file info
echo "\n=== File Verification ===\n";
$files = [
    'site.webmanifest',
    'manifest.json',
    'web/site.webmanifest'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        $modified = date('Y-m-d H:i:s', filemtime($path));
        echo "✓ {$file}: {$size} bytes, modified {$modified}\n";
    } else {
        echo "❌ {$file}: NOT FOUND\n";
    }
}

echo "\n=== Next Steps ===\n";
echo "1. Test manifest URL again:\n";
echo "   https://sigap.wahanabandhawakencana.co.id/web/site.webmanifest\n";
echo "2. If still cached, wait 2-3 minutes and try again\n";
echo "3. Or use browser incognito mode to bypass browser cache\n";
echo "4. Delete this file after use for security\n";

echo "</pre>";
