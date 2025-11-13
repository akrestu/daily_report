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

    public function isLevel1(): bool
    {
        return $this->hasRole('level1');
    }

    public function isLevel2(): bool
    {
        return $this->hasRole('level2');
    }

    public function isLevel3(): bool
    {
        return $this->hasRole('level3');
    }

    public function isLevel4(): bool
    {
        return $this->hasRole('level4');
    }

    public function isLevel5(): bool
    {
        return $this->hasRole('level5');
    }

    // Legacy methods for backward compatibility
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
     * Get the role level number (1-5) or 0 for non-level roles
     */
    public function getRoleLevel(): int
    {
        if ($this->isLevel1()) return 1;
        if ($this->isLevel2()) return 2;
        if ($this->isLevel3()) return 3;
        if ($this->isLevel4()) return 4;
        if ($this->isLevel5()) return 5;
        return 0;
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

        $approverLevel = $this->getRoleLevel();
        $userLevel = $user->getRoleLevel();

        // Non-level roles cannot approve
        if ($approverLevel === 0) {
            return false;
        }

        // Level 5 can approve reports from Level 1-4
        if ($approverLevel === 5 && $userLevel >= 1 && $userLevel <= 4) {
            return true;
        }

        // Level 2-4 can approve reports from one level below
        if ($approverLevel >= 2 && $approverLevel <= 4) {
            return $userLevel === ($approverLevel - 1);
        }

        return false;
    }

    /**
     * Get eligible PIC roles based on current user's role
     * Returns array of role slugs that can be selected as PIC
     */
    public function getEligiblePicRoles(): array
    {
        // Admin cannot be selected as PIC
        // Level 1: can only select Level 2
        if ($this->isLevel1()) {
            return ['level2'];
        }

        // Level 2: can only select Level 3
        if ($this->isLevel2()) {
            return ['level3'];
        }

        // Level 3: can only select Level 4
        if ($this->isLevel3()) {
            return ['level4'];
        }

        // Level 4: can only select Level 5
        if ($this->isLevel4()) {
            return ['level5'];
        }

        // Level 5: cannot select Admin, but can select Level 2-5 for flexibility
        if ($this->isLevel5()) {
            return ['level2', 'level3', 'level4', 'level5'];
        }

        // Admin can select any level except themselves
        if ($this->isAdmin()) {
            return ['level2', 'level3', 'level4', 'level5'];
        }

        // Default: no eligible roles
        return [];
    }

    /**
     * Check if a user can be selected as PIC
     */
    public function canBePic(): bool
    {
        // Level 1 and Admin cannot be PIC
        return !$this->isLevel1() && !$this->isAdmin();
    }

    /**
     * Get the profile picture URL
     * FIXED: Returns default avatar instead of null, doesn't silently modify database
     */
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            // Check if the file actually exists before returning the URL
            if (Storage::disk('public')->exists($this->profile_picture)) {
                return asset('storage/' . $this->profile_picture);
            } else {
                // Log the missing file but don't modify database in accessor
                Log::warning("Profile picture file not found for user {$this->id}: {$this->profile_picture}. Returning default avatar.");

                // Return default avatar instead of null
                return $this->getDefaultAvatarUrl();
            }
        }

        // Return default avatar for users without profile picture
        return $this->getDefaultAvatarUrl();
    }

    /**
     * Get default avatar URL based on user's initials
     */
    public function getDefaultAvatarUrl(): string
    {
        // Generate avatar using UI Avatars service or return local default
        $initials = $this->getInitials();
        $backgroundColor = $this->getAvatarColor();

        // Using UI Avatars API for consistent avatar generation
        return "https://ui-avatars.com/api/?name=" . urlencode($initials)
            . "&background=" . $backgroundColor
            . "&color=fff&size=200&bold=true";
    }

    /**
     * Get user initials for avatar
     */
    protected function getInitials(): string
    {
        $nameParts = explode(' ', trim($this->name));

        if (count($nameParts) >= 2) {
            return strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Get consistent color for user avatar based on user ID
     */
    protected function getAvatarColor(): string
    {
        $colors = [
            '3498db', // Blue
            '9b59b6', // Purple
            'e74c3c', // Red
            'f39c12', // Orange
            '1abc9c', // Turquoise
            '34495e', // Dark Gray
            '16a085', // Green Sea
            'd35400', // Pumpkin
        ];

        return $colors[$this->id % count($colors)];
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
