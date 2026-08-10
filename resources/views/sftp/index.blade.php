@extends('layouts.app')

@section('title', 'Akses SFTP (Secure Shell File Transfer)')
@section('page_title', 'Akses SFTP (Secure Shell File Transfer)')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, activeWebsite: null, newPassword: '', generatePassword() { this.newPassword = Math.random().toString(36).slice(-10) + 'A1!' } }">

    <!-- Header Actions -->
    <div class="card-box p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Daftar Akun SFTP Website</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola kredensial SFTP terisolasi untuk mentransfer berkas menggunakan FileZilla, WinSCP, atau Cyberduck.</p>
        </div>
    </div>

    <!-- Instruction Card -->
    <div class="card-box p-5 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white space-y-3 font-mono text-xs shadow-md border-slate-800">
        <div class="flex items-center space-x-2 font-bold text-brand-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Panduan Koneksi FTP Client (FileZilla / WinSCP):</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-[11px] text-slate-300">
            <div class="p-2.5 rounded bg-slate-900/80 border border-slate-800">
                <span class="text-slate-500 block text-[10px]">PROTOCOL:</span>
                <span class="font-bold text-white">SFTP (SSH File Transfer Protocol)</span>
            </div>
            <div class="p-2.5 rounded bg-slate-900/80 border border-slate-800">
                <span class="text-slate-500 block text-[10px]">HOST / IP:</span>
                <span class="font-bold text-emerald-400">{{ request()->getHost() }} (Port 22)</span>
            </div>
            <div class="p-2.5 rounded bg-slate-900/80 border border-slate-800">
                <span class="text-slate-500 block text-[10px]">AUTHENTICATION:</span>
                <span class="font-bold text-amber-400">User Linux + Password khusus</span>
            </div>
        </div>
    </div>

    <!-- SFTP Accounts List Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider font-mono">
                        <th class="py-3 px-4">Domain Website</th>
                        <th class="py-3 px-4">User SFTP</th>
                        <th class="py-3 px-4">Host & Port</th>
                        <th class="py-3 px-4">Document Root Target</th>
                        @if(auth()->user()->isAdmin())
                            <th class="py-3 px-4">Pemilik (Client)</th>
                        @endif
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-mono">
                    @forelse($websites as $w)
                        @php
                            $serverHost = request()->getHost();
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <a href="{{ route('websites.show', $w) }}" class="hover:text-brand-600">
                                    {{ $w->domain_name }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-1 rounded bg-brand-50 border border-brand-200 text-brand-700 font-bold">{{ $w->system_user }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                {{ $serverHost }}:22
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 text-[11px]">
                                {{ $w->document_root }}
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="py-3.5 px-4 text-slate-600 font-sans font-semibold">
                                    {{ $w->user ? $w->user->name : '-' }}
                                </td>
                            @endif
                            <td class="py-3.5 px-4 text-right">
                                <button @click="showModal = true; activeWebsite = { id: {{ $w->id }}, domain: '{{ $w->domain_name }}', username: '{{ $w->system_user }}' }; generatePassword()"
                                        class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-[11px] border border-slate-200 transition-all">
                                    🔑 Set / Reset Password
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 6 : 5 }}" class="py-8 text-center text-slate-400 font-mono">
                                Belum ada website terdaftar untuk SFTP.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Reset Password SFTP -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 transition-opacity" @click="showModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 space-y-5 font-mono text-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900">Set Password SFTP: <span class="text-brand-600" x-text="activeWebsite ? activeWebsite.domain : ''"></span></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" :action="'/websites/' + (activeWebsite ? activeWebsite.id : '') + '/sftp/reset-password'" class="space-y-4 font-sans">
                    @csrf

                    <div class="space-y-1 font-mono">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">User SFTP Linux</label>
                        <input type="text" readonly :value="activeWebsite ? activeWebsite.username : ''" class="w-full px-3 py-2 rounded-lg bg-slate-100 border border-slate-300 text-slate-700 text-xs font-bold font-mono">
                    </div>

                    <div class="space-y-1 font-mono">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px]">Password SFTP Baru</label>
                            <button type="button" @click="generatePassword()" class="text-[10px] font-bold text-brand-600 hover:underline">🎲 Acak Password</button>
                        </div>
                        <input type="text" name="password" x-model="newPassword" required class="w-full px-3 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-brand-600 font-mono">
                    </div>

                    <div class="pt-3 flex justify-end space-x-2 font-mono">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition-all">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold transition-all">Simpan Password SFTP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
