@extends('layouts.app')

@section('title', 'Manajemen Website')
@section('page_title', 'Manajemen Website')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="card-box p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Website Terisolasi</h2>
            <p class="text-xs text-slate-500">Setiap website memiliki Linux user khusus, document root terisolasi, PHP-FPM pool, & SSL otomatis.</p>
        </div>

        <a href="{{ route('websites.create') }}" 
           class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition-all shadow-xs flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ Tambah Website Baru</span>
        </a>
    </div>

    <!-- Website Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 border-collapse">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-600 uppercase font-mono tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Domain Website</th>
                        @if(auth()->user()->isAdmin())
                            <th class="px-5 py-3.5">Pemilik (Client)</th>
                        @endif
                        <th class="px-5 py-3.5">Linux User</th>
                        <th class="px-5 py-3.5">Document Root</th>
                        <th class="px-5 py-3.5">PHP Version</th>
                        <th class="px-5 py-3.5">SSL</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($websites as $website)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('websites.show', $website) }}" class="font-bold text-slate-900 hover:text-brand-600 transition-colors">
                                    {{ $website->domain_name }}
                                </a>
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="px-5 py-4 text-slate-700 font-sans font-semibold">
                                    {{ $website->user ? $website->user->name : '-' }}
                                </td>
                            @endif
                            <td class="px-5 py-4 text-slate-600">
                                {{ $website->system_user }}
                            </td>
                            <td class="px-5 py-4 text-slate-500 text-[11px]">
                                {{ $website->document_root }}
                            </td>
                            <td class="px-5 py-4 text-slate-800">
                                <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-[11px]">PHP {{ $website->php_version }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $hasSsl = $website->sslCertificates && $website->sslCertificates->contains('status', 'active');
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $hasSsl ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    {{ $hasSsl ? 'HTTPS Active' : 'HTTP' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase {{ $website->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $website->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right space-x-1.5">
                                <a href="{{ route('websites.show', $website) }}" class="px-2.5 py-1 rounded bg-brand-50 hover:bg-brand-100 text-brand-700 font-semibold text-[11px] border border-brand-200 inline-block">
                                    Kelola
                                </a>

                                <form method="POST" action="{{ route('websites.toggle-suspend', $website) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] border border-slate-200">
                                        {{ $website->status === 'active' ? 'Suspend' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 8 : 7 }}" class="px-5 py-8 text-center text-slate-400 font-mono">
                                Belum ada website yang terdaftar. Klik "+ Tambah Website Baru" untuk membuat website pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
