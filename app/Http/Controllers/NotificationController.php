<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Get the user's notifications
     */
    public function getNotifications(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Optimized query with better eager loading to prevent N+1 queries
        $notifications = $user->notifications()
            ->with([
                'dailyReport:id,job_name,user_id',
                'comment:id,daily_report_id,user_id,comment',
                'comment.user:id,name'
            ])
            ->select(['id', 'user_id', 'daily_report_id', 'comment_id', 'type', 'message', 'is_read', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        // Use more efficient count query
        $unreadCount = $user->notifications()
            ->where('is_read', false)
            ->count();
            
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $notificationId = $request->input('notification_id');
        $notification = Notification::findOrFail($notificationId);
        
        // Check if notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $notification->update(['is_read' => true]);
        
        // Get updated unread count
        /** @var User $user */
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();
        
        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->notifications()->update(['is_read' => true]);
        
        return response()->json([
            'success' => true,
            'unread_count' => 0
        ]);
    }
    
    /**
     * Clear all notifications (delete them permanently)
     */
    public function clearAll(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->notifications()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications have been cleared'
        ]);
    }
    
    /**
     * View all notifications in a dedicated page
     */
    public function viewAll(): View
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get all notifications with pagination (25 per page) and optimized eager loading
        $notifications = $user->notifications()
            ->with([
                'dailyReport:id,job_name,user_id',
                'comment:id,daily_report_id,user_id,comment',
                'comment.user:id,name'
            ])
            ->select(['id', 'user_id', 'daily_report_id', 'comment_id', 'type', 'message', 'is_read', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);
            
        // Mark all as read when viewing this page (optimized bulk update)
        if (request()->has('mark_read') && request('mark_read') == 1) {
            $user->notifications()
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }
        
        // Get unread count for UI
        $unreadCount = $user->notifications()
            ->where('is_read', false)
            ->count();
        
        return view('notifications.all', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
    
    /**
     * Get user's notification preferences
     */
    public function getPreferences(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        return response()->json([
            'preferences' => $user->notificationPreferences()
        ]);
    }
    
    /**
     * Update user's notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'job_approved' => 'boolean',
            'job_rejected' => 'boolean',
            'pending_approval' => 'boolean',
            'new_comment' => 'boolean',
            'email_notifications' => 'boolean',
        ]);
        
        /** @var User $user */
        $user = Auth::user();
        
        $user->updateNotificationPreferences($request->only([
            'job_approved',
            'job_rejected', 
            'pending_approval',
            'new_comment',
            'email_notifications'
        ]));
        
        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
            'preferences' => $user->notificationPreferences()
        ]);
    }
}
