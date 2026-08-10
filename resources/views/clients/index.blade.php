@extends('layouts.app')

@section('title', 'Manajemen Client')
@section('page_title', 'Manajemen Client Hosting')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="card-box p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Akun Client</h2>
            <p class="text-xs text-slate-500">Administrator dapat mengelola resource & batasan quota client.</p>
        </div>

        <button onclick="alert('Form pendaftaran client baru disiapkan.')" 
                class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition-all shadow-xs flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Client Baru</span>
        </button>
    </div>

    <!-- Client Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100/70 text-[11px] font-bold text-slate-500 uppercase font-mono tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Nama Client</th>
                        <th class="px-5 py-3.5">Email</th>
                        <th class="px-5 py-3.5">Quota Disk</th>
                        <th class="px-5 py-3.5">Terpakai</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($clients as $client)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-900 font-sans">
                                {{ $client->name }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ $client->email }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600">
                                {{ $client->disk_quota_mb }} MB
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-600">
                                {{ $client->disk_used_mb }} MB
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase {{ $client->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $client->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right space-x-2">
                                <button class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] border border-slate-200">
                                    Edit Quota
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400 font-mono">
                                Belum ada akun client terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
