@extends('layouts.app')

@section('title', 'File Manager')
@section('page_title', 'File Manager (Isolasi Document Root)')

@section('content')
<div class="space-y-6">

    <!-- Information Card -->
    <div class="card-box p-5 space-y-2">
        <h2 class="text-base font-bold text-slate-900">Penjelajah File Terisolasi</h2>
        <p class="text-xs text-slate-500 leading-relaxed">
            Akses File Manager dibatasi penuh pada directory document root milik client. Jalur sistem sensitif seperti <code class="bg-slate-100 px-1 py-0.5 rounded text-rose-600 font-mono">/etc</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-rose-600 font-mono">/root</code>, atau <code class="bg-slate-100 px-1 py-0.5 rounded text-rose-600 font-mono">/var</code> tidak pernah diizinkan demi keamanan.
        </p>
    </div>

    <!-- Website Document Root Picker -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($websites as $site)
            <div class="card-box card-box-hover p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="font-bold text-slate-900 font-mono">{{ $site->domain_name }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Public HTML
                    </span>
                </div>

                <div class="text-xs text-slate-600 font-mono space-y-1">
                    <p>Root Path: <span class="text-slate-900 font-semibold">{{ $site->document_root }}</span></p>
                    <p>Owner User: <span class="text-slate-900 font-semibold">{{ $site->system_user }}</span></p>
                </div>

                <button onclick="alert('File Manager Explorer aktif pada Phase 6 File Manager.')" 
                        class="w-full py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition-all shadow-xs flex items-center justify-center space-x-2 mt-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span>Buka File Manager</span>
                </button>
            </div>
        @empty
            <div class="card-box p-8 text-center text-slate-400 text-xs font-mono col-span-2">
                Belum ada website yang terkonfigurasi. Tambahkan website terlebih dahulu untuk mengakses file manager.
            </div>
        @endforelse
    </div>

</div>
@endsection
