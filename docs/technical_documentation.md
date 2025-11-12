# SiGAP - Technical Documentation

## System Architecture

SiGAP (Sistem Informasi Giat Aktivitas Pekerjaan) is a Laravel-based web application with the following architecture:

### Core Components
1. **Laravel Framework**: The application is built on Laravel, utilizing its MVC architecture, routing, middleware, and other core features.
2. **Database**: MySQL/PostgreSQL database with Eloquent ORM.
3. **Frontend**: Blade templates with Livewire components for dynamic interactions.
4. **Authentication**: Laravel's built-in authentication system with custom middleware for role-based access control.
5. **File Storage**: Laravel Storage facade for file management with public and private disks.

## Database Structure

### Main Tables
- **users**: Stores user information and authentication data
- **roles**: Defines system roles (Admin, Department Head, Leader, Staff)
- **departments**: Organizational departments
- **daily_reports**: The core entity that stores all report data
- **job_comments**: Comments on reports
- **notifications**: System notifications for users

### Key Relationships
- User belongs to a Role and Department
- DailyReport belongs to a User (creator) and Department
- DailyReport has many JobComments
- User has many Notifications
- User can be assigned as PIC (Person In Charge) for DailyReports

## Feature Implementation Details

### Daily Report Management
- **Creation**: Users can create individual reports or use batch import
- **Approval Workflow**: Multi-step approval based on organizational hierarchy
- **Filtering and Sorting**: Advanced filtering by date, status, department, etc.
- **File Attachments**: Support for multiple file uploads (up to 3 attachments) with automatic image optimization
- **Report Details View**: Tabbed interface with Description, Remarks, and Comments sections
  - Tab order: Desc → Remarks → Comments (optimized for logical reading flow)
  - Comments section includes real-time loading and inline posting
  - Rejection reasons displayed in dedicated tab when applicable

### User Management
- **Role-Based Access Control**: Different permissions for Admin, Department Head, Leader, Staff
- **Department Organization**: Users are organized by departments
- **Profile Management**: Users can update their profile information and picture
- **Hierarchical Levels**: Users are organized in 5 levels (Level 1-5) plus Admin
  - Level 5: Highest approval authority
  - Level 4-2: Middle management
  - Level 1: Entry level staff

### Organization Chart
- **Hierarchical Visualization**: Visual representation of department structure
- **Level-Based Display**: Shows organizational hierarchy from Level 5 down to Level 1
- **Role Legend**: Displays color-coded badges for each organizational level
  - Level 5 (Blue): Highest Approval Authority
  - Level 4 (Cyan): Mid-High Management
  - Level 3 (Yellow): Mid Management
  - Level 2 (Green): Team Leaders
  - Level 1 (Gray): Entry Level Staff
- **Note**: Administrator role exists for system management but is not displayed in organization hierarchy to maintain focus on operational structure

### Notification System
- **Real-time Notifications**: Notifications for report approvals, rejections, and comments
- **Read/Unread Status**: Users can mark notifications as read

### Import/Export System
- **Excel Integration**: Using Laravel Excel package for import/export functionality
- **Custom Templates**: Export template for batch imports

## Security Considerations

1. **Authentication**: Laravel Sanctum for secure authentication
2. **Authorization**: Role-based access control through middleware and policies
3. **File Validation**: Strict validation for file uploads with size and type restrictions
4. **CSRF Protection**: Laravel's built-in CSRF protection
5. **Input Validation**: Request validation for all form submissions

## UI/UX Design

### Branding
- **Application Logo**: Custom SiGAP logo (Sigap.png) displayed in:
  - Login screen (circular container at top)
  - Sidebar navigation header
  - Both locations use responsive sizing for optimal display
- **Color Scheme**: Gradient theme with primary colors blue (#0d6efd) and purple (#6610f2)
- **Icon System**: FontAwesome icons for consistent visual language

### Responsive Design
- **Mobile-First Approach**: Optimized for mobile, tablet, and desktop views
- **Adaptive Navigation**: Sidebar converts to mobile drawer on smaller screens
- **Touch-Optimized**: Enhanced touch targets for mobile users
- **Tab Interface**: Responsive tab system that adjusts text and spacing based on screen size

## Performance Optimizations

1. **Image Processing**: Using Intervention Image for image optimization
2. **Query Optimization**: Eager loading relationships to prevent N+1 query problems
3. **Pagination**: Implementation of pagination for large data sets
4. **Asset Optimization**: Vite for fast frontend asset compilation and hot module replacement

## API Documentation

The system primarily operates through web interfaces, but some API endpoints are available:

- `GET /notifications` - Get user notifications
- `POST /notifications/mark-as-read` - Mark notification as read
- `GET /daily-reports/{reportId}/comments` - Get comments for a report
- `POST /daily-reports/{reportId}/comments` - Add a comment to a report

## Deployment Guidelines

1. **Server Requirements**:
   - PHP 8.1 or higher
   - MySQL 5.7+ or PostgreSQL 9.6+
   - Composer
   - Node.js and NPM for asset compilation
   - Web server (Apache/Nginx)

2. **Installation Steps**:
   - Clone the repository
   - Install PHP dependencies with Composer
   - Install JavaScript dependencies with NPM
   - Set up environment variables
   - Generate application key
   - Run database migrations and seeders
   - Set up storage symlinks
   - Compile frontend assets

3. **Environment Configuration**:
   - Database connection
   - Mail server settings
   - File storage configuration
   - Application URL and environment

## Maintenance

### Backup Procedures
- Regular database backups
- File storage backups
- Environment configuration backups

### Update Procedures
1. Pull latest code changes
2. Install dependencies
3. Run migrations
4. Clear caches
5. Recompile assets if needed

## Troubleshooting

### Common Issues
1. **File Upload Issues**: Check storage permissions and disk configuration
2. **Email Notification Problems**: Verify mail configuration
3. **Import/Export Errors**: Check PHP memory limits and Excel package configuration

### Logging
The application uses Laravel's built-in logging system with daily log rotation. 