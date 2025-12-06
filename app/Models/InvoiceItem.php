<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id','description','quantity','unit_price_cents','total_cents','sort'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
