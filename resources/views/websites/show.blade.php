@extends('layouts.app')

@section('title', 'Detail Website: ' . $website->domain_name)
@section('page_title', 'Kelola Website: ' . $website->domain_name)

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ auth()->user()->isAdmin() ? route('admin.websites') : route('client.websites') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 font-mono flex items-center space-x-1">
            <span>&larr; Kembali ke Daftar Website</span>
        </a>
    </div>

    <!-- Header Card -->
    <div class="card-box p-6 bg-gradient-to-r from-white via-slate-50 to-brand-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight font-mono">{{ $website->domain_name }}</h2>
                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase {{ $website->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                    {{ $website->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 font-mono">System User: <span class="font-bold text-slate-800">{{ $website->system_user }}</span> &bull; PHP {{ $website->php_version }} FPM</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-3">
            <form method="POST" action="{{ route('websites.toggle-suspend', $website) }}">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-lg font-semibold text-xs transition-all border shadow-xs {{ $website->status === 'active' ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                    {{ $website->status === 'active' ? 'Suspend Website' : 'Aktifkan Kembali' }}
                </button>
            </form>

            <form method="POST" action="{{ route('websites.destroy', $website) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus website {{ $website->domain_name }} beserta seluruh filenya?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs border border-rose-200 transition-all shadow-xs">
                    Hapus Website
                </button>
            </form>
        </div>
    </div>

    <!-- Grid Overview & SSL -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Details & Nginx Config Inspector -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Directory Info Card -->
            <div class="card-box p-5 space-y-4">
                <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono border-b border-slate-100 pb-3">Struktur Folder & User</h3>
                
                <div class="space-y-3 text-xs font-mono">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2.5 rounded-lg bg-slate-50 border border-slate-200 gap-1">
                        <span class="text-slate-500">Document Root:</span>
                        <span class="font-bold text-slate-900">{{ $website->document_root }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2.5 rounded-lg bg-slate-50 border border-slate-200 gap-1">
                        <span class="text-slate-500">Directory Logs:</span>
                        <span class="font-bold text-slate-900">{{ dirname($website->document_root) }}/logs</span>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2.5 rounded-lg bg-slate-50 border border-slate-200 gap-1">
                        <span class="text-slate-500">PHP FPM Socket:</span>
                        <span class="font-bold text-brand-600">/run/php/php{{ $website->php_version }}-fpm-{{ $website->system_user }}.sock</span>
                    </div>
                </div>
            </div>

            <!-- Nginx VirtualHost Configuration Viewer -->
            <div class="card-box p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono">Konfigurasi Nginx VirtualHost</h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        nginx.conf
                    </span>
                </div>
                <pre class="p-4 rounded-lg bg-slate-900 text-slate-100 font-mono text-[11px] overflow-x-auto leading-relaxed border border-slate-800">{{ $nginxConfig }}</pre>
            </div>
        </div>

        <!-- Right Side: SSL Card & Quick Actions -->
        <div class="space-y-6">
            <!-- SSL Card with Live Progress -->
            <div class="card-box p-5 space-y-4" x-data="{ issuingSsl: false }">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono">Status SSL / Let's Encrypt</h3>
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>

                @php
                    $ssl = $website->sslCertificates()->latest()->first();
                @endphp

                @if($ssl && $ssl->status === 'active')
                    <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs space-y-1">
                        <div class="font-bold flex items-center space-x-1">
                            <span>Sertifikat SSL Aktif</span>
                        </div>
                        <p class="text-[11px] text-emerald-700">Penerbit: {{ $ssl->issuer }}</p>
                        <p class="text-[11px] text-emerald-700 font-mono">Kedaluwarsa: {{ $ssl->expires_at ? $ssl->expires_at->format('Y-m-d') : '90 Hari Auto-Renew' }}</p>
                    </div>
                @else
                    <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs space-y-1">
                        <div class="font-bold">SSL Belum Terpasang</div>
                        <p class="text-[11px] text-amber-700">Pastikan A Record DNS domain <span class="font-bold">{{ $website->domain_name }}</span> sudah mengarah ke IP server ini sebelum menerbitkan SSL.</p>
                    </div>

                    <!-- Live Progress SSL Card -->
                    <div x-show="issuingSsl" class="p-3.5 rounded-lg bg-slate-900 text-white space-y-2 font-mono text-xs border border-slate-800 animate-pulse">
                        <div class="font-bold text-brand-400 flex items-center space-x-2">
                            <svg class="w-4 h-4 animate-spin text-brand-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Proses Menerbitkan SSL...</span>
                        </div>
                        <p class="text-[11px] text-slate-300">1. Memvalidasi A-Record DNS {{ $website->domain_name }}...</p>
                        <p class="text-[11px] text-slate-300">2. Meminta sertifikat ke Let's Encrypt (Certbot)...</p>
                        <p class="text-[11px] text-emerald-400">3. Menyetel izin sertifikat & me-reload Nginx...</p>
                    </div>

                    <form method="POST" action="{{ route('websites.issue-ssl', $website) }}" @submit="issuingSsl = true">
                        @csrf
                        <button type="submit" :disabled="issuingSsl"
                                class="w-full py-2.5 px-3 rounded-lg text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center space-x-2"
                                :class="issuingSsl ? 'bg-slate-400 cursor-not-allowed' : 'bg-brand-600 hover:bg-brand-700'">
                            <template x-if="!issuingSsl">
                                <span class="flex items-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span>Terbitkan Sertifikat SSL 1-Click</span>
                                </span>
                            </template>
                            <template x-if="issuingSsl">
                                <span class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Memproses SSL...</span>
                                </span>
                            </template>
                        </button>
                    </form>
                @endif
            </div>

            <!-- File Manager Link Card -->
            <div class="card-box p-5 space-y-3">
                <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono">Akses Berkas & Perbaikan 502</h3>
                <p class="text-xs text-slate-500">Kelola file atau perbaiki izin file (chown/chmod 755/644) jika terjadi 502 Bad Gateway setelah upload ZIP.</p>
                <div class="space-y-2">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.files') : route('client.files') }}" class="w-full py-2.5 px-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-200 transition-all flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002-2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span>Buka File Manager</span>
                    </a>

                    <form method="POST" action="{{ route('websites.fix-permissions', $website) }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-900 font-bold text-[11px] border border-amber-200 transition-all flex items-center justify-center space-x-1.5 font-mono">
                            <span>🛠️ Fix Izin Berkas & Reset 502 Error</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
