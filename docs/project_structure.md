# Project Structure Documentation

## Overview
This document describes the cleaned and organized structure of the SiGAP (Sistem Informasi Giat Aktivitas Pekerjaan) project.

## Directory Structure

### Application Core (`app/`)
```
app/
├── Console/
│   ├── Commands/           # Custom Artisan commands
│   └── Kernel.php         # Console kernel configuration
├── Exports/               # Excel export classes
├── Helpers/               # Helper classes and utilities (.gitkeep maintained)
├── Http/
│   ├── Controllers/       # HTTP controllers
│   ├── Middleware/        # Custom middleware
│   └── Requests/          # Form request validation
├── Imports/               # Excel import classes
├── Livewire/              # Livewire components
├── Models/                # Eloquent models
├── Observers/             # Model observers
├── Policies/              # Authorization policies
├── Providers/             # Service providers
├── Services/              # Business logic services (.gitkeep maintained)
└── View/Components/       # Blade components
```

### Resources (`resources/`)
```
resources/
├── css/
│   └── app.css           # Main application styles
├── js/
│   ├── app.js            # Main application JavaScript
│   ├── bootstrap.js      # Bootstrap configuration
│   └── comments.js       # Comment system JavaScript
└── views/                # Blade templates
    ├── admin/           # Admin panel views
    ├── auth/            # Authentication views
    ├── components/      # Reusable Blade components
    ├── daily-reports/   # Daily report views
    ├── dashboard/       # Dashboard views (role-specific)
    ├── layouts/         # Layout templates
    ├── notifications/   # Notification views
    ├── organization/    # Organization chart views
    └── profile/         # User profile views
```

### Public Assets (`public/`)
```
public/
├── css/
│   ├── custom.css        # Custom CSS styles
│   └── dashboard.css     # Dashboard-specific styles
├── img/                 # Static images (.gitkeep maintained)
├── js/
│   ├── app.js           # Compiled application JavaScript
│   ├── dashboard.js     # Dashboard functionality
│   ├── notification.js  # Notification system
│   └── node_modules_chart_js_dist_chart_js.js  # Chart.js bundle
├── webfonts/            # Font Awesome fonts
├── favicon.ico
├── favicon.svg
├── index.php            # Application entry point
├── mix-manifest.json    # Asset manifest
└── robots.txt
```

### Database (`database/`)
```
database/
├── factories/           # Model factories for testing
├── migrations/          # Database schema migrations
└── seeders/            # Database seeders
```

### Configuration (`config/`)
Standard Laravel configuration files for various services and features.

### Documentation (`docs/`)
```
docs/
├── api_documentation.md
├── deployment_checklist.md
├── development_setup.md
├── notification_improvements.md
├── reports_cleanup_system.md
├── technical_documentation.md
├── troubleshooting.md
├── user_guide.md
└── project_structure.md  # This file
```

## Cleaned/Removed Items

### Files Removed:
- `test.txt` - Temporary test file
- `dokumentasi.md` - Duplicate documentation (moved to docs/)
- `webpack.mix.js` - Replaced with Vite configuration
- `public/js/468.js` - Unused JavaScript bundle
- `public/js/468.js.LICENSE.txt` - Unused license file
- `public/js/theme-switcher.js` - Unused theme switcher
- `resources/css/custom.css` - Duplicate CSS file
- `database/migrations/2023_08_05_create_projects_table.php` - Obsolete migration

### Directories Cleaned:
- `resources/views/projects/` - Removed (projects feature deprecated)
- `app/Helpers/` - Cleaned and maintained with .gitkeep
- `app/Services/` - Cleaned and maintained with .gitkeep
- `public/img/` - Cleaned and maintained with .gitkeep

### Dependencies Cleaned:
- Removed Laravel Mix dependencies (`laravel-mix`, `cssnano`, `@vitejs/plugin-vue`)
- Removed unused npm scripts (development, watch, hot, production)
- Fixed security vulnerabilities in npm packages

## Build System

### Vite Configuration
The project now uses Vite as the primary build tool:
- Entry points: `resources/css/app.css`, `resources/js/app.js`
- Output directory: `public/build/`
- HMR enabled for development

### NPM Scripts
- `npm run dev` - Start development server with HMR
- `npm run build` - Build for production

### Development Environment
Use `start-dev.bat` to automatically:
1. Install dependencies if missing
2. Create .env file if missing
3. Start both Laravel and Vite servers
4. Provide clean shutdown when finished

## Asset Management

### CSS Files:
- `resources/css/app.css` - Main application styles (compiled by Vite)
- `public/css/custom.css` - Additional custom styles
- `public/css/dashboard.css` - Dashboard-specific styles

### JavaScript Files:
- `resources/js/app.js` - Main application JavaScript (compiled by Vite)
- `public/js/dashboard.js` - Dashboard functionality
- `public/js/notification.js` - Notification system
- `public/js/node_modules_chart_js_dist_chart_js.js` - Chart.js for dashboard charts

## Maintenance Notes

### .gitkeep Files
Added to maintain essential directory structure:
- `app/Helpers/.gitkeep`
- `app/Services/.gitkeep`
- `public/img/.gitkeep`

### Security
- All npm vulnerabilities resolved
- Unused dependencies removed
- Proper file permissions maintained

### Future Development
- Use `app/Services/` for business logic classes
- Use `app/Helpers/` for utility functions
- Place static images in `public/img/`
- User uploads go to `storage/app/public/`

## Standards Compliance

This structure follows Laravel 11 conventions:
- Vite for asset bundling (not Laravel Mix)
- Standard directory structure
- Proper separation of concerns
- Clean dependency management
- Comprehensive documentation 