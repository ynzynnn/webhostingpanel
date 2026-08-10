@extends('layouts.app')

@section('title', 'Sertifikat SSL')
@section('page_title', 'Sertifikat SSL / Let\'s Encrypt')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="card-box p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Sertifikat SSL (Certbot / Let's Encrypt)</h2>
            <p class="text-xs text-slate-500">Penerbitan otomatis sertifikat SSL setelah pencocokan DNS valid.</p>
        </div>

        <button onclick="alert('Fitur diterbitkan otomatis pada Phase 4 SSL Certbot.')" 
                class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition-all shadow-xs flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Terbitkan SSL Baru</span>
        </button>
    </div>

    <!-- SSL Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100/70 text-[11px] font-bold text-slate-500 uppercase font-mono tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Domain</th>
                        <th class="px-5 py-3.5">Penerbit</th>
                        <th class="px-5 py-3.5">Status SSL</th>
                        <th class="px-5 py-3.5">Tanggal Kedaluwarsa</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sslCertificates as $ssl)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-900 font-mono">
                                {{ $ssl->domain }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600">
                                {{ $ssl->issuer }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $ssl->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-500">
                                {{ $ssl->expires_at ? $ssl->expires_at->format('Y-m-d') : '90 Hari Auto-Renew' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] border border-slate-200">
                                    Renew SSL
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 font-mono">
                                Belum ada sertifikat SSL terpasang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
