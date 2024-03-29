<?php

namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Devaslanphp\FilamentAvatar\Core\HasAvatarUrl;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Illuminate\Database\Eloquent\Relations\HasMany;
use BasementChat\Basement\Contracts\User as BasementUserContract;
use BasementChat\Basement\Traits\HasPrivateMessages;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements BasementUserContract, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasAvatarUrl, HasPanelShield, HasPrivateMessages;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function posts() : HasMany
    {
        return $this->hasMany(Post::class);
    }

}
