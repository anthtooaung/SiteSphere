<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'slug', 'password', 'password_set', 'user_dob', 'user_phone', 'user_bio', 'user_image', 'two_factor_enabled', 'is_verified', 'status', 'is_permanently_banned', 'banned_by', 'banned_at', 'ban_reason', 'appeal_submitted_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    private const DEFAULT_ACCENT_COLOR = '#6c5ce7';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user): void {
            $user->slug = Str::slug($user->name);
        });

        static::created(function (User $user): void {
            $user->provisionDefaultPreferences();
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
     * Determine if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Determine if the user is an OAuth-only user (no password set).
     */
    public function isOauthOnly(): bool
    {
        return $this->socialAccounts()->exists() && ! $this->password_set;
    }

    /**
     * User settings relation
     */
    public function settings(): HasOne
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

    public function provisionDefaultPreferences(): void
    {
        $theme = Themes::query()->firstOrCreate([
            'accent_color' => self::DEFAULT_ACCENT_COLOR,
        ]);

        $this->settings()->firstOrCreate(
            [],
            [
                'menuBar_location' => 'left',
                'noti_location' => 'top-end',
                'dark_mode' => false,
                'user_post_visible' => true,
                'theme_id' => $theme->id,
            ],
        );

        $defaultFont = Fonts::query()
            ->where('is_default', true)
            ->orderBy('sort_order')
            ->firstOrFail();

        $this->currentFonts()->syncWithoutDetaching([$defaultFont->id]);
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
            'two_factor_enabled' => 'boolean',
            'is_permanently_banned' => 'boolean',
            'banned_at' => 'datetime',
            'appeal_submitted_at' => 'datetime',
        ];
    }

    /**
     * Determine if the user is banned.
     */
    public function isBanned(): bool
    {
        return $this->status === 'banned';
    }

    /**
     * Determine if the user is marked as unsecure.
     */
    public function isUnsecure(): bool
    {
        return $this->status === 'unsecure';
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
