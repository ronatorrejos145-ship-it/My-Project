<?php

namespace App\Services;

use App\Models\TechnicalSurvey;

class SurveyReportPdfService
{
    /**
     * Generate HTML representation of printable Technical Survey Report.
     */
    public function generateReportHtml(TechnicalSurvey $survey): string
    {
        $survey->load([
            'application',
            'customer',
            'package',
            'technician.user',
            'supervisor.user',
            'responses.checklistItem',
            'measurements',
            'photos',
            'materials.item',
            'equipment.assetModel',
            'signatures',
        ]);

        $appName = $survey->application?->applicant_name ?: $survey->customer?->full_name ?: 'Applicant';
        $pkgName = $survey->package->name;
        $surveyNum = $survey->survey_number;

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Technical Survey Report - {$surveyNum}</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 20px; }
                h1 { font-size: 18px; color: #0f172a; margin-bottom: 5px; }
                .header { border-bottom: 2px solid #6366f1; padding-bottom: 10px; margin-bottom: 15px; }
                .grid { display: flex; justify-content: space-between; margin-bottom: 15px; }
                .col { width: 48%; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
                th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
                th { background-color: #f8fafc; font-size: 11px; }
                .badge { font-weight: bold; padding: 2px 6px; border-radius: 4px; font-size: 10px; display: inline-block; }
                .pass { background-color: #d1fae5; color: #065f46; }
                .fail { background-color: #ffe4e6; color: #9f1239; }
                .section-title { font-size: 13px; font-weight: bold; background-color: #f1f5f9; padding: 4px 8px; margin-top: 15px; border-left: 4px solid #6366f1; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>APEX BROADBAND — TECHNICAL FIELD SURVEY REPORT</h1>
                <div>Survey Number: <strong>{$surveyNum}</strong> | Status: <strong>{$survey->status}</strong> | Date: " . ($survey->completed_at?->format('M d, Y') ?: date('M d, Y')) . "</div>
            </div>

            <div class='grid'>
                <div class='col'>
                    <strong>Applicant Details:</strong><br>
                    Name: {$appName}<br>
                    Phone: " . ($survey->application?->primary_phone ?: $survey->customer?->primary_phone) . "<br>
                    Target Package: {$pkgName}<br>
                </div>
                <div class='col'>
                    <strong>Field Assignment:</strong><br>
                    Lead Technician: " . ($survey->technician?->user?->name ?: 'Assigned Technician') . "<br>
                    Supervisor Reviewer: " . ($survey->supervisor?->user?->name ?: 'Technical Supervisor') . "<br>
                    GPS Arrival Distance: {$survey->arrival_distance_meters} meters<br>
                </div>
            </div>

            <div class='section-title'>I. Field Technical Measurements & Signal Assessment</div>
            <table>
                <thead>
                    <tr>
                        <th>Measurement Type</th>
                        <th>Recorded Value</th>
                        <th>Status</th>
                        <th>Measurement Tool</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($survey->measurements as $m) {
            $badgeClass = $m->acceptance_status === 'PASS' ? 'pass' : 'fail';
            $html .= "
                    <tr>
                        <td>{$m->measurement_type}</td>
                        <td><strong>{$m->value} {$m->unit}</strong></td>
                        <td><span class='badge {$badgeClass}'>{$m->acceptance_status}</span></td>
                        <td>{$m->measurement_tool}</td>
                    </tr>";
        }

        $html .= "
                </tbody>
            </table>

            <div class='section-title'>II. Estimated Material & Hardware Requirements</div>
            <table>
                <thead>
                    <tr>
                        <th>Item / Description</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($survey->materials as $mat) {
            $html .= "
                    <tr>
                        <td>{$mat->item_name}</td>
                        <td><strong>{$mat->estimated_quantity}</strong></td>
                        <td>{$mat->unit}</td>
                    </tr>";
        }

        $html .= "
                </tbody>
            </table>

            <div class='section-title'>III. Final Technical Decision & Justification</div>
            <div style='padding: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0; margin-top: 10px;'>
                <strong>Technical Recommendation:</strong> {$survey->technical_recommendation}<br>
                <strong>Final Feasibility Decision:</strong> {$survey->final_decision}<br>
                <strong>Summary:</strong> " . ($survey->technical_summary ?: 'On-site technical evaluation completed.') . "
            </div>
        </body>
        </html>";

        return $html;
    }
}
