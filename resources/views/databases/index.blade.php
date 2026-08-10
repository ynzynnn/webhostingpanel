@extends('layouts.app')

@section('title', 'Manajemen Database MariaDB & Backup SQL')
@section('page_title', 'Manajemen Database MariaDB & Backup SQL')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, showImportModal: false, activeImportDb: null, dbSuffix: '', dbPass: '', generatePassword() { this.dbPass = Math.random().toString(36).slice(-10) + 'A1!' } }">

    <!-- Header Actions -->
    <div class="card-box p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Database MariaDB</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola database MariaDB terisolasi, backup/restore file SQL dump, dan integrasi web manager.</p>
        </div>

        <div class="flex items-center space-x-2">
            <button @click="showModal = true; generatePassword()" class="py-2.5 px-4 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Database Baru</span>
            </button>
        </div>
    </div>

    <!-- Databases List Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider font-mono">
                        <th class="py-3 px-4">Nama Database</th>
                        <th class="py-3 px-4">User Database</th>
                        <th class="py-3 px-4">Website Target</th>
                        <th class="py-3 px-4">Host & Port</th>
                        @if(auth()->user()->isAdmin())
                            <th class="py-3 px-4">Pemilik (Client)</th>
                        @endif
                        <th class="py-3 px-4 text-right">Aksi & Backup</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($databases as $db)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                <span class="px-2 py-1 rounded bg-slate-100 border border-slate-200 text-slate-900">{{ $db->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">
                                <span class="px-2 py-1 rounded bg-brand-50 border border-brand-200 text-brand-700 font-bold">{{ $db->username }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                @if($db->website)
                                    <a href="{{ route('websites.show', $db->website) }}" class="font-bold text-brand-600 hover:underline">
                                        {{ $db->website->domain_name }}
                                    </a>
                                @else
                                    <span class="text-slate-400">Umum / Tanpa Website</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ $db->host }}:{{ $db->port }}
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    {{ $db->user ? $db->user->name : '-' }}
                                </td>
                            @endif
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end space-x-1.5 font-mono">
                                    <!-- Export SQL Button -->
                                    <a href="{{ route('databases.export', $db) }}" title="Download Backup File .SQL"
                                       class="px-2 py-1 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[11px] border border-emerald-200 transition-all font-semibold flex items-center space-x-1">
                                        <span>📥 Export</span>
                                    </a>

                                    <!-- Import SQL Button -->
                                    <button @click="showImportModal = true; activeImportDb = { id: {{ $db->id }}, name: '{{ $db->name }}' }" title="Import File .SQL ke Database"
                                            class="px-2 py-1 rounded bg-amber-50 hover:bg-amber-100 text-amber-800 text-[11px] border border-amber-200 transition-all font-semibold flex items-center space-x-1">
                                        <span>📤 Import</span>
                                    </button>

                                    <!-- Delete DB Form -->
                                    <form method="POST" action="{{ route('databases.destroy', $db) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus database {{ $db->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] border border-rose-200 transition-all font-semibold">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="py-8 text-center text-slate-500 font-mono text-xs">
                                Belum ada database MariaDB terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Buat Database Baru -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="showModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6 space-y-5 font-mono text-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Buat Database MariaDB Baru</h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('databases.store') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Website Target (Opsional)</label>
                        <select name="website_id" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600">
                            <option value="" selected>-- Tanpa Binding Website --</option>
                            @foreach($websites as $w)
                                <option value="{{ $w->id }}">{{ $w->domain_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Akhiran Nama Database</label>
                        <input type="text" name="db_name" x-model="dbSuffix" required placeholder="contoh: wpdb atau main" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600">
                        <p class="text-[10px] text-slate-400">Nama DB & User akhir: <span class="font-bold text-brand-600" x-text="'{{ Str::slug(explode('@', auth()->user()->email)[0]) }}_' + dbSuffix"></span></p>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Password User Database</label>
                            <button type="button" @click="generatePassword()" class="text-[10px] font-bold text-brand-600 hover:underline">🎲 Acak Password</button>
                        </div>
                        <input type="text" name="password" x-model="dbPass" required class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                    </div>

                    <div class="pt-3 flex justify-end space-x-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold transition-all">Buat Database</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Form Import Database SQL -->
    <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="showImportModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 space-y-5 font-mono text-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Import SQL ke: <span class="text-brand-600" x-text="activeImportDb ? activeImportDb.name : ''"></span></h3>
                    <button @click="showImportModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="'/databases/' + (activeImportDb ? activeImportDb.id : '') + '/import'" enctype="multipart/form-data" class="space-y-4 font-sans">
                    @csrf

                    <div class="space-y-1 font-mono">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Pilih File Backup (.SQL atau .SQL.GZ)</label>
                        <input type="file" name="sql_file" accept=".sql,.gz,.txt" required class="w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-slate-900 text-xs font-mono">
                        <p class="text-[10px] text-slate-400 mt-1">Maksimal ukuran file: 50MB.</p>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2 font-mono">
                        <button type="button" @click="showImportModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold transition-all">Impor SQL Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
