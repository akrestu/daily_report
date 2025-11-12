# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SiGAP (Sistem Informasi Giat Aktivitas Pekerjaan) is a Laravel 12-based daily work activity reporting system with multi-level approval workflows. The system handles report creation, approval routing, notifications, comments, and file attachments with role-based access control.

### Current Version: 1.1.0 (November 2024)

#### Recent Updates
- **Custom Logo**: Implemented SiGAP logo (public/Sigap.png) in login and sidebar
- **Organization Chart**: Removed Administrator from Role Legend to focus on operational hierarchy (Level 1-5)
- **Report Details UI**: Reordered tabs from "Desc, Comments, Remarks" to "Desc, Remarks, Comments" for better UX
- **Multiple Attachments**: Support for up to 3 file attachments per report

## Development Commands

### Setup and Installation
```bash
# Initial setup (uses start-dev.bat on Windows)
.\start-dev.bat

# Manual setup
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### Running Development Servers
```bash
# Start all servers concurrently (Laravel, Queue, Vite)
composer run dev

# Individual servers
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

### Testing
```bash
# Run all tests
composer run test

# Run specific test
php artisan test --filter=TestName
```

### Production Optimization
```bash
# Cache everything for production
composer run optimize

# Clear all caches
composer run optimize:clear
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh install with seeding
php artisan migrate:fresh --seed

# Rollback one batch
php artisan migrate:rollback
```

### Maintenance Commands
```bash
# Cleanup old notifications
php artisan notifications:cleanup

# Cleanup old reports
php artisan reports:cleanup

# Cleanup orphaned profile pictures
php artisan cleanup:orphaned-profile-pictures

# List all users
php artisan list:users
```

### Code Quality
```bash
# Format code (Laravel Pint)
./vendor/bin/pint

# View logs in real-time
php artisan pail
```

## Architecture

### Role-Based System
The system has 4 user roles with distinct permissions:
- **Admin**: Full system access, user management, department management, system settings
- **Department Head**: Department-level approval authority, can approve reports from their department
- **Leader**: Team-level approval authority, can approve reports from staff in their department
- **Staff**: Can create and submit reports, view assigned tasks

Role hierarchy flows: Staff → Leader → Department Head → Admin

### Approval Workflow
Daily reports follow a hierarchical approval workflow:
1. Staff creates report (status: `pending`)
2. Leader reviews and approves/rejects (approval_status: `approved_by_leader` or `rejected`)
3. Department Head reviews and approves/rejects (approval_status: `approved_by_department_head` or `rejected`)
4. Final status becomes `completed` when fully approved

Rejection at any level sends the report back with a rejection reason.

### Model Relationships
Core models and their relationships:
- **User**: `belongsTo(Role, Department)`, `hasMany(DailyReport, Notification, JobComment)`
- **DailyReport**: `belongsTo(User, Department, User as approver)`, `hasMany(JobComment)`
- **JobComment**: `belongsTo(User, DailyReport)`
- **Notification**: `belongsTo(User)`, uses `notifiable_type` and `notifiable_id` for polymorphic relation

### Observers Pattern
The system uses Laravel Observers for cross-cutting concerns:
- **DailyReportObserver**: Handles notification creation on report status changes (approval, rejection)
- **JobCommentObserver**: Handles notification creation when comments are added to reports

Register observers in `AppServiceProvider` or `bootstrap/app.php`.

### Livewire Components
Two main Livewire components for real-time updates:
- **DailyReportForm**: Multi-page form for creating daily reports with dynamic field validation
- **DailyReportList**: Real-time list with filtering, sorting, pagination, and batch operations

### Notification System
Notifications are created automatically via observers and include:
- Report approval/rejection notifications
- Comment notifications
- User preferences stored in `notification_preferences` JSON field
- Real-time count updates via AJAX polling

### File Upload System
- **Attachments**: Stored in `storage/app/public/attachments/`
- **Profile Pictures**: Stored in `storage/app/public/profile_pictures/`
- **Image Optimization**: Uses Intervention Image v3 to resize/optimize images on upload
- **Security**: File access is protected via route middleware checking user permissions (routes/web.php:143-177)

### Excel Import/Export
Uses Laravel Excel (maatwebsite/excel) for:
- Export all reports to Excel
- Export filtered reports
- Import reports from template
- Export user lists
- Import users from template

### UI/UX Design
**Branding**:
- Custom logo: `public/Sigap.png` (bar chart and document graphic)
- Logo displayed in: login screen (circular container) and sidebar navigation
- Color scheme: Gradient theme with blue (#0d6efd) and purple (#6610f2)

**Report Details Interface**:
- Tabbed layout: Desc → Remarks → Comments (optimized order for natural reading flow)
- Real-time comment loading with AJAX
- Support for 3 file attachments with preview and download

**Organization Chart**:
- Hierarchical visualization of department structure (Level 5 to Level 1)
- Color-coded role badges (Blue, Cyan, Yellow, Green, Gray)
- Admin role excluded from legend (system management, not operational hierarchy)
- Mobile-responsive with touch-optimized interactions

## Important Configuration

### Timezone & Localization
The system is configured for Indonesia (WIB/UTC+7):
- Application timezone: `Asia/Jakarta` (config/app.php)
- Database timezone: `+07:00` (config/database.php)
- Carbon locale: `id` (Indonesian) set in AppServiceProvider
- All timestamps display in WIB

### Laravel 12 Compliance
This project uses Laravel 12 features:
- **Model casts**: Use `protected function casts(): array` instead of `protected $casts = []`
- **Query monitoring**: Slow queries (>500ms) are logged automatically in local environment
- **Concurrent development**: `composer run dev` runs Laravel, Queue, and Vite together

### Queue Configuration
- Driver: `database-uuids` for better performance
- Queue worker runs via `composer run dev`
- Jobs retry once (`--tries=1`)

## Key Code Patterns

### Role Checking
Users have convenience methods for role checking:
```php
$user->isAdmin()
$user->isDepartmentHead()
$user->isLeader()
$user->isStaff()
```

### Authorization
Use policies for authorization checks:
- `DailyReportPolicy`: Controls view, update, delete, approve actions on reports
- Check via `$this->authorize('update', $dailyReport)` in controllers

### Database Transactions
Critical operations use database transactions for data integrity:
```php
DB::transaction(function () {
    // Multiple related operations
});
```

### Eager Loading
Prevent N+1 queries by eager loading relationships:
```php
DailyReport::with(['user', 'department', 'approver', 'comments.user'])->get()
```

### Pagination
- Use `paginate()` for UI pagination with page links
- Use `simplePaginate()` for better performance when page numbers aren't needed

## Common Pitfalls

1. **File Paths**: Always use `Storage` facade for file operations, never direct path manipulation
2. **Timezone**: Don't manually convert timezones - Carbon is pre-configured for Asia/Jakarta
3. **Casts**: Use `casts()` method (Laravel 12), not `$casts` property
4. **Mass Assignment**: Always add new fillable fields to model's `$fillable` array
5. **Authorization**: Check permissions via policies before displaying UI elements or performing actions
6. **Queue Workers**: Queue must be running for notifications and async jobs to work

## Testing Environment

Default seeded credentials:
- Admin: admin@example.com / password
- Test data is seeded via `database/seeders/DatabaseSeeder.php`

## File Structure Notes

### Services Pattern
- Business logic should go in `app/Services/` (currently maintained with .gitkeep)
- Controllers should remain thin, delegating to services for complex operations

### Helpers
- Utility functions go in `app/Helpers/` (currently maintained with .gitkeep)
- Can be autoloaded via composer.json if needed

### Frontend Assets
- Source: `resources/js/app.js`, `resources/css/app.css`
- Build: Vite compiles to `public/build/`
- Additional static assets: `public/js/`, `public/css/`
- Uses Bootstrap 5 + Tailwind CSS (hybrid approach)
- Alpine.js for lightweight interactivity
- Chart.js for dashboard visualizations

## Documentation

Comprehensive documentation exists in `docs/`:
- `technical_documentation.md`: System architecture and features
- `project_structure.md`: Directory organization and cleaned items
- `laravel_12_compliance.md`: Laravel 12 features and compliance
- `api_documentation.md`: API endpoints reference
- `deployment_checklist.md`: Production deployment steps
- `user_guide.md`: End-user documentation
- `troubleshooting.md`: Common issues and solutions
- `notification_improvements.md`: Notification system details
- `reports_cleanup_system.md`: Report cleanup features
- `timezone_configuration.md`: Timezone handling details

## Security Considerations

- CSRF protection enabled globally
- File upload validation: type, size, MIME checking
- Path traversal protection in file access routes
- XSS prevention via Blade `{{ }}` escaping
- SQL injection prevention via Eloquent/Query Builder
- Role-based access control via middleware and policies
