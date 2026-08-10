@extends('layouts.app')

@section('title', 'Pengaturan Akun')
@section('page_title', 'Profil Akun & Keamanan')

@section('content')
<div class="max-w-4xl space-y-6">

    <!-- Profile Overview Card -->
    <div class="card-box p-6 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider font-mono border-b border-slate-100 pb-3">Informasi Akun</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
            <div>
                <span class="text-slate-500 uppercase tracking-wider block">Nama Lengkap</span>
                <span class="text-slate-900 font-bold font-sans text-sm">{{ $user->name }}</span>
            </div>
            <div>
                <span class="text-slate-500 uppercase tracking-wider block">Alamat Email</span>
                <span class="text-slate-800 font-bold text-sm">{{ $user->email }}</span>
            </div>
            <div>
                <span class="text-slate-500 uppercase tracking-wider block">Role Akun</span>
                <span class="inline-block px-2.5 py-1 rounded text-[10px] font-bold uppercase bg-brand-50 text-brand-700 border border-brand-200 mt-1">
                    {{ $user->role }}
                </span>
            </div>
            <div>
                <span class="text-slate-500 uppercase tracking-wider block">Status Akun</span>
                <span class="inline-block px-2.5 py-1 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 mt-1">
                    {{ $user->status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Security & Password Form -->
    <div class="card-box p-6 space-y-6">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Ubah Password</h3>
            <p class="text-xs text-slate-500 mt-1">Perbarui password akses akun Anda.</p>
        </div>

        <form method="POST" action="{{ route('account.password') }}" class="space-y-4 max-w-md">
            @csrf

            <div class="space-y-1">
                <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider font-mono">Password Saat Ini</label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-sm focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 transition-all font-mono">
            </div>

            <div class="space-y-1">
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider font-mono">Password Baru</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-sm focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 transition-all font-mono">
            </div>

            <div class="space-y-1">
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider font-mono">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-slate-900 text-sm focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 transition-all font-mono">
            </div>

            <button type="submit" 
                    class="px-4 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all">
                Simpan Password Baru
            </button>
        </form>
    </div>

</div>
@endsection
