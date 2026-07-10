<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VipResultItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vip_result_id',
        'league_name',
        'match_time',
        'home_team',
        'away_team',
        'home_score',
        'away_score',
        'tip',
        'odds',
        'is_won',
    ];

    protected $casts = [
        'is_won' => 'boolean',
        'odds' => 'decimal:2',
    ];

    /**
     * Get the VIP result that owns the item.
     */
    public function vipResult()
    {
        return $this->belongsTo(VipResult::class);
    }
}
