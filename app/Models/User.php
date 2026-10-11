<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'avatar',
        'password',
        'is_active',
        'must_change_password',
        'last_login_at',
        'locale',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function quotesAssigned(): HasMany
    {
        return $this->hasMany(Quote::class, 'assigned_user_id');
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function adminNotifications(): HasMany
    {
        return $this->hasMany(AdminNotification::class);
    }

    public function usesDemoPassword(): bool
    {
        return Hash::check('password', $this->getAuthPassword());
    }

    public function shouldPromptPasswordChange(): bool
    {
        if (! $this->must_change_password) {
            return false;
        }

        if ($this->usesDemoPassword()) {
            return true;
        }

        $this->forceFill(['must_change_password' => false])->saveQuietly();

        return false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasStaffRole(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Admin', 'Manager', 'Editor', 'Sales']);
    }

    public function allows(string $permission): bool
    {
        if ($this->hasRole('Super Admin') || $this->hasRole('Admin')) {
            return true;
        }

        try {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        } catch (\Throwable) {
        }

        return $this->hasStaffRole();
    }
}
