<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    // Role constants
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_AGENT = 'agent';
    const ROLE_VIEWER = 'viewer';

    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'avatar',
        'role',
        'is_active',
        'last_login_at',
        'phone',
        'department',
        // Legacy (kept for compatibility)
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // ====================
    // RELATIONSHIPS
    // ====================

    public function mailingLists()
    {
        return $this->hasMany(MailingList::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function emailTemplates()
    {
        return $this->hasMany(EmailTemplate::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ====================
    // ROLE CHECKS
    // ====================

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]) 
            || $this->is_admin === true; // Legacy support
    }

    public function isManager(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN, 
            self::ROLE_ADMIN, 
            self::ROLE_MANAGER
        ]);
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    // ====================
    // PERMISSION CHECKS
    // ====================

    public function can($permission, $arguments = []): bool
    {
        // Super admins can do everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Define role-based permissions
        $rolePermissions = [
            self::ROLE_ADMIN => [
                'users.manage', 'campaigns.manage', 'lists.manage',
                'inboxes.manage', 'settings.manage', 'reports.view'
            ],
            self::ROLE_MANAGER => [
                'campaigns.manage', 'campaigns.create', 'campaigns.edit',
                'lists.manage', 'emails.send', 'reports.view'
            ],
            self::ROLE_AGENT => [
                'campaigns.view', 'emails.send', 'reports.view'
            ],
            self::ROLE_VIEWER => [
                'campaigns.view', 'reports.view'
            ],
        ];

        $permissions = $rolePermissions[$this->role] ?? [];
        
        return in_array($permission, $permissions) 
            || parent::can($permission, $arguments);
    }

    // ====================
    // ADMINLTE INTEGRATION
    // ====================

    public function adminlte_image()
    {
        return $this->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    public function adminlte_desc()
    {
        return ucwords(str_replace('_', ' ', $this->role ?? 'User'));
    }

    public function adminlte_profile_url()
    {
        return 'profile';
    }
}
