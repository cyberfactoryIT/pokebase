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

    /**
     * Generate next progressive invoice number with W prefix (e.g., W000001, W000002)
     */
    public static function generateInvoiceNumber(): string
    {
        // Get the last invoice number
        $lastInvoice = self::orderBy('id', 'desc')->first();
        
        if (!$lastInvoice || !preg_match('/^W(\d+)$/', $lastInvoice->number, $matches)) {
            // Start from W000001 if no invoice exists or format doesn't match
            return 'W000001';
        }
        
        // Increment the number
        $lastNumber = intval($matches[1]);
        $nextNumber = $lastNumber + 1;
        
        // Format with leading zeros (6 digits)
        return 'W' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
