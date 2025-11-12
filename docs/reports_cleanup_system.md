# SiGAP Reports Cleanup System

## Overview
The SiGAP Reports Cleanup System provides automated and manual management of old reports to maintain optimal database performance and storage efficiency.

## Features

### 1. Command Line Interface
```bash
php artisan reports:cleanup [options]
```

**Options:**
- `--days=365` - Number of days to keep reports (default: 365)
- `--status=STATUS` - Only cleanup reports with specific status
- `--dry-run` - Preview what would be deleted
- `--keep-attachments` - Keep attachment files when deleting

### 2. Admin Web Interface
- Access via `/admin/reports/cleanup`
- Visual statistics and configuration
- Preview mode before execution
- Real-time cleanup execution

### 3. Automatic Scheduling
```php
// Monthly cleanup of completed/approved reports older than 2 years
$schedule->command('reports:cleanup --status=completed --status=approved --days=730')
         ->monthlyOn(1, '03:00');
```

## Usage Examples

### Preview Cleanup
```bash
# Preview reports older than 1 year
php artisan reports:cleanup --dry-run --days=365

# Preview only completed reports
php artisan reports:cleanup --dry-run --status=completed --status=approved
```

### Execute Cleanup
```bash
# Delete reports older than 1 year
php artisan reports:cleanup --days=365

# Delete specific status reports
php artisan reports:cleanup --status=completed --days=730
```

## Safety Features
- Dry-run mode for preview
- Multiple confirmation prompts in production
- Chunked processing to prevent memory issues
- Proper cascading deletion of related data
- Optional attachment file retention

## Web Interface Features
- Statistical overview dashboard
- Interactive cleanup configuration
- Preview results before execution
- Real-time progress feedback
- Storage usage monitoring 