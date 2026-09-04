<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerServiceRequest;
use App\Models\ServiceAccount;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CustomerServiceRequestService
{
    public function __construct(
        protected NumberSequenceService $numberSequenceService
    ) {}

    public function createRequest(
        Customer $customer,
        string $requestType,
        ?ServiceAccount $serviceAccount = null,
        ?ServicePackage $targetPackage = null,
        ?array $payload = null
    ): CustomerServiceRequest {
        return DB::transaction(function () use ($customer, $requestType, $serviceAccount, $targetPackage, $payload) {
            $reqNum = $this->numberSequenceService->getNextNumber('SERVICE_REQUEST');

            $request = CustomerServiceRequest::create([
                'request_number' => $reqNum,
                'customer_id' => $customer->id,
                'service_account_id' => $serviceAccount?->id,
                'request_type' => $requestType,
                'target_package_id' => $targetPackage?->id,
                'payload' => $payload,
                'status' => 'SUBMITTED',
            ]);

            AuditLogService::log('CREATE_CUSTOMER_SERVICE_REQUEST', 'portal', $request, null, $request->toArray());

            return $request;
        });
    }
}
