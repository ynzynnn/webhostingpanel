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
    <div class="card-box p-6 sm:p-8 space-y-6" x-data="{ domain: '', submitting: false, step: 1, autoSsl: true }">
        <div>
            <h2 class="text-base font-bold text-slate-900 tracking-tight">Form Provisi Website Baru</h2>
            <p class="text-xs text-slate-500 mt-1">Sistem akan secara otomatis membuat Linux User khusus, document root terisolasi, PHP-FPM pool, & Nginx VirtualHost.</p>
        </div>

        <form method="POST" action="{{ route('websites.store') }}" class="space-y-5" @submit="submitting = true; setInterval(() => { if(step < 5) step++ }, 800)">
            @csrf

            <!-- Domain Name Input -->
            <div class="space-y-1">
                <label for="domain_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider font-mono">Nama Domain Website / Subdomain</label>
                <input type="text" id="domain_name" name="domain_name" x-model="domain" required autofocus :readonly="submitting"
                       placeholder="contoh: tokosaya.com atau sub.domain.com"
                       class="w-full px-3.5 py-2.5 rounded-lg bg-white border border-slate-300 text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-brand-600 focus:ring-1 focus:ring-brand-600 transition-all font-mono">
                <p class="text-[11px] text-slate-400">Masukkan nama domain utama atau subdomain tanpa http:// atau www.</p>
            </div>

            <!-- Auto Generated Info Preview Box -->
            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs space-y-2 font-mono" x-show="domain.length > 0">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Preview Konfigurasi Otomatis:</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-700">
                    <div>Linux User: <span class="font-bold text-brand-600" x-text="'site_' + domain.replace(/[^a-zA-Z0-9]/g, '_').substring(0,12)"></span></div>
                    <div>Document Root: <span class="font-bold text-slate-900" x-text="'/var/www/vhosts/site_' + domain.replace(/[^a-zA-Z0-9]/g, '_').substring(0,12) + '/public_html'"></span></div>
                    <div>Logs Dir: <span class="font-bold text-slate-900" x-text="'/var/www/vhosts/site_' + domain.replace(/[^a-zA-Z0-9]/g, '_').substring(0,12) + '/logs'"></span></div>
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
                <input type="checkbox" id="auto_ssl" name="auto_ssl" value="1" x-model="autoSsl" class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                <div class="text-xs">
                    <label for="auto_ssl" class="font-bold text-emerald-900 cursor-pointer">Aktifkan Auto SSL (Let's Encrypt)</label>
                    <p class="text-emerald-700 text-[11px] mt-0.5">Sistem akan secara otomatis menerbitkan dan memasang sertifikat SSL jika DNS domain sudah terpointing ke IP VPS.</p>
                </div>
            </div>

            <!-- Realtime Live Progress Box -->
            <div x-show="submitting" class="p-4 rounded-xl bg-slate-900 text-white space-y-3 font-mono text-xs border border-slate-800 shadow-md animate-pulse">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <div class="font-bold text-brand-400 flex items-center space-x-2">
                        <svg class="w-4 h-4 animate-spin text-brand-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Live Pipeline Progress:</span>
                    </div>
                    <span class="text-[10px] text-slate-400" x-text="'Langkah ' + step + '/5'"></span>
                </div>

                <div class="space-y-2 text-[11px]">
                    <div class="flex items-center space-x-2" :class="step >= 1 ? 'text-emerald-400 font-bold' : 'text-slate-500'">
                        <span x-text="step > 1 ? '✓' : '🔄'"></span>
                        <span>[1/5] Memvalidasi domain & mengisolasi Linux User...</span>
                    </div>
                    <div class="flex items-center space-x-2" :class="step >= 2 ? 'text-emerald-400 font-bold' : 'text-slate-500'">
                        <span x-text="step > 2 ? '✓' : (step == 2 ? '🔄' : '⏳')"></span>
                        <span>[2/5] Membuat direktori /var/www/vhosts & public_html...</span>
                    </div>
                    <div class="flex items-center space-x-2" :class="step >= 3 ? 'text-emerald-400 font-bold' : 'text-slate-500'">
                        <span x-text="step > 3 ? '✓' : (step == 3 ? '🔄' : '⏳')"></span>
                        <span>[3/5] Membangun PHP 8.3 FPM Pool & Nginx Config...</span>
                    </div>
                    <div class="flex items-center space-x-2" :class="step >= 4 ? 'text-emerald-400 font-bold' : 'text-slate-500'">
                        <span x-text="step > 4 ? '✓' : (step == 4 ? '🔄' : '⏳')"></span>
                        <span>[4/5] Menguji sintaks Nginx (sudo nginx -t) & reload...</span>
                    </div>
                    <div class="flex items-center space-x-2" :class="step >= 5 ? 'text-emerald-400 font-bold' : 'text-slate-500'">
                        <span x-text="step == 5 ? '🔄' : '⏳'"></span>
                        <span>[5/5] Pengecekan A-Record DNS & Menerbitkan Auto SSL...</span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" :disabled="submitting"
                        class="w-full py-3 px-4 rounded-lg text-white font-bold text-xs shadow-xs transition-all flex items-center justify-center space-x-2"
                        :class="submitting ? 'bg-slate-400 cursor-not-allowed' : 'bg-brand-600 hover:bg-brand-700'">
                    <template x-if="!submitting">
                        <span class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Provisi Website Sekarang</span>
                        </span>
                    </template>
                    <template x-if="submitting">
                        <span class="flex items-center space-x-2">
                            <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Sedang Memprovisi Website...</span>
                        </span>
                    </template>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
