# Daily Report System - API Documentation

Although the Daily Report System primarily operates through web interfaces, it provides several API endpoints for integration with other systems. This document outlines the available endpoints, their parameters, and response formats.

## Authentication

All API endpoints require authentication. The system uses Laravel Sanctum for API authentication.

### Obtaining Authentication Token

```
POST /api/login
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "your-password"
}
```

**Response:**
```json
{
  "token": "your-access-token",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "role_id": 1,
    "department_id": 1
  }
}
```

**Error Response:**
```json
{
  "message": "Invalid credentials"
}
```

## Daily Reports

### Get All Reports

Retrieve all daily reports the authenticated user has access to.

```
GET /api/daily-reports
```

**Query Parameters:**
- `department_id` (optional): Filter by department
- `status` (optional): Filter by status (pending, in_progress, completed)
- `approval_status` (optional): Filter by approval status (pending, approved, rejected)
- `start_date` (optional): Filter by start date (YYYY-MM-DD)
- `end_date` (optional): Filter by end date (YYYY-MM-DD)
- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "job_name": "Project X Implementation",
      "user_id": 1,
      "department_id": 2,
      "report_date": "2023-01-15",
      "due_date": "2023-01-20",
      "description": "Implementation of new features",
      "status": "in_progress",
      "approval_status": "pending",
      "created_at": "2023-01-15T08:30:00Z",
      "updated_at": "2023-01-15T08:30:00Z"
    }
  ],
  "links": {
    "first": "http://example.com/api/daily-reports?page=1",
    "last": "http://example.com/api/daily-reports?page=5",
    "prev": null,
    "next": "http://example.com/api/daily-reports?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "http://example.com/api/daily-reports",
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

### Get Single Report

```
GET /api/daily-reports/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "job_name": "Project X Implementation",
    "user_id": 1,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "department_id": 2,
    "department": {
      "id": 2,
      "name": "Development"
    },
    "report_date": "2023-01-15",
    "due_date": "2023-01-20",
    "description": "Implementation of new features",
    "remark": "On schedule",
    "status": "in_progress",
    "approval_status": "pending",
    "job_pic": 3,
    "pic": {
      "id": 3,
      "name": "Jane Smith"
    },
    "approved_by": null,
    "rejection_reason": null,
    "attachment_path": "attachments/file123.pdf",
    "attachment_original_name": "documentation.pdf",
    "created_at": "2023-01-15T08:30:00Z",
    "updated_at": "2023-01-15T08:30:00Z"
  }
}
```

### Create Report

```
POST /api/daily-reports
```

**Request Body:**
```json
{
  "job_name": "New Feature Development",
  "department_id": 2,
  "report_date": "2023-02-01",
  "due_date": "2023-02-15",
  "description": "Develop new dashboard features",
  "remark": "High priority",
  "status": "pending",
  "job_pic": 3
}
```

**Response:**
```json
{
  "message": "Report created successfully",
  "data": {
    "id": 10,
    "job_name": "New Feature Development",
    "user_id": 1,
    "department_id": 2,
    "report_date": "2023-02-01",
    "due_date": "2023-02-15",
    "description": "Develop new dashboard features",
    "remark": "High priority",
    "status": "pending",
    "approval_status": "pending",
    "job_pic": 3,
    "created_at": "2023-02-01T09:00:00Z",
    "updated_at": "2023-02-01T09:00:00Z"
  }
}
```

### Update Report

```
PUT /api/daily-reports/{id}
```

**Request Body:**
```json
{
  "job_name": "Updated Feature Development",
  "status": "in_progress",
  "description": "Updated description"
}
```

**Response:**
```json
{
  "message": "Report updated successfully",
  "data": {
    "id": 10,
    "job_name": "Updated Feature Development",
    "status": "in_progress",
    "description": "Updated description",
    "updated_at": "2023-02-02T10:15:00Z"
  }
}
```

### Delete Report

```
DELETE /api/daily-reports/{id}
```

**Response:**
```json
{
  "message": "Report deleted successfully"
}
```

## Comments

### Get Comments for a Report

```
GET /api/daily-reports/{reportId}/comments
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 2,
      "user": {
        "id": 2,
        "name": "Sarah Johnson"
      },
      "daily_report_id": 10,
      "comment": "Please provide more details",
      "visibility": "public",
      "created_at": "2023-02-02T14:30:00Z",
      "updated_at": "2023-02-02T14:30:00Z"
    }
  ]
}
```

### Add Comment to Report

```
POST /api/daily-reports/{reportId}/comments
```

**Request Body:**
```json
{
  "comment": "I'll update the documentation tomorrow",
  "visibility": "public"
}
```

**Response:**
```json
{
  "message": "Comment added successfully",
  "data": {
    "id": 2,
    "user_id": 1,
    "daily_report_id": 10,
    "comment": "I'll update the documentation tomorrow",
    "visibility": "public",
    "created_at": "2023-02-03T09:45:00Z",
    "updated_at": "2023-02-03T09:45:00Z"
  }
}
```

### Delete Comment

```
DELETE /api/comments/{commentId}
```

**Response:**
```json
{
  "message": "Comment deleted successfully"
}
```

## Notifications

### Get User Notifications

```
GET /api/notifications
```

**Query Parameters:**
- `is_read` (optional): Filter by read status (true/false)
- `page` (optional): Page number for pagination

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "title": "Report Approved",
      "message": "Your report 'Project X Implementation' has been approved",
      "link": "/daily-reports/1",
      "is_read": false,
      "created_at": "2023-02-03T10:15:00Z",
      "updated_at": "2023-02-03T10:15:00Z"
    }
  ],
  "meta": {
    "unread_count": 3,
    "total": 15
  }
}
```

### Mark Notification as Read

```
POST /api/notifications/mark-as-read
```

**Request Body:**
```json
{
  "notification_id": 1
}
```

**Response:**
```json
{
  "message": "Notification marked as read",
  "data": {
    "id": 1,
    "is_read": true,
    "updated_at": "2023-02-03T11:20:00Z"
  }
}
```

### Mark All Notifications as Read

```
POST /api/notifications/mark-all-as-read
```

**Response:**
```json
{
  "message": "All notifications marked as read",
  "count": 3
}
```

## Error Handling

All API endpoints follow a consistent error response format:

```json
{
  "message": "Error message",
  "errors": {
    "field1": [
      "Validation error message 1",
      "Validation error message 2"
    ],
    "field2": [
      "Validation error message"
    ]
  }
}
```

### Common HTTP Status Codes

- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error 