<?php

use App\Models\Contract;
use App\Services\ContractBillingService;
use Illuminate\Support\Carbon;

test('payment due date is contract day of next month', function () {
    $contract = Contract::factory()->make([
        'start_date' => '2026-01-08',
    ]);

    $service = new ContractBillingService;

    $dueDate = $service->paymentDueDate($contract, Carbon::parse('2026-06-15'));

    expect($dueDate->format('Y-m-d'))->toBe('2026-07-08');
});

test('payment due date uses last day when contract day exceeds month length', function () {
    $contract = Contract::factory()->make([
        'start_date' => '2026-01-31',
    ]);

    $service = new ContractBillingService;

    $dueDate = $service->paymentDueDate($contract, Carbon::parse('2026-01-31'));

    expect($dueDate->format('Y-m-d'))->toBe('2026-02-28');
});

test('payment due date for renter returns null without contract', function () {
    $service = new ContractBillingService;

    expect($service->paymentDueDateForRenter(999, Carbon::parse('2026-06-01')))->toBeNull();
});
