<?php

namespace App\Models;

use Database\Factories\TeamInviteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvite extends Model
{
    /** @use HasFactory<TeamInviteFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'token'
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
