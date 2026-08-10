@extends('layouts.app')

@section('title', 'Manajemen Database')
@section('page_title', 'Manajemen Database MariaDB')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="card-box p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-900">Database MariaDB Client</h2>
            <p class="text-xs text-slate-500">Database & user dibuat dengan prefix khusus untuk mencegah collision.</p>
        </div>

        <button onclick="alert('Fitur pembuatan database akan aktif pada Phase 5 Database Management.')" 
                class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs transition-all shadow-xs flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Buat Database</span>
        </button>
    </div>

    <!-- Database Table Card -->
    <div class="card-box overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-100/70 text-[11px] font-bold text-slate-500 uppercase font-mono tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Nama Database</th>
                        <th class="px-5 py-3.5">Username Database</th>
                        <th class="px-5 py-3.5">Host</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($databases as $db)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-900 font-mono">
                                {{ $db->db_name }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-700">
                                {{ $db->db_user }}
                            </td>
                            <td class="px-5 py-4 font-mono text-slate-500">
                                127.0.0.1 / localhost
                            </td>
                            <td class="px-5 py-4 text-right space-x-2">
                                <button class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] border border-slate-200">
                                    Ubah Password
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-400 font-mono">
                                Belum ada database yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
