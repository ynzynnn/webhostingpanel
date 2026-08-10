@extends('layouts.app')

@section('title', 'Audit Log Keamanan')
@section('page_title', 'Audit Log Keamanan Sistem')

@section('content')
<div class="space-y-6">

    <!-- Information Card -->
    <div class="card-box p-5 space-y-1">
        <h2 class="text-base font-bold text-slate-900">Jejak Audit Keamanan & System Events</h2>
        <p class="text-xs text-slate-500">Mencatat aktivitas otentikasi, login gagal, perubahan privilege, dan eksekusi administratif.</p>
    </div>

    <!-- Audit Logs Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100/70 text-[11px] font-bold text-slate-500 uppercase font-mono tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Waktu</th>
                        <th class="px-5 py-3.5">User</th>
                        <th class="px-5 py-3.5">Aksi</th>
                        <th class="px-5 py-3.5">Keterangan Aktivitas</th>
                        <th class="px-5 py-3.5">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-mono text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-5 py-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $log->user ? $log->user->name : 'System' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-800">
                                {{ $log->description }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-500 whitespace-nowrap">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 font-mono">
                                Belum ada log keamanan tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
