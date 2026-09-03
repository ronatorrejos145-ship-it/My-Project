<?php

namespace App\Services;

use App\Models\InstallationWorkOrder;

class InstallationReportPdfService
{
    public function generatePdfHtml(InstallationWorkOrder $workOrder): string
    {
        $workOrder->load([
            'customer',
            'package',
            'packageVersion',
            'assignedTechnician',
            'supervisor',
            'checklistResponses.item',
            'materials',
            'equipment',
            'tests',
            'acceptances',
            'supervisorReviews',
        ]);

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Installation Work Order - ' . e($workOrder->work_order_number) . '</title>';
        $html .= '<style>body{font-family:sans-serif;margin:20px;color:#333;} h1,h2,h3{margin-bottom:5px;} table{width:100%;border-collapse:collapse;margin-bottom:15px;} th,td{border:1px solid #ccc;padding:8px;text-align:left;} th{background:#f5f5f5;} .header{border-bottom:2px solid #333;padding-bottom:10px;margin-bottom:20px;}</style></head><body>';

        $html .= '<div class="header">';
        $html .= '<h1>ISP Management Platform - Installation Work Order</h1>';
        $html .= '<p><strong>WO #:</strong> ' . e($workOrder->work_order_number) . ' | <strong>Status:</strong> ' . e($workOrder->status) . ' | <strong>Type:</strong> ' . e($workOrder->work_type) . '</p>';
        $html .= '</div>';

        $html .= '<h3>Customer Information</h3>';
        $html .= '<table>';
        $html .= '<tr><th>Customer Name</th><td>' . e($workOrder->customer->full_name ?? $workOrder->customer->first_name . ' ' . $workOrder->customer->last_name) . '</td><th>Customer #</th><td>' . e($workOrder->customer->customer_number ?? 'N/A') . '</td></tr>';
        $html .= '<tr><th>Package</th><td>' . e($workOrder->package->name ?? 'N/A') . ' (' . e($workOrder->packageVersion->download_speed ?? 0) . ' Mbps)</td><th>Scheduled Date</th><td>' . e($workOrder->scheduled_start ? $workOrder->scheduled_start->format('Y-m-d H:i') : 'Unscheduled') . '</td></tr>';
        $html .= '<tr><th>Technician</th><td>' . e($workOrder->assignedTechnician->first_name ?? 'Unassigned') . ' ' . e($workOrder->assignedTechnician->last_name ?? '') . '</td><th>GPS Coordinates</th><td>' . e($workOrder->latitude) . ', ' . e($workOrder->longitude) . '</td></tr>';
        $html .= '</table>';

        $html .= '<h3>Equipment Installed</h3>';
        $html .= '<table><tr><th>Type</th><th>Model</th><th>Serial Number</th><th>MAC Address</th></tr>';
        foreach ($workOrder->equipment as $eq) {
            $html .= '<tr><td>' . e($eq->equipment_type) . '</td><td>' . e($eq->model_name) . '</td><td>' . e($eq->serial_number) . '</td><td>' . e($eq->mac_address) . '</td></tr>';
        }
        if ($workOrder->equipment->isEmpty()) {
            $html .= '<tr><td colspan="4">No equipment assets logged.</td></tr>';
        }
        $html .= '</table>';

        $html .= '<h3>Technical Test Results</h3>';
        $html .= '<table><tr><th>Test Type</th><th>Measured Value</th><th>Unit</th><th>Result</th></tr>';
        foreach ($workOrder->tests as $t) {
            $html .= '<tr><td>' . e($t->test_type) . '</td><td>' . e($t->measured_value) . '</td><td>' . e($t->unit) . '</td><td>' . e($t->result) . '</td></tr>';
        }
        if ($workOrder->tests->isEmpty()) {
            $html .= '<tr><td colspan="4">No technical tests recorded.</td></tr>';
        }
        $html .= '</table>';

        $html .= '<h3>Customer Acceptance & Sign-off</h3>';
        $acc = $workOrder->acceptances->first();
        if ($acc) {
            $html .= '<p><strong>Signer Name:</strong> ' . e($acc->signer_name) . ' (' . e($acc->signer_relationship) . ')</p>';
            $html .= '<p><strong>Status:</strong> ' . e($acc->acceptance_status) . ' | <strong>Signed At:</strong> ' . e($acc->signed_at ? $acc->signed_at->format('Y-m-d H:i:s') : 'N/A') . '</p>';
        } else {
            $html .= '<p>Pending customer acceptance signature.</p>';
        }

        $html .= '</body></html>';

        return $html;
    }
}
