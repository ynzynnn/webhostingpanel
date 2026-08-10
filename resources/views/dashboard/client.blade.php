@extends('layouts.app')

@section('title', 'Client Dashboard')
@section('page_title', 'Ringkasan Resource Client')

@section('content')
<div class="space-y-6">

    <!-- Client Welcome Banner -->
    <div class="card-box p-6 bg-gradient-to-r from-white via-slate-50 to-brand-50/30">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-1">
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Selamat Datang, {{ $user->name }} 👋</h2>
                <p class="text-xs text-slate-500">Kelola website, domain custom, database, dan file Anda secara mandiri.</p>
            </div>

            <!-- Disk Quota Storage Meter -->
            <div class="w-full md:w-80 bg-white border border-slate-200 rounded-lg p-4 space-y-2 shadow-xs">
                <div class="flex items-center justify-between text-xs font-mono font-bold">
                    <span class="text-slate-500 uppercase tracking-wider">Kapasitas Disk</span>
                    <span class="text-slate-900">{{ $diskUsedMb }} MB / {{ $diskQuotaMb }} MB</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                    <div class="h-full bg-brand-600 rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $diskPercent)) }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[10px] text-slate-500 font-mono">
                    <span>Terpakai: {{ $diskPercent }}%</span>
                    <span>Sisa: {{ max(0, $diskQuotaMb - $diskUsedMb) }} MB</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Resource Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Websites List -->
        <div class="lg:col-span-2 card-box overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 tracking-tight">Daftar Website</h3>
                    <p class="text-xs text-slate-500">Website terisolasi pengguna Linux khusus</p>
                </div>
                <a href="{{ route('client.websites') }}" class="px-3.5 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition-all shadow-xs">
                    + Tambah Website
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($websites as $website)
                    <div class="p-5 hover:bg-slate-50/60 transition-colors flex items-center justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2.5">
                                <a href="http://{{ $website->domain_name }}" target="_blank" class="font-bold text-slate-900 hover:text-brand-600 transition-colors">
                                    {{ $website->domain_name }}
                                </a>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $website->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $website->status }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 font-mono space-x-3">
                                <span>PHP {{ $website->php_version }} FPM</span>
                                <span>&bull;</span>
                                <span>Root: {{ $website->document_root }}</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <a href="{{ route('client.files') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200 text-xs font-semibold flex items-center space-x-1.5 transition-all">
                                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                <span>File Manager</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs font-mono">
                        Belum ada website yang dibuat. Klik "+ Tambah Website" untuk mulai.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sub-services Overview -->
        <div class="space-y-6">
            <!-- Active Custom Domains -->
            <div class="card-box p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono">Domain ({{ $domains->count() }})</h3>
                    <a href="{{ route('client.domains') }}" class="text-xs text-brand-600 hover:underline font-mono">Kelola</a>
                </div>
                <div class="space-y-2">
                    @forelse($domains as $domain)
                        <div class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-slate-50 border border-slate-200">
                            <span class="font-mono font-bold text-slate-900 truncate">{{ $domain->domain }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $domain->dns_status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 font-mono">Belum ada domain custom.</p>
                    @endforelse
                </div>
            </div>

            <!-- Databases Summary -->
            <div class="card-box p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider font-mono">Database ({{ $databases->count() }})</h3>
                    <a href="{{ route('client.databases') }}" class="text-xs text-brand-600 hover:underline font-mono">Kelola</a>
                </div>
                <div class="space-y-2">
                    @forelse($databases as $db)
                        <div class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-slate-50 border border-slate-200">
                            <span class="font-mono font-bold text-slate-900 truncate">{{ $db->db_name }}</span>
                            <span class="font-mono text-slate-500 text-[11px]">{{ $db->db_user }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 font-mono">Belum ada database dibuat.</p>
                    @endforelse
                </div>
            </div>

            <!-- SSL Card -->
            <div class="card-box p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Sertifikat SSL Aktif</h3>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1 font-mono tracking-tight">{{ $sslCount }}</div>
                </div>
                <a href="{{ route('client.ssl') }}" class="p-3 rounded-lg bg-brand-50 text-brand-600 border border-brand-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
