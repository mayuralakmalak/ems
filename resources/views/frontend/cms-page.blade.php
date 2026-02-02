@extends('layouts.frontend')

@section('title', $page->title . ' - ' . config('app.name', 'EMS'))

@push('styles')
<style>
    .cms-page { max-width: 900px; margin: 0 auto; padding: 50px 20px 80px; }
    .cms-page h1 { font-size: 2rem; font-weight: 700; color: #1a1a40; margin-bottom: 8px; }
    .cms-page .subtitle { color: #6c757d; margin-bottom: 40px; }
    .cms-page h2 { font-size: 1.25rem; font-weight: 700; color: #333; margin-top: 32px; margin-bottom: 12px; }
    .cms-page p, .cms-page li { color: #444; line-height: 1.7; margin-bottom: 12px; }
    .cms-page ul { padding-left: 24px; margin-bottom: 16px; }
    .cms-page ul li { margin-bottom: 8px; }
    .cms-page a { color: var(--primary-purple, #8C52FF); text-decoration: none; }
    .cms-page a:hover { text-decoration: underline; }
    .cms-page table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 0.95rem; }
    .cms-page table th, .cms-page table td { border: 1px solid #ddd; padding: 12px 16px; text-align: left; }
    .cms-page table th { background: #f5f5f5; font-weight: 600; color: #333; }
    .cms-page table tr:nth-child(even) { background: #fafafa; }
</style>
@endpush

@section('content')
<div class="cms-page">
    <h1>{{ $page->title }}</h1>
    <p class="subtitle">Last updated: {{ $page->updated_at->format('F j, Y') }}</p>
    <div class="cms-content">
        {!! $page->content !!}
    </div>
</div>
@endsection
