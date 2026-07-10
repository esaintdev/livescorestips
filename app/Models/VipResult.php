<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VipResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'total_odds',
        'status',
        'win_rate_display',
        'stage',
    ];

    protected $casts = [
        'date' => 'date',
        'total_odds' => 'decimal:2',
        'stage' => 'string',
    ];

    /**
     * Get the items for this VIP result.
     */
    public function items()
    {
        return $this->hasMany(VipResultItem::class);
    }
}
