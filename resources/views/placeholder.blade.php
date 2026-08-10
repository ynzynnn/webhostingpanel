@extends('layouts.app')

@section('title', $title ?? 'Modul Fitur')
@section('page_title', $title ?? 'Modul Fitur')

@section('content')
<div class="card-box p-8 text-center space-y-4 max-w-2xl mx-auto my-8">
    <div class="w-14 h-14 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 mx-auto flex items-center justify-center">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
    </div>
    
    <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">{{ $title }}</h2>
    
    <p class="text-xs text-slate-500 leading-relaxed max-w-md mx-auto">
        Modul <span class="text-brand-600 font-mono font-bold">{{ $title }}</span> disiapkan untuk dikembangkan pada fase selanjutnya.
    </p>

    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-[11px] font-mono text-slate-600">
        <span class="w-1.5 h-1.5 rounded-full bg-brand-600 animate-pulse"></span>
        <span>Modul Ready untuk Phase Selanjutnya</span>
    </div>
</div>
@endsection
