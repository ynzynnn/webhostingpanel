<?php

namespace App\Http\Controllers;

use App\Models\DatabaseModel;
use App\Models\Domain;
use App\Models\SslCertificate;
use App\Models\Website;
use Illuminate\View\View;

class ClientDashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $websites = Website::where('user_id', $user->id)->latest()->get();
        $domains = Domain::where('user_id', $user->id)->latest()->get();
        $databases = DatabaseModel::where('user_id', $user->id)->latest()->get();
        $sslCount = SslCertificate::where('user_id', $user->id)->where('status', 'active')->count();

        $diskQuotaMb = $user->disk_quota_mb ?: 5000;
        $diskUsedMb = $user->disk_used_mb ?: 0;
        $diskPercent = min(100, round(($diskUsedMb / $diskQuotaMb) * 100));

        return view('dashboard.client', compact(
            'user',
            'websites',
            'domains',
            'databases',
            'sslCount',
            'diskQuotaMb',
            'diskUsedMb',
            'diskPercent'
        ));
    }
}
