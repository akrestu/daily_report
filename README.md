<h1 align="center">Hi 👋, I'm Restu</h1>
<h3 align="center">A passionate laravel developer from Surabaya</h3>

- 📫 How to reach me **adrosrestuk@gmail.com**

<h3 align="left">Connect with me:</h3>
<p align="left">
</p>

<h3 align="left">Languages and Tools:</h3>
<p align="left"> <a href="https://getbootstrap.com" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/bootstrap/bootstrap-plain-wordmark.svg" alt="bootstrap" width="40" height="40"/> </a> <a href="https://flutter.dev" target="_blank" rel="noreferrer"> <img src="https://www.vectorlogo.zone/logos/flutterio/flutterio-icon.svg" alt="flutter" width="40" height="40"/> </a> <a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/javascript/javascript-original.svg" alt="javascript" width="40" height="40"/> </a> <a href="https://laravel.com/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/laravel/laravel-plain-wordmark.svg" alt="laravel" width="40" height="40"/> </a> <a href="https://www.mysql.com/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original-wordmark.svg" alt="mysql" width="40" height="40"/> </a> <a href="https://www.php.net" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" alt="php" width="40" height="40"/> </a> <a href="https://reactjs.org/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/react/react-original-wordmark.svg" alt="react" width="40" height="40"/> </a> <a href="https://vuejs.org/" target="_blank" rel="noreferrer"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/vuejs/vuejs-original-wordmark.svg" alt="vuejs" width="40" height="40"/> </a> </p>

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
