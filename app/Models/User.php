<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    protected $guard_name = 'web';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'locale',
        'theme',
        'email_verification_token',
        'email_verification_expires_at',
    ];
    /**
     * Get the user's preferred locale.
     */
    public function getLocale()
    {
        return $this->locale ?? config('app.locale');
    }

    /**
     * Set the user's preferred locale.
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->save();
    }

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
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected $guarded = [];

    /**
     * Override default password reset notification to use custom template.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPasswordNotification($token));
    }
    

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Return a display name for the user's organization.
     * If organizations are disabled, return the user's name as fallback.
     */
    public function getOrganizationNameAttribute()
    {
        if (config('organizations.enabled') && $this->organization) {
            return $this->organization->name;
        }

        return $this->name;
    }

    /**
     * Get all decks for this user
     */
    public function decks()
    {
        return $this->hasMany(\App\Models\Deck::class);
    }

    /**
     * Get all collection items for this user
     */
    public function collection()
    {
        return $this->hasMany(\App\Models\UserCollection::class);
    }

    /**
     * Get all games enabled for this user
     */
    public function games()
    {
        return $this->belongsToMany(\App\Models\Game::class, 'game_user')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific game enabled
     */
    public function hasGame($gameCode): bool
    {
        return $this->games()->where('code', $gameCode)->exists();
    }

    /**
     * Check if user has any games enabled
     */
    public function hasAnyGames(): bool
    {
        return $this->games()->count() > 0;
    }

    
     /**
     * Override default email verification notification to use custom template.
     */
    public function sendEmailVerificationNotification()
    {
        // Use the same token logic as registration
        if (!$this->email_verification_token || $this->email_verification_expires_at < now()) {
            $this->email_verification_token = \Str::random(32);
            $this->email_verification_expires_at = now()->addHours(24);
            $this->save();
        }
        \Log::info('Invio notifica verifica email', [
            'user_id' => $this->id,
            'email' => $this->email,
            'token' => $this->email_verification_token,
            'expires_at' => $this->email_verification_expires_at,
        ]);
        $this->notify(new \App\Notifications\VerifyEmailNotification($this->email_verification_token));
    }
     
}
