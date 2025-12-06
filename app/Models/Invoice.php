<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'organization_id','number','provider','provider_id','currency',
        'subtotal_cents','tax_cents','total_cents','status','issued_at','due_at','paid_at',
        'receipt_pdf_path','discount_cents','coupon_code','promotion_snapshot','meta',
        'org_name','org_company','org_billing_email','org_vat','org_address','org_city','org_country'
    ];
    protected $casts = [
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'promotion_snapshot' => 'array',
        'meta' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
