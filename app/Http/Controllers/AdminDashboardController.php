<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DatabaseModel;
use App\Models\Domain;
use App\Models\User;
use App\Models\Website;
use App\Services\SystemMonitoringService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected SystemMonitoringService $monitoringService
    ) {}

    public function index(): View
    {
        $metrics = $this->monitoringService->getSystemMetrics();

        $stats = [
            'clients_count' => User::where('role', 'client')->count(),
            'websites_count' => Website::count(),
            'domains_count' => Domain::count(),
            'databases_count' => DatabaseModel::count(),
        ];

        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard.admin', compact('metrics', 'stats', 'recentAuditLogs'));
    }

    public function websites(): View
    {
        $websites = Website::with('user')->latest()->paginate(15);
        return view('placeholder', ['title' => 'Websites Management (Admin)', 'item' => $websites]);
    }

    public function clients(): View
    {
        $clients = User::where('role', 'client')->latest()->paginate(15);
        return view('placeholder', ['title' => 'Clients Management', 'item' => $clients]);
    }

    public function auditLogs(): View
    {
        $logs = AuditLog::with('user')->latest()->paginate(20);
        return view('placeholder', ['title' => 'System Audit Logs', 'item' => $logs]);
    }
}
