@extends('layouts.app')

@section('title', 'Tambah Website Baru')
@section('page_title', 'Tambah Website Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ auth()->user()->isAdmin() ? route('admin.websites') : route('client.websites') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 font-mono flex items-center space-x-1">
            <span>&larr; Kembali ke Daftar Website</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="card-box p-6 sm:p-8 space-y-6">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Form Provisi Website Baru</h2>
            <p class="text-xs text-slate-500 mt-1">Sistem akan secara otomatis membuat Linux User khusus, document root terisolasi, PHP-FPM pool, & Nginx VirtualHost.</p>
        </div>

        <form method="POST" action="{{ route('websites.store') }}" class="space-y-5" x-data="{ domain: '' }">
            @csrf

            <!-- Domain Name Input -->
            <div class="space-y-1">
                <label for="domain_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider font-mono">Nama Domain Website</label>
                <input type="text" id="domain_name" name="domain_name" x-model="domain" required autofocus
                       placeholder="contoh: tokosaya.com"
                       class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-slate-300 text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 transition-all font-mono">
                <p class="text-[11px] text-slate-400">Masukkan nama domain utama tanpa http:// atau www.</p>
            </div>

            <!-- Auto Generated Info Preview Box -->
            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs space-y-2 font-mono" x-show="domain.length > 0">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Preview Konfigurasi Otomatis:</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-700">
                    <div>Linux User: <span class="font-bold text-brand-600" x-text="'site_' + domain.split('.')[0].substring(0,10)"></span></div>
                    <div>Document Root: <span class="font-bold text-slate-900" x-text="'/home/site_' + domain.split('.')[0].substring(0,10) + '/public_html'"></span></div>
                    <div>Logs Dir: <span class="font-bold text-slate-900" x-text="'/home/site_' + domain.split('.')[0].substring(0,10) + '/logs'"></span></div>
                    <div>Nginx Config: <span class="font-bold text-slate-900" x-text="'/etc/nginx/sites-available/' + domain + '.conf'"></span></div>
                </div>
            </div>

            <!-- PHP Version Selector -->
            <div class="space-y-1">
                <label for="php_version" class="block text-xs font-bold text-slate-700 uppercase tracking-wider font-mono">Versi Engine PHP</label>
                <select id="php_version" name="php_version" required
                        class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-slate-300 text-slate-900 text-sm focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 transition-all font-mono">
                    <option value="8.3" selected>PHP 8.3 FPM (Rekomendasi & Performa Terbaik)</option>
                    <option value="8.2">PHP 8.2 FPM</option>
                    <option value="8.1">PHP 8.1 FPM</option>
                </select>
            </div>

            <!-- Auto SSL Checkbox -->
            <div class="p-4 rounded-lg bg-emerald-50/60 border border-emerald-200 flex items-start space-x-3">
                <input type="checkbox" id="auto_ssl" name="auto_ssl" value="1" checked class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <div class="text-xs">
                    <label for="auto_ssl" class="font-bold text-emerald-900 cursor-pointer">Aktifkan Auto SSL (Let's Encrypt)</label>
                    <p class="text-emerald-700 text-[11px] mt-0.5">Sistem akan secara otomatis menerbitkan dan memasang sertifikat SSL gratis untuk domain ini.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3 px-4 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Provisi Website Sekarang</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
