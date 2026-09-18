<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'team_id',
        'player_id',
        'assist_player_id',
        'event_type',
        'minute',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'minute' => 'integer',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function assistPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'assist_player_id');
    }

    /**
     * Human-readable Indonesian event type label.
     */
    public function getEventLabelAttribute(): string
    {
        return match ($this->event_type) {
            'goal' => 'GOL',
            'own_goal' => 'GOL BUNUH DIRI',
            'yellow_card' => 'KARTU KUNING',
            'red_card' => 'KARTU MERAH',
            'second_yellow' => 'KARTU KUNING KEDUA (MERAH)',
            'assist' => 'ASSIST',
            default => strtoupper($this->event_type),
        };
    }
}
