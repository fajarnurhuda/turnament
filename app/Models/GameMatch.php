<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameMatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'matches';

    protected $fillable = [
        'category_id',
        'stage_id',
        'group_id',
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'match_date',
        'venue_id',
        'venue',
        'status',
        'current_minute',
        'half_duration_minutes',
        'extra_time_duration_minutes',
        'referee_1_id',
        'referee_2_id',
        'referee_3_id',
        'timer_seconds',
        'timer_running',
        'timer_started_at',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'home_score' => 'integer',
            'away_score' => 'integer',
            'current_minute' => 'integer',
            'half_duration_minutes' => 'integer',
            'extra_time_duration_minutes' => 'integer',
            'referee_1_id' => 'integer',
            'referee_2_id' => 'integer',
            'referee_3_id' => 'integer',
            'timer_seconds' => 'integer',
            'timer_running' => 'boolean',
            'timer_started_at' => 'datetime',
        ];
    }

    /**
     * Duration of 1 half in seconds (e.g. 12 mins = 720s, 20 mins = 1200s).
     */
    public function getHalfDurationSecondsAttribute(): int
    {
        return ((int) ($this->half_duration_minutes ?: 20)) * 60;
    }

    /**
     * Total normal match regulation seconds (e.g. 2 x 12 = 1440s, 2 x 20 = 2400s).
     */
    public function getFullTimeSecondsAttribute(): int
    {
        return $this->half_duration_seconds * 2;
    }

    /**
     * Extra time duration in seconds (e.g. 5 mins = 300s).
     */
    public function getExtraTimeSecondsAttribute(): int
    {
        return ((int) ($this->extra_time_duration_minutes ?: 5)) * 60;
    }

    /**
     * On-field Lead / Referee 1 (Wasit Utama).
     */
    public function referee1(): BelongsTo
    {
        return $this->belongsTo(Referee::class, 'referee_1_id');
    }

    /**
     * On-field Referee 2 (Wasit 2).
     */
    public function referee2(): BelongsTo
    {
        return $this->belongsTo(Referee::class, 'referee_2_id');
    }

    /**
     * On-field Referee 3 / Reserve (Wasit 3 / Cadangan).
     */
    public function referee3(): BelongsTo
    {
        return $this->belongsTo(Referee::class, 'referee_3_id');
    }

    /**
     * Assigned table referees / operators for this match.
     */
    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'match_operators', 'match_id', 'user_id');
    }

    /**
     * Compute current elapsed seconds in real-time.
     */
    public function getElapsedSecondsAttribute(): int
    {
        $seconds = $this->timer_seconds ?? ($this->current_minute * 60);

        if ($this->timer_running && $this->timer_started_at) {
            $seconds += (int) $this->timer_started_at->diffInSeconds(now(), true);
        }

        return (int) $seconds;
    }

    /**
     * Get stopwatch time formatted as MM:SS (e.g. "14:23").
     */
    public function getTimeFormattedAttribute(): string
    {
        $seconds = $this->elapsed_seconds;
        $minutes = floor($seconds / 60);
        $remainder = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $remainder);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function venueModel(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id')->orderBy('minute', 'asc')->orderBy('id', 'asc');
    }

    // Scopes
    public function scopeLive(Builder $query): Builder
    {
        return $query->whereIn('status', ['first_half', 'half_time', 'second_half', 'extra_time']);
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('status', 'finished');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Check if match is currently in progress.
     */
    public function isLive(): bool
    {
        return in_array($this->status, ['first_half', 'half_time', 'second_half', 'extra_time']);
    }

    /**
     * Status badge text for UI.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'first_half' => ['text' => '1ST HALF', 'color' => 'bg-live-pulse/20 text-live-pulse border-live-pulse/40 animate-pulse'],
            'half_time' => ['text' => 'HALF TIME', 'color' => 'bg-card-yellow/20 text-card-yellow border-card-yellow/40'],
            'second_half' => ['text' => '2ND HALF', 'color' => 'bg-live-pulse/20 text-live-pulse border-live-pulse/40 animate-pulse'],
            'extra_time' => ['text' => 'EXTRA TIME', 'color' => 'bg-secondary-container/20 text-secondary border-secondary/40 animate-pulse'],
            'finished' => ['text' => 'FULL TIME', 'color' => 'bg-surface-bright text-text-muted border-court-border'],
            'postponed' => ['text' => 'DITUNDA', 'color' => 'bg-card-red/20 text-card-red border-card-red/40'],
            default => ['text' => 'UPCOMING', 'color' => 'bg-surface-container text-text-muted border-court-border'],
        };
    }
}
