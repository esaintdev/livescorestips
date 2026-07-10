<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipSubscription extends Model
{
    protected static function booted()
    {
        static::updated(function ($subscription) {
            // When a subscription is marked as active (approved), 
            // automatically delete all other dangling "pending" checkout attempts for this user.
            if ($subscription->isDirty('status') && $subscription->status === 'active') {
                self::where('user_id', $subscription->user_id)
                    ->where('id', '!=', $subscription->id)
                    ->where('status', 'pending')
                    ->delete();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'vip_plan_id',
        'name',
        'email',
        'phone',
        'payment_reference',
        'status',
        'starts_at',
        'expires_at',
        'is_disabled',
        'is_flagged',
    ];

    protected $casts = [
        'starts_at'   => 'datetime',
        'expires_at'  => 'datetime',
        'is_disabled' => 'boolean',
        'is_flagged'  => 'boolean',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the VIP plan for this subscription.
     */
    public function vipPlan()
    {
        return $this->belongsTo(VipPlan::class);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive()
    {
        return $this->status === 'active' &&
            $this->expires_at &&
            $this->expires_at->isFuture() &&
            !$this->is_disabled;
    }

    /**
     * Check if account is flagged.
     */
    public function isFlagged()
    {
        return (bool) $this->is_flagged;
    }

    /**
     * Check if account is disabled.
     */
    public function isDisabled()
    {
        return (bool) $this->is_disabled;
    }

    /**
     * Scope to get active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now())
            ->where('is_disabled', false);
    }

    /**
     * Scope for subscribed (active and not expired).
     */
    public function scopeSubscribed($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for disabled members.
     */
    public function scopeDisabled($query)
    {
        return $query->where('is_disabled', true);
    }

    /**
     * Scope for flagged members.
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }
}
