<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'jersey_number',
        'position',
        'photo',
        'is_captain',
    ];

    protected function casts(): array
    {
        return [
            'jersey_number' => 'integer',
            'is_captain' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }

    public function assists(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'assist_player_id');
    }

    /**
     * Get position label in Indonesian.
     */
    public function getPositionLabelAttribute(): string
    {
        return match ($this->position) {
            'GK' => 'Kiper',
            'DEF' => 'Anchor / Defender',
            'FLA' => 'Flank',
            'PIV' => 'Pivot',
            default => $this->position,
        };
    }
}
