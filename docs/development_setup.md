# Development Environment Setup Guide

## Initial Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/daily-report.git
   cd daily-report
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js Dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure the Database**
   - Edit `.env` file to set your database credentials
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=daily_report
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

6. **Run Migrations and Seeders**
   ```bash
   php artisan migrate --seed
   ```

7. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

## Development Workflow

### Running the Application

You need to run **both** the Laravel development server and the Vite development server for asset compilation:

1. **Start Laravel Development Server**
   ```bash
   php artisan serve
   ```

2. **Start Vite Development Server (in a separate terminal)**
   ```bash
   npm run dev
   ```

3. **Access the Application**
   - Open your browser and navigate to http://127.0.0.1:8000
   - Default admin credentials:
     - Email: admin@example.com
     - Password: password

### Alternative Asset Handling

If you prefer not to run the Vite development server, you can build the assets and use them directly:

1. **Build Assets**
   ```bash
   npm run build
   ```

2. **Configure Laravel to Use Built Assets**
   - Add this line to your `.env` file:
   ```
   VITE_MANIFEST_PATH="build/manifest.json"
   ```

3. **Run Laravel Server**
   ```bash
   php artisan serve
   ```

### Common Issues and Solutions

1. **Asset Loading Errors**
   - If you see errors about resources not loading from port 5173, make sure the Vite development server is running
   - Or build assets and configure VITE_MANIFEST_PATH as described above

2. **Chart.js Initialization Errors**
   - If you see errors about charts, make sure each chart canvas ID is unique
   - Clear the browser cache if changes don't take effect

3. **Database Connection Issues**
   - Verify your database credentials in the `.env` file
   - Make sure your database server is running

## Testing

1. **Run PHP Tests**
   ```bash
   php artisan test
   ```

2. **Run JavaScript Tests**
   ```bash
   npm run test
   ```

## Useful Commands

- Clear all Laravel caches:
  ```bash
  php artisan optimize:clear
  ```

- Reset database and run migrations again:
  ```bash
  php artisan migrate:fresh --seed
  ```

- Monitor Laravel logs:
  ```bash
  tail -f storage/logs/laravel.log
  ```

- Check for JavaScript errors:
  ```bash
  npm run lint
  ```

- Format JavaScript code:
  ```bash
  npm run format
  ``` 