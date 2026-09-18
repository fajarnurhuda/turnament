<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Check if user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has operator role.
     */
    public function isOperator(): bool
    {
        return in_array($this->role, ['operator', 'admin']);
    }

    /**
     * Matches assigned to this operator.
     */
    public function assignedMatches(): BelongsToMany
    {
        return $this->belongsToMany(GameMatch::class, 'match_operators', 'user_id', 'match_id');
    }

    /**
     * Check if user can control a given match.
     */
    public function canControlMatch(GameMatch $match): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->assignedMatches()->where('matches.id', $match->id)->exists();
    }

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
        ];
    }
}
