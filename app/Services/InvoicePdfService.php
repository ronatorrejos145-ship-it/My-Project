<?php

namespace App\Services;

use App\Models\Invoice;

class InvoicePdfService
{
    public function generateInvoiceHtml(Invoice $invoice): string
    {
        $invoice->load(['customer', 'serviceAccount', 'lines']);

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Invoice - ' . e($invoice->invoice_number) . '</title>';
        $html .= '<style>body{font-family:sans-serif;margin:20px;color:#333;} h1,h2,h3{margin-bottom:5px;} table{width:100%;border-collapse:collapse;margin-bottom:15px;} th,td{border:1px solid #ccc;padding:8px;text-align:left;} th{background:#f5f5f5;} .header{border-bottom:2px solid #333;padding-bottom:10px;margin-bottom:20px;} .totals{float:right;width:300px;}</style></head><body>';

        $html .= '<div class="header">';
        $html .= '<h1>ISP Management Platform - Official Invoice</h1>';
        $html .= '<p><strong>Invoice #:</strong> ' . e($invoice->invoice_number) . ' | <strong>Date:</strong> ' . e($invoice->invoice_date->format('Y-m-d')) . ' | <strong>Due Date:</strong> ' . e($invoice->due_date->format('Y-m-d')) . '</p>';
        $html .= '<p><strong>Status:</strong> ' . e($invoice->status) . '</p>';
        $html .= '</div>';

        $html .= '<h3>Customer & Service Details</h3>';
        $html .= '<table>';
        $html .= '<tr><th>Customer Name</th><td>' . e($invoice->customer->full_name ?? 'N/A') . '</td><th>Customer #</th><td>' . e($invoice->customer->customer_number ?? 'N/A') . '</td></tr>';
        $html .= '<tr><th>Service Account #</th><td>' . e($invoice->serviceAccount->account_number ?? 'N/A') . '</td><th>Billing Period</th><td>' . e($invoice->period_start ? $invoice->period_start->format('Y-m-d') : 'N/A') . ' to ' . e($invoice->period_end ? $invoice->period_end->format('Y-m-d') : 'N/A') . '</td></tr>';
        $html .= '</table>';

        $html .= '<h3>Line Items</h3>';
        $html .= '<table><tr><th>Description</th><th>Type</th><th>Qty</th><th>Unit Price</th><th>Tax</th><th>Total</th></tr>';
        foreach ($invoice->lines as $line) {
            $html .= '<tr><td>' . e($line->description) . '</td><td>' . e($line->charge_type) . '</td><td>' . e($line->quantity) . '</td><td>' . e(number_format($line->unit_price, 2)) . '</td><td>' . e(number_format($line->tax_amount, 2)) . '</td><td>PHP ' . e(number_format($line->total_amount, 2)) . '</td></tr>';
        }
        $html .= '</table>';

        $html .= '<div class="totals"><table>';
        $html .= '<tr><th>Subtotal</th><td>PHP ' . e(number_format($invoice->subtotal, 2)) . '</td></tr>';
        $html .= '<tr><th>Tax Amount</th><td>PHP ' . e(number_format($invoice->tax_amount, 2)) . '</td></tr>';
        $html .= '<tr><th>Total Amount</th><td><strong>PHP ' . e(number_format($invoice->total_amount, 2)) . '</strong></td></tr>';
        $html .= '<tr><th>Amount Paid</th><td>PHP ' . e(number_format($invoice->amount_paid, 2)) . '</td></tr>';
        $html .= '<tr><th>Amount Due</th><td><strong>PHP ' . e(number_format($invoice->amount_due, 2)) . '</strong></td></tr>';
        $html .= '</table></div>';

        $html .= '</body></html>';

        return $html;
    }
}
