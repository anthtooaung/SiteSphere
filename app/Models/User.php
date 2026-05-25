<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'slug', 'password', 'user_dob', 'user_phone', 'user_bio', 'user_image', 'is_verified'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->slug = Str::slug($user->name);
        });
    }

    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return (bool) $this->is_verified;
    }

    /**
     * Mark the user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'is_verified' => true,
        ])->save();
    }

    /**
     * User settings relation
     */
    public function settings()
    {
        return $this->hasOne(Settings::class);
    }

    /**
     * Fonts currently selected by the user.
     */
    public function currentFonts(): BelongsToMany
    {
        return $this->belongsToMany(Fonts::class, 'user_current_fonts', 'user_id', 'font_id')
            ->withTimestamps();
    }

    /**
     * Social accounts linked to this user.
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrl(): string
    {
        if (! $this->user_image) {
            return '';
        }

        if (Str::startsWith($this->user_image, ['http://', 'https://'])) {
            return $this->user_image;
        }

        return asset('storage/'.$this->user_image);
    }
}
