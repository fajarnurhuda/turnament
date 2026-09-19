<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'code',
        'logo',
        'manager_name',
        'manager_contact',
    ];

    protected $appends = [
        'logo_url',
        'initials',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('jersey_number');
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'away_team_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }

    /**
     * Helper to get full logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }

        return asset('storage/'.$this->logo);
    }

    /**
     * Helper to get a team badge initials if logo is empty.
     */
    public function getInitialsAttribute(): string
    {
        if ($this->code) {
            return strtoupper(substr($this->code, 0, 3));
        }

        $words = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($words, 0, 3) as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
        }

        return $initials ?: 'FT';
    }
}
