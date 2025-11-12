<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'user_id',
        'role_id',
        'department_id',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'json',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function approvedReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'approved_by');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the unread notifications for the user.
     */
    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->where('is_read', false);
    }

    public function hasRole(string $role): bool
    {
        return $this->role && $this->role->slug === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isDepartmentHead(): bool
    {
        return $this->hasRole('department_head');
    }

    public function isLeader(): bool
    {
        return $this->hasRole('leader');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user can approve reports for the given user
     */
    public function canApprove(User $user): bool
    {
        // Admin can approve anyone
        if ($this->isAdmin()) {
            return true;
        }

        // Department heads can only approve within their department
        if ($this->isDepartmentHead() && $this->department_id === $user->department_id) {
            return $user->isLeader() || $user->isStaff();
        }

        // Leaders can only approve staff within their department
        if ($this->isLeader() && $this->department_id === $user->department_id) {
            return $user->isStaff();
        }

        return false;
    }

    /**
     * Get the profile picture URL
     */
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            // Check if the file actually exists before returning the URL
            if (Storage::disk('public')->exists($this->profile_picture)) {
                return asset('storage/' . $this->profile_picture);
            } else {
                // Log the missing file and clear the profile_picture field
                Log::warning("Profile picture file not found for user {$this->id}: {$this->profile_picture}");
                $this->update(['profile_picture' => null]);
            }
        }
        return null;
    }

    /**
     * Get user's notification preferences
     */
    public function notificationPreferences(): array
    {
        $preferences = $this->getAttribute('notification_preferences');
        
        if (is_string($preferences)) {
            $preferences = json_decode($preferences, true);
        }
        
        return array_merge([
            'job_approved' => true,
            'job_rejected' => true,
            'pending_approval' => true,
            'new_comment' => true,
            'email_notifications' => false,
        ], $preferences ?? []);
    }
    
    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(array $preferences): void
    {
        $current = $this->notificationPreferences();
        $updated = array_merge($current, $preferences);
        
        $this->update(['notification_preferences' => json_encode($updated)]);
    }
    
    /**
     * Check if user wants to receive a specific type of notification
     */
    public function wantsNotification(string $type): bool
    {
        $preferences = $this->notificationPreferences();
        return $preferences[$type] ?? true; // Default to true for new notification types
    }
}
