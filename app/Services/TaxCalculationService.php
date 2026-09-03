<?php

namespace App\Services;

use App\Models\Tax;

class TaxCalculationService
{
    public function calculateTax(float $taxableAmount, ?Tax $tax = null): array
    {
        if (!$tax || $tax->status !== 'ACTIVE' || $tax->rate <= 0) {
            return [
                'tax_code' => 'NO_TAX',
                'rate' => 0.00,
                'is_inclusive' => false,
                'subtotal' => round($taxableAmount, 2),
                'tax_amount' => 0.00,
                'total_amount' => round($taxableAmount, 2),
            ];
        }

        $rateDecimal = ((float) $tax->rate) / 100.0;

        if ($tax->is_inclusive) {
            // Inclusive tax: Subtotal = Total / (1 + rate)
            $total = round($taxableAmount, 2);
            $subtotal = round($total / (1 + $rateDecimal), 2);
            $taxAmount = round($total - $subtotal, 2);
        } else {
            // Exclusive tax: Tax = Subtotal * rate
            $subtotal = round($taxableAmount, 2);
            $taxAmount = round($subtotal * $rateDecimal, 2);
            $total = round($subtotal + $taxAmount, 2);
        }

        return [
            'tax_code' => $tax->code,
            'rate' => (float) $tax->rate,
            'is_inclusive' => (bool) $tax->is_inclusive,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
        ];
    }
}
