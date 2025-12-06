<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnforcesSuperAdmin;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SuperAdminBillingController extends Controller
{
    use EnforcesSuperAdmin;

    public function __construct()
    {
        $this->enforceSuperAdmin();
    }

    public function showInvoice(Invoice $invoice)
    {
        // Superadmin può vedere tutte le fatture
        return view('billing.invoice', [
            'invoice' => $invoice,
            'org' => config('organizations.enabled') ? $invoice->organization : null
        ]);
    }

    public function exportInvoices(Request $request)
    {
        $query = \App\Models\Invoice::with('organization');
        if (config('organizations.enabled') && $request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        $invoices = $query->get();
        $csv = [];
        $header = [
            'number', 'issued_at', 'status', 'currency', 'subtotal_cents', 'discount_cents', 'tax_cents', 'total_cents',
            'org_name', 'org_company', 'org_billing_email', 'org_vat', 'org_address', 'org_city', 'org_country',
            'organization_id', 'description', 'coupon_code', 'billing_period'
        ];
        $csv[] = implode(',', $header);
        foreach ($invoices as $invoice) {
            $row = [
                $invoice->number,
                $invoice->issued_at,
                $invoice->status,
                $invoice->currency,
                $invoice->subtotal_cents,
                $invoice->discount_cents,
                $invoice->tax_cents,
                $invoice->total_cents,
                $invoice->org_name,
                $invoice->org_company,
                $invoice->org_billing_email,
                $invoice->org_vat,
                $invoice->org_address,
                $invoice->org_city,
                $invoice->org_country,
                $invoice->organization_id,
                $invoice->description,
                $invoice->coupon_code,
                $invoice->billing_period,
            ];
            $csv[] = implode(',', array_map(function($v) {
                return '"' . str_replace('"', '""', $v) . '"';
            }, $row));
        }
        $csvContent = implode("\n", $csv);
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="invoices_export.csv"',
        ]);
    }
}
