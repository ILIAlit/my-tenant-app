<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\DashboardFeedService;
use App\Services\DashboardFinancialChartService;
use App\Services\DashboardMonthlyExpensesService;
use App\Services\DashboardRecentRecordsService;
use App\Services\DashboardRentersWithDebtService;
use App\Services\DashboardStatisticsService;
use App\Services\HousePlanService;
use App\Services\RenterDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        DashboardStatisticsService $statistics,
        HousePlanService $housePlan,
        DashboardMonthlyExpensesService $monthlyExpenses,
        DashboardRecentRecordsService $recentRecords,
        DashboardRentersWithDebtService $rentersWithDebt,
        DashboardFinancialChartService $financialChart,
        DashboardFeedService $feed,
        RenterDashboardService $renterDashboard,
    ): Response {
        $isAdmin = $request->user()?->role === UserRole::ADMIN->value;
        $user = $request->user();
        $expenseMonth = $monthlyExpenses->resolveMonth($request->query('expense_month'));
        $financeMonth = $monthlyExpenses->resolveMonth($request->query('finance_month'));

        return Inertia::render('dashboard', [
            'statistics' => $isAdmin ? $statistics->get() : null,
            'housePlan' => $isAdmin ? $housePlan->get() : null,
            'monthlyExpenses' => $isAdmin
                ? $monthlyExpenses->getForMonth($expenseMonth)
                : null,
            'recentPayments' => $isAdmin ? $recentRecords->recentPayments() : null,
            'recentMeterReadings' => $isAdmin ? $recentRecords->recentMeterReadings() : null,
            'rentersWithDebt' => $isAdmin ? $rentersWithDebt->get() : null,
            'financialChart' => $isAdmin
                ? $financialChart->getForMonth($financeMonth)
                : null,
            'dashboardNotifications' => $isAdmin && $user
                ? $feed->notifications($user)
                : null,
            'dashboardNews' => $isAdmin ? $feed->news() : null,
            'renterDashboard' => $user?->role === UserRole::RENTER->value
                ? $renterDashboard->get($user)
                : null,
        ]);
    }
}
