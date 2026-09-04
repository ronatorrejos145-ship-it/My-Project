<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\InstallationWorkOrder;

class TechnicianEligibilityService
{
    public function checkEligibility(Employee $employee, InstallationWorkOrder $workOrder): array
    {
        $reasons = [];
        $isEligible = true;

        if ($employee->employment_status !== 'ACTIVE') {
            $isEligible = false;
            $reasons[] = 'Employee is not active.';
        }

        if ($employee->branch_id !== $workOrder->branch_id) {
            $isEligible = false;
            $reasons[] = 'Employee branch does not match installation work order branch.';
        }

        return [
            'eligible' => $isEligible,
            'reasons' => $reasons,
        ];
    }
}
