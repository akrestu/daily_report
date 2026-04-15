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
        'job_site_id',
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

    public function jobSite(): BelongsTo
    {
        return $this->belongsTo(JobSite::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function approvedReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'approved_by');
    }

    public function createdJobPlans(): HasMany
    {
        return $this->hasMany(JobPlan::class, 'creator_id');
    }

    public function assignedJobPlans(): HasMany
    {
        return $this->hasMany(JobPlan::class, 'assignee_id');
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

    public function isLevel6(): bool
    {
        return $this->hasRole('level6');
    }

    public function isLevel7(): bool
    {
        return $this->hasRole('level7');
    }

    public function isLevel8(): bool
    {
        return $this->hasRole('level8');
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
     * Get the role level number (1-8) or 0 for non-level roles
     */
    public function getRoleLevel(): int
    {
        if ($this->isLevel1()) return 1;
        if ($this->isLevel2()) return 2;
        if ($this->isLevel3()) return 3;
        if ($this->isLevel4()) return 4;
        if ($this->isLevel5()) return 5;
        if ($this->isLevel6()) return 6;
        if ($this->isLevel7()) return 7;
        if ($this->isLevel8()) return 8;
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

        // Level 8 can approve Level 6 and Level 7 (2 levels below)
        if ($approverLevel === 8) {
            return $userLevel === 6 || $userLevel === 7;
        }

        // Level 2-7 can approve reports from 1 or 2 levels below
        // Level 2 only approves Level 1 (no Level 0 exists)
        if ($approverLevel >= 2 && $approverLevel <= 7) {
            return $userLevel > 0 && (
                $userLevel === ($approverLevel - 1) ||
                $userLevel === ($approverLevel - 2)
            );
        }

        return false;
    }

    /**
     * Get eligible PIC roles based on current user's role
     * Returns array of role slugs that can be selected as PIC
     */
    public function getEligiblePicRoles(): array
    {
        // Level 1: can select Level 2 and Level 3
        if ($this->isLevel1()) {
            return ['level2', 'level3'];
        }

        // Level 2: can select Level 3 and Level 4
        if ($this->isLevel2()) {
            return ['level3', 'level4'];
        }

        // Level 3: can select Level 4 and Level 5
        if ($this->isLevel3()) {
            return ['level4', 'level5'];
        }

        // Level 4: can select Level 5 and Level 6
        if ($this->isLevel4()) {
            return ['level5', 'level6'];
        }

        // Level 5: can select Level 6 and Level 7
        if ($this->isLevel5()) {
            return ['level6', 'level7'];
        }

        // Level 6: can select Level 7 and Level 8 (Level 8 is cross-department, same job site)
        if ($this->isLevel6()) {
            return ['level7', 'level8'];
        }

        // Level 7: can select Level 8 (cross-department, same job site)
        if ($this->isLevel7()) {
            return ['level8'];
        }

        // Level 8: cannot create reports, so no eligible PIC
        if ($this->isLevel8()) {
            return [];
        }

        // Admin can select any level except themselves
        if ($this->isAdmin()) {
            return ['level2', 'level3', 'level4', 'level5', 'level6', 'level7', 'level8'];
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
        // Level 8 can be PIC for Level 6-7 reports
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
            'job_approved'       => true,
            'job_rejected'       => true,
            'pending_approval'   => true,
            'new_comment'        => true,
            'job_plan_assigned'  => true,
            'job_plan_updated'   => true,
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
