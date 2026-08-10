@extends('layouts.app')

@section('title', 'Manajemen Client')
@section('page_title', 'Manajemen Client Hosting')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, editModal: false, activeClient: null, clientPass: '', generatePassword() { this.clientPass = Math.random().toString(36).slice(-10) + 'A1!' } }">

    <!-- Top Action Bar -->
    <div class="card-box p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Akun Client</h2>
            <p class="text-xs text-slate-500">Administrator dapat mengelola akun client, batas quota disk, dan batas jumlah website.</p>
        </div>

        <button @click="showModal = true; generatePassword()" 
                class="px-4 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs transition-all shadow-xs flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Client Baru</span>
        </button>
    </div>

    <!-- Client Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 border-collapse">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-600 uppercase font-mono tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Nama Client</th>
                        <th class="px-5 py-3.5">Email</th>
                        <th class="px-5 py-3.5">Quota Storage</th>
                        <th class="px-5 py-3.5">Quota Website</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($clients as $client)
                        @php
                            $webCount = $client->websites()->count();
                            $maxWeb = $client->max_websites ?? 5;
                            $webPercent = min(100, round(($webCount / max(1, $maxWeb)) * 100));
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-900 font-sans">
                                {{ $client->name }}
                            </td>
                            <td class="px-5 py-4 text-slate-700">
                                {{ $client->email }}
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                {{ $client->disk_used_mb }} / {{ $client->disk_quota_mb }} MB
                            </td>
                            <td class="px-5 py-4 text-slate-700">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span>{{ $webCount }} / {{ $maxWeb }} Website</span>
                                        <span class="text-[10px] text-slate-400">({{ $webPercent }}%)</span>
                                    </div>
                                    <div class="w-28 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full {{ $webPercent >= 100 ? 'bg-rose-500' : ($webPercent >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $webPercent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase {{ $client->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $client->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <button @click="editModal = true; activeClient = { id: {{ $client->id }}, name: '{{ $client->name }}', disk_quota_mb: {{ $client->disk_quota_mb }}, max_websites: {{ $maxWeb }} }"
                                        class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] border border-slate-200 transition-all font-mono">
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

    <!-- Modal Form Tambah Client Baru -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="showModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6 space-y-5 font-mono text-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Tambah Akun Client Baru</h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.clients.store') }}" class="space-y-4 font-sans">
                    @csrf

                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] font-mono">Nama Lengkap Client</label>
                        <input type="text" name="name" required placeholder="contoh: Ahmad Subagyo" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600">
                    </div>

                    <div class="space-y-1">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] font-mono">Email Akun Client</label>
                        <input type="email" name="email" required placeholder="client@domain.com" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] font-mono">Password Akun</label>
                            <button type="button" @click="generatePassword()" class="text-[10px] font-bold text-brand-600 hover:underline font-mono">🎲 Acak Password</button>
                        </div>
                        <input type="text" name="password" x-model="clientPass" required class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] font-mono">Storage Quota (MB)</label>
                            <input type="number" name="disk_quota_mb" value="1024" required min="100" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] font-mono">Max Quota Website</label>
                            <input type="number" name="max_websites" value="5" required min="1" max="100" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end space-x-2 font-mono">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold transition-all">Simpan Akun Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Form Edit Quota Client -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="editModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 space-y-5 font-mono text-xs" x-if="activeClient">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Edit Quota Client: <span class="text-brand-600" x-text="activeClient ? activeClient.name : ''"></span></h3>
                    <button @click="editModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="'/admin/clients/' + (activeClient ? activeClient.id : '') + '/quota'" class="space-y-4 font-sans">
                    @csrf
                    @method('PUT')

                    <div class="space-y-1 font-mono">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Storage Disk Quota (MB)</label>
                        <input type="number" name="disk_quota_mb" x-model="activeClient.disk_quota_mb" required min="100" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                    </div>

                    <div class="space-y-1 font-mono">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Max Quota Jumlah Website</label>
                        <input type="number" name="max_websites" x-model="activeClient.max_websites" required min="1" max="100" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                    </div>

                    <div class="pt-3 flex justify-end space-x-2 font-mono">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold transition-all">Update Quota</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
