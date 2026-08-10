@extends('layouts.app')

@section('title', 'Manajemen Domain')
@section('page_title', 'Manajemen Domain')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="card-box p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Custom Domain</h2>
            <p class="text-xs text-slate-500">Mapping domain ke website & validasi DNS otomatis.</p>
        </div>

        <button onclick="alert('Fitur penambahan domain akan aktif pada Phase 3 Domain Management.')" 
                class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition-all shadow-xs flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Domain</span>
        </button>
    </div>

    <!-- Domain Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100/70 text-[11px] font-bold text-slate-500 uppercase font-mono tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Nama Domain</th>
                        <th class="px-5 py-3.5">Tipe Domain</th>
                        <th class="px-5 py-3.5">Status DNS</th>
                        <th class="px-5 py-3.5">Target Website</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($domains as $domain)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-900 font-mono">
                                {{ $domain->domain }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600 capitalize">
                                {{ $domain->type }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $domain->dns_status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ $domain->website ? $domain->website->domain_name : 'Unmapped' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] border border-slate-200">
                                    Cek DNS
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 font-mono">
                                Belum ada domain yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
