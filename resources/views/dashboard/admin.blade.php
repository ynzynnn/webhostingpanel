@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Ringkasan Server & Infrastructure')

@section('content')
<div class="space-y-6">

    <!-- Infrastructure Service Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Nginx Web Server -->
        <div class="card-box card-box-hover p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3.5">
                <div class="p-2.5 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono block">Web Server</span>
                    <span class="text-sm font-extrabold text-slate-900">Nginx</span>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-semibold {{ $metrics['services']['nginx']['running'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $metrics['services']['nginx']['running'] ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }} mr-1.5"></span>
                {{ $metrics['services']['nginx']['running'] ? 'Active' : 'Offline' }}
            </span>
        </div>

        <!-- PHP-FPM Engine -->
        <div class="card-box card-box-hover p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3.5">
                <div class="p-2.5 rounded-lg bg-brand-50 text-brand-600 border border-brand-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono block">PHP Engine</span>
                    <span class="text-sm font-extrabold text-slate-900">PHP 8.3 FPM</span>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-semibold {{ $metrics['services']['php_fpm']['running'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $metrics['services']['php_fpm']['running'] ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }} mr-1.5"></span>
                {{ $metrics['services']['php_fpm']['running'] ? 'Active' : 'Offline' }}
            </span>
        </div>

        <!-- MariaDB Engine -->
        <div class="card-box card-box-hover p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3.5">
                <div class="p-2.5 rounded-lg bg-sky-50 text-sky-600 border border-sky-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-mono block">Database</span>
                    <span class="text-sm font-extrabold text-slate-900">MariaDB</span>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-semibold {{ $metrics['services']['mariadb']['running'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $metrics['services']['mariadb']['running'] ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }} mr-1.5"></span>
                {{ $metrics['services']['mariadb']['running'] ? 'Active' : 'Offline' }}
            </span>
        </div>
    </div>

    <!-- System Metrics Gauges -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        
        <!-- CPU Usage Card -->
        <div class="card-box p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono">CPU Usage</span>
                <span class="px-2 py-0.5 rounded-md text-xs font-mono font-extrabold bg-brand-50 text-brand-700 border border-brand-200">
                    {{ $metrics['cpu']['percentage'] }}%
                </span>
            </div>
            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                <div class="h-full bg-brand-600 rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $metrics['cpu']['percentage'])) }}%"></div>
            </div>
            <div class="text-[11px] text-slate-500 font-mono truncate">
                {{ $metrics['cpu']['label'] }}
            </div>
        </div>

        <!-- RAM Usage Card -->
        <div class="card-box p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono">RAM Memory</span>
                <span class="px-2 py-0.5 rounded-md text-xs font-mono font-extrabold bg-sky-50 text-sky-700 border border-sky-200">
                    {{ $metrics['ram']['percentage'] }}%
                </span>
            </div>
            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                <div class="h-full bg-sky-500 rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $metrics['ram']['percentage'])) }}%"></div>
            </div>
            <div class="text-[11px] text-slate-500 font-mono truncate">
                {{ $metrics['ram']['label'] }}
            </div>
        </div>

        <!-- Disk Storage Card -->
        <div class="card-box p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono">Disk Storage</span>
                <span class="px-2 py-0.5 rounded-md text-xs font-mono font-extrabold bg-purple-50 text-purple-700 border border-purple-200">
                    {{ $metrics['disk']['percentage'] }}%
                </span>
            </div>
            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                <div class="h-full bg-purple-600 rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $metrics['disk']['percentage'])) }}%"></div>
            </div>
            <div class="text-[11px] text-slate-500 font-mono truncate">
                {{ $metrics['disk']['label'] }}
            </div>
        </div>

        <!-- Server Uptime Card -->
        <div class="card-box p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono">Uptime Server</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    ONLINE
                </span>
            </div>
            <div class="text-lg font-extrabold font-mono text-slate-900 tracking-tight">
                {{ $metrics['uptime'] }}
            </div>
            <div class="text-[11px] text-slate-400 font-mono">
                Pemeriksaan hemat resource
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Clients -->
        <a href="{{ route('admin.clients') }}" class="card-box card-box-hover p-5 block group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Client Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-brand-50 group-hover:text-brand-600 text-slate-500 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline space-x-2 mt-3">
                <span class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $stats['clients_count'] }}</span>
                <span class="text-[10px] text-emerald-600 font-semibold font-mono">Terdaftar</span>
            </div>
        </a>

        <!-- Total Websites -->
        <a href="{{ route('admin.websites') }}" class="card-box card-box-hover p-5 block group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Total Website</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-brand-50 group-hover:text-brand-600 text-slate-500 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </div>
            </div>
            <div class="flex items-baseline space-x-2 mt-3">
                <span class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $stats['websites_count'] }}</span>
                <span class="text-[10px] text-brand-600 font-semibold font-mono">Terisolasi</span>
            </div>
        </a>

        <!-- Custom Domains -->
        <a href="{{ route('admin.domains') }}" class="card-box card-box-hover p-5 block group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Domain</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-brand-50 group-hover:text-brand-600 text-slate-500 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                </div>
            </div>
            <div class="flex items-baseline space-x-2 mt-3">
                <span class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $stats['domains_count'] }}</span>
                <span class="text-[10px] text-sky-600 font-semibold font-mono">Mapped</span>
            </div>
        </a>

        <!-- MariaDB Databases -->
        <a href="{{ route('admin.databases') }}" class="card-box card-box-hover p-5 block group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Database</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-brand-50 group-hover:text-brand-600 text-slate-500 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s-8-1.79-8-4"/></svg>
                </div>
            </div>
            <div class="flex items-baseline space-x-2 mt-3">
                <span class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $stats['databases_count'] }}</span>
                <span class="text-[10px] text-purple-600 font-semibold font-mono">MariaDB</span>
            </div>
        </a>
    </div>

    <!-- Security Audit Logs Table -->
    <div class="card-box overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-900 tracking-tight">Audit Log Keamanan & Aktivitas</h3>
                <p class="text-xs text-slate-500">Catatan aktivitas pengguna dan riwayat otentikasi sistem</p>
            </div>
            <a href="{{ route('admin.audit-logs') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 font-mono">
                Lihat Log &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100/70 text-[11px] font-bold text-slate-500 uppercase font-mono tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Waktu</th>
                        <th class="px-5 py-3.5">User</th>
                        <th class="px-5 py-3.5">Aksi</th>
                        <th class="px-5 py-3.5">Keterangan</th>
                        <th class="px-5 py-3.5">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAuditLogs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-900">{{ $log->user ? $log->user->name : 'System' }}</span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-800">
                                {{ $log->description }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-slate-500 whitespace-nowrap">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 font-mono">
                                Belum ada log keamanan yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
