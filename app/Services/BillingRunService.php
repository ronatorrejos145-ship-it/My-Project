<?php

namespace App\Services;

use App\Models\BillableCharge;
use App\Models\BillingException;
use App\Models\BillingPeriod;
use App\Models\BillingProfile;
use App\Models\BillingRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class BillingRunService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService,
        protected ChargeGenerationService $chargeGenerationService
    ) {}

    public function createAndExecuteRun(string $billingDate, string $billingCycle = 'MONTHLY', ?int $userId = null): BillingRun
    {
        return DB::transaction(function () use ($billingDate, $billingCycle, $userId) {
            $runNum = $this->numberSequenceService->getNextNumber('BILLING_RUN');

            $bDate = Carbon::parse($billingDate)->startOfDay();
            $pStart = $bDate->copy()->startOfMonth()->toDateString();
            $pEnd = $bDate->copy()->endOfMonth()->toDateString();

            $run = BillingRun::create([
                'run_number' => $runNum,
                'billing_date' => $bDate->toDateString(),
                'period_start' => $pStart,
                'period_end' => $pEnd,
                'billing_cycle' => $billingCycle,
                'status' => 'PROCESSING',
                'started_at' => now(),
                'initiated_by' => $userId,
            ]);

            $profiles = BillingProfile::with(['serviceAccount.customer', 'serviceAccount.currentSubscription.package', 'serviceAccount.currentSubscription.packageVersion'])
                ->where('status', 'ACTIVE')
                ->where('billing_hold', false)
                ->get();

            $total = $profiles->count();
            $successful = 0;
            $failed = 0;
            $totalAmount = 0.00;
            $totalChargesCount = 0;

            foreach ($profiles as $profile) {
                try {
                    $account = $profile->serviceAccount;
                    if (!$account || $account->status !== 'ACTIVE') {
                        continue;
                    }

                    $subscription = $account->currentSubscription;
                    if (!$subscription || $subscription->status !== 'ACTIVE') {
                        continue;
                    }

                    // Get or create BillingPeriod for this profile
                    $period = BillingPeriod::firstOrCreate(
                        [
                            'billing_profile_id' => $profile->id,
                            'period_start' => $pStart,
                            'period_end' => $pEnd,
                        ],
                        [
                            'billing_date' => $bDate->toDateString(),
                            'due_date' => $bDate->copy()->addDays($profile->due_days ?? 15)->toDateString(),
                            'grace_date' => $bDate->copy()->addDays(($profile->due_days ?? 15) + ($profile->grace_days ?? 3))->toDateString(),
                            'status' => 'GENERATED',
                            'generated_at' => now(),
                            'billing_run_id' => $run->id,
                        ]
                    );

                    $charge = $this->chargeGenerationService->generateRecurringCharge($profile, $period, $subscription, $run, $userId);

                    $successful++;
                    $totalChargesCount++;
                    $totalAmount += (float) $charge->total_amount;
                } catch (Exception $e) {
                    $failed++;
                    $excNum = $this->numberSequenceService->getNextNumber('EXCEPTION');
                    BillingException::create([
                        'exception_number' => $excNum,
                        'billing_run_id' => $run->id,
                        'service_account_id' => $profile->service_account_id,
                        'subscription_id' => $profile->serviceAccount?->currentSubscription?->id,
                        'severity' => 'ERROR',
                        'type' => 'CALCULATION_FAILURE',
                        'message' => $e->getMessage(),
                        'status' => 'OPEN',
                    ]);
                }
            }

            $run->update([
                'status' => $failed > 0 ? 'COMPLETED_WITH_ERRORS' : 'COMPLETED',
                'total_accounts' => $total,
                'eligible_accounts' => $total,
                'successful_accounts' => $successful,
                'failed_accounts' => $failed,
                'total_charges' => $totalChargesCount,
                'total_amount' => $totalAmount,
                'completed_at' => now(),
            ]);

            AuditLogService::log(
                'EXECUTE_BILLING_RUN',
                'billing',
                $run,
                null,
                $run->toArray()
            );

            return $run;
        });
    }
}
