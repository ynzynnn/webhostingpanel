@extends('layouts.app')

@section('title', 'Manajemen Domain & Mapping')
@section('page_title', 'Manajemen Domain & Mapping')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Domain & Mapping Website</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola domain utama, domain alias (pointer), subdomain, dan pengecekan A-Record DNS.</p>
        </div>

        <button @click="showModal = true" class="py-2.5 px-4 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Domain Alias / Subdomain</span>
        </button>
    </div>

    <!-- Domain List Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider font-mono">
                        <th class="py-3 px-4">Nama Domain / Subdomain</th>
                        <th class="py-3 px-4">Target Website</th>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4">Status DNS</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($domains as $d)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                <a href="http://{{ $d->domain }}" target="_blank" class="hover:text-brand-600 flex items-center space-x-1">
                                    <span>{{ $d->domain }}</span>
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                @if($d->website)
                                    <a href="{{ route('websites.show', $d->website) }}" class="font-bold text-brand-600 hover:underline">
                                        {{ $d->website->domain_name }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($d->type === 'primary')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-50 text-brand-700 border border-brand-200">Domain Utama</span>
                                @elseif($d->type === 'alias')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">Domain Alias</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-sky-50 text-sky-700 border border-sky-200">Subdomain</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($d->dns_status === 'valid')
                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span>✓ A-Record Valid</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span>⚠️ Belum Pointing</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Check DNS Button -->
                                    <form method="POST" action="{{ route('domains.check-dns', $d) }}">
                                        @csrf
                                        <button type="submit" title="Cek DNS A-Record" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[11px] border border-slate-200 transition-all">
                                            Cek DNS
                                        </button>
                                    </form>

                                    <!-- Delete Alias Button -->
                                    @if($d->type !== 'primary')
                                        <form method="POST" action="{{ route('domains.destroy', $d) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus domain alias {{ $d->domain }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Alias" class="px-2.5 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 font-mono text-[11px] border border-rose-200 transition-all">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 font-mono text-xs">
                                Belum ada domain alias atau subdomain terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Tambah Domain Alias -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="showModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 font-mono">Tambah Domain Alias / Subdomain</h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('domains.store') }}" class="space-y-4 font-mono text-xs">
                    @csrf

                    <!-- Target Website Selector -->
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Website Target (Mapping)</label>
                        <select name="website_id" required class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600">
                            <option value="" disabled selected>-- Pilih Website Target --</option>
                            @foreach($websites as $w)
                                <option value="{{ $w->id }}">{{ $w->domain_name }} ({{ $w->system_user }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Domain Name Input -->
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Nama Domain Alias / Subdomain</label>
                        <input type="text" name="domain" required placeholder="contoh: aliasdomain.com atau sub.website.com" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600">
                    </div>

                    <!-- Type Selector -->
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Tipe Domain</label>
                        <select name="type" required class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600">
                            <option value="alias" selected>Domain Alias (Pointer ke Website)</option>
                            <option value="subdomain">Subdomain</option>
                        </select>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold transition-all">Simpan & Sync Nginx</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
