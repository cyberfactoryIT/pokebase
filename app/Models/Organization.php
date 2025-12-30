<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
    /**
     * Relazione con le promozioni applicate all'organizzazione.
     */
{
    use HasFactory, SoftDeletes;

    /**
     * Relazione con le promozioni applicate all'organizzazione.
     */
    public function promotions()
    {
        return $this->belongsToMany(\App\Models\Promotion::class, 'organization_promotions')
            ->withPivot(['redeemed_at','coupon_code','meta']);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'slug',
        'timezone',
        'subscription_date',
        'renew_date',
        'end_promotion_date',
    'promotion_code',
    'subscription_cancelled',
    'cancellation_subscription_date',
    'reactivate_subscription_date',
    // Billing fields
    'company',
    'billing_email',
    'vat_number',
    'address_line1',
    'address_line2',
    'city',
    'postcode',
    'country',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the users for the organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }
    /**
     * Get the invoices for the organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Invoice::class);
    }
    
    /**
     * Get the pricing plan for the organization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pricingPlan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\PricingPlan::class, 'pricing_plan_id');
    }
    /**
     * Applica una promozione all'organizzazione e aggiorna i campi di tracking.
     */
    public function applyPromotion(Promotion $promotion, $endDate = null, $code = null)
    {
    $this->promotion_code = $code ?? $promotion->code;
    $this->end_promotion_date = $this->subscription_date ? \Carbon\Carbon::parse($this->subscription_date)->addYear() : now()->addYear();
        $this->save();
        // Associa la promozione se non già associata
        if (!$this->promotions()->where('promotion_id', $promotion->id)->exists()) {
            $this->promotions()->attach($promotion->id, [
                'redeemed_at' => now(),
                'coupon_code' => $this->promotion_code,
            ]);
        }
    }
}
