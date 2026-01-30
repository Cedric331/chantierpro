<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FeatureUsage;
use Illuminate\Support\Carbon;

class UsageTrackingService
{
    public function track(Account $account, string $featureKey): void
    {
        $today = Carbon::now()->toDateString();

        $alreadyTracked = FeatureUsage::query()
            ->where('account_id', $account->id)
            ->where('feature_key', $featureKey)
            ->whereDate('used_at', $today)
            ->exists();

        if ($alreadyTracked) {
            return;
        }

        FeatureUsage::create([
            'account_id' => $account->id,
            'feature_key' => $featureKey,
            'used_at' => Carbon::now(),
        ]);
    }
}



