<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\HasAvatar;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;


class User extends Authenticatable implements HasAvatar, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable, CausesActivity, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'phone', 'type', 'avatar_url'];

    protected string $guard_name = 'web';

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
        ];
    }

    #retorna la url del storage de avatar_url
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? \Storage::url($this->avatar_url) : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->getAllPermissions()->contains('name', 'page_Dashboard');
    }

    // ---------- Spatie Activitylog ----------
    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->useLogName('users')
            ->logAll()            // todo el payload del modelo
            ->logOnlyDirty()      // solo difs
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "User {$event}");
    }
}
