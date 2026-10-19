<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'role',
        'reporting_to_id',
        'designation',
        'department',
        'is_active',
        'password',
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
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Immediate manager / supervisor.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_to_id');
    }

    /**
     * Direct reports.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'reporting_to_id');
    }

    /**
     * All recursive subordinate IDs under this user.
     */
    public function allSubordinateIds(): array
    {
        $ids = [];
        $directReports = $this->subordinates()->get();

        foreach ($directReports as $report) {
            $ids[] = $report->id;
            $ids = array_merge($ids, $report->allSubordinateIds());
        }

        return array_unique($ids);
    }

    /**
     * Tasks assigned by this user.
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Tasks assigned to this user.
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }
}
