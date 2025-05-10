# Daily Report System

## Overview
The Daily Report System is a Laravel-based web application designed to streamline the process of submitting, tracking, and managing daily work reports within an organization. The system supports multiple user roles, departments, and features a comprehensive approval workflow.

## Key Features
- **Daily Report Management**: Create, view, edit, and delete daily reports
- **Approval Workflow**: Multi-level approval process based on user roles
- **User Management**: Admin panel for managing users and departments
- **Notifications**: Real-time notifications for report approvals, rejections, and comments
- **Export/Import**: Export reports to Excel and import data from templates
- **File Attachments**: Support for attaching files to reports with automatic image optimization
- **Comments System**: Threaded comments for discussion on specific reports
- **Organization Chart**: Visual representation of the company structure
- **Dashboard**: Comprehensive dashboard with reports statistics and charts

## User Roles
- **Admin**: Full access to all features and management tools
- **Department Head**: Can approve reports within their department
- **Leader**: Can approve reports from staff members in their department
- **Staff**: Can create and submit reports for approval

## Technical Implementation
- Built with Laravel PHP framework
- Uses Livewire for dynamic frontend components
- Implements Laravel Excel for import/export functionality
- Image processing with Intervention Image
- Uses database transactions for data integrity
- Responsive UI design

## Installation

### Requirements
- PHP 8.1 or higher
- Composer
- MySQL/PostgreSQL
- Node.js and NPM

### Setup
1. Clone the repository
2. Run `composer install`
3. Run `npm install && npm run dev`
4. Copy `.env.example` to `.env` and configure your database settings
5. Run `php artisan key:generate`
6. Run `php artisan migrate --seed`
7. Run `php artisan storage:link`
8. Start the server with `php artisan serve`

## Usage
After installation, you can access the application through your browser. Default admin credentials:
- Email: admin@example.com
- Password: password

## License
This project is licensed under the MIT License.
