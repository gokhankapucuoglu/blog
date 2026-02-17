<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Casts\TitleCaseCast;
use App\Traits\SpatieActivityLogTrait;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles, SpatieActivityLogTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'username',
        'email',
        'password',
        'is_active',
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
            'is_active' => 'boolean',
            'name' => TitleCaseCast::class,
            'surname' => TitleCaseCast::class,
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->id === Auth::id()) {
                throw new \Exception('Güvenlik ihlali: Kendi hesabınızı silemezsiniz!');
            }

            if ($user->id === 1) {
                throw new \Exception('Sistem hesabı silinemez!');
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => "$this->name $this->surname",
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($panel->getId() === 'admin') {
            return $this->hasAnyRole(['super_admin', 'admin', 'editor', 'author']);
        }

        return false;
    }
}
