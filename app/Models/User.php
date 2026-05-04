<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function plan()
    {
        $subscription = $this->subscriptions()
            ->where('stripe_status', 'active')
            ->orWhere('stripe_status', 'trialing')
            ->first();

        if (!$subscription) {
            return (object) ['name' => 'Free'];
        }

        return (object) ['name' => 'Pro'];
    }

    public function hasProPlan(): bool
    {
        return $this->subscriptions()
            ->where(function ($query) {
                $query->where('stripe_status', 'active')
                      ->orWhere('stripe_status', 'trialing');
            })
            ->exists();
    }
}
