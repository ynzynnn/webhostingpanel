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
        $websites = Website::with('user')->latest()->get();
        return view('websites.index', compact('websites'));
    }

    public function storeClient(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'disk_quota_mb' => 'required|integer|min:100',
        ]);

        $client = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Hash::make($request->password),
            'role' => 'client',
            'status' => 'active',
            'disk_quota_mb' => $request->disk_quota_mb,
            'disk_used_mb' => 0,
        ]);

        \App\Services\AuditLogger::log('client_created', "Akun client baru {$client->email} (Quota: {$client->disk_quota_mb}MB) berhasil dibuat.", auth()->id());

        return redirect()->back()->with('success', "Akun client {$client->email} berhasil dibuat!");
    }
}
