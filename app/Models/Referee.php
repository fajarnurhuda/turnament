<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'license',
        'phone',
        'city',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Matches where this referee was the Lead / Wasit 1.
     */
    public function matchesAsReferee1(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'referee_1_id');
    }

    /**
     * Matches where this referee was Wasit 2.
     */
    public function matchesAsReferee2(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'referee_2_id');
    }

    /**
     * Matches where this referee was Wasit 3 / Cadangan.
     */
    public function matchesAsReferee3(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'referee_3_id');
    }

    /**
     * Total matches led on the pitch.
     */
    public function getTotalMatchesCountAttribute(): int
    {
        return $this->matchesAsReferee1()->count() + $this->matchesAsReferee2()->count() + $this->matchesAsReferee3()->count();
    }
}
