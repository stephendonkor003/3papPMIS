<?php

namespace App\Services;

use App\Jobs\NotifyAdminsOfVendorPurchaseRequest;
use App\Models\VendorPurchaseRequest;
use Illuminate\Support\Facades\Log;
use Throwable;

class VendorPurchaseRequestAdminNotificationService
{
    public function notify(VendorPurchaseRequest $purchaseRequest): void
    {
        try {
            NotifyAdminsOfVendorPurchaseRequest::dispatch($purchaseRequest->id);
            return;
        } catch (Throwable $exception) {
            Log::warning('Vendor purchase request admin notification queue dispatch failed; falling back to immediate send.', [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            NotifyAdminsOfVendorPurchaseRequest::dispatchSync($purchaseRequest->id);
        } catch (Throwable $exception) {
            Log::warning('Vendor purchase request admin notification fallback failed.', [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
