<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\DatabaseModel;
use App\Models\User;
use App\Models\Website;
use Illuminate\Http\JsonResponse;

class SystemApiController extends ApiController
{
    /**
     * Get real-time SeptaPanel server health & resource statistics.
     */
    public function status(): JsonResponse
    {
        $websitesCount = Website::count();
        $clientsCount = User::where('role', 'client')->count();
        $databasesCount = DatabaseModel::count();

        $cpuLoad = 'N/A';
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $cpuLoad = isset($load[0]) ? round($load[0], 2) : 'N/A';
        }

        $freeDisk = disk_free_space(base_path());
        $totalDisk = disk_total_space(base_path());
        $diskUsedPercent = $totalDisk ? round((($totalDisk - $freeDisk) / $totalDisk) * 100, 1) : 0;

        return $this->success([
            'server_name' => gethostname(),
            'os' => PHP_OS_FAMILY,
            'php_version' => PHP_VERSION,
            'cpu_load_1min' => $cpuLoad,
            'disk_usage' => [
                'total_gb' => round($totalDisk / 1073741824, 2),
                'free_gb' => round($freeDisk / 1073741824, 2),
                'used_percent' => $diskUsedPercent,
            ],
            'statistics' => [
                'total_websites' => $websitesCount,
                'total_clients' => $clientsCount,
                'total_databases' => $databasesCount,
            ],
        ], 'Status server SeptaPanel berhasil diambil.');
    }
}
