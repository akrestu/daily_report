<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MoveAttachmentsToPrivate extends Command
{
    protected $signature = 'attachments:move-private {--dry-run : List files without moving them}';

    protected $description = 'Move report/ticket attachments from the public disk to the private attachments disk';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('attachments');
        $files = $public->allFiles('attachments');

        if (empty($files)) {
            $this->info('No attachments found on the public disk.');
            return self::SUCCESS;
        }

        $moved = 0;
        $failed = 0;

        foreach ($files as $path) {
            if ($this->option('dry-run')) {
                $this->line($path);
                continue;
            }

            $stream = $public->readStream($path);
            $written = $stream && $private->writeStream($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            if ($written && $private->exists($path) && $private->size($path) === $public->size($path)) {
                $public->delete($path);
                $moved++;
            } else {
                $this->error("Failed to move: {$path}");
                $failed++;
            }
        }

        if ($this->option('dry-run')) {
            $this->info(count($files) . ' file(s) would be moved.');
            return self::SUCCESS;
        }

        $this->info("Moved {$moved} file(s), {$failed} failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
