@extends('layouts.modern')

@section('title', 'View Submission | BRACU HUB')

@section('extra_css')
<style>
    .form-card { max-width: 800px; margin: 0 auto; padding: 3rem; }
    .content-box { 
        background: rgba(0, 0, 0, 0.2); 
        border: 1px solid var(--glass-border); 
        border-radius: 12px; 
        padding: 1.5rem; 
        color: white; 
        min-height: 150px;
        white-space: pre-wrap; 
        font-family: inherit;
        line-height: 1.6;
    }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Mission <span class="neon-text">Locked</span></h1>
    <p class="page-subtitle">{{ $submission->assignment->section->course->title }} • {{ $submission->assignment->title }}</p>
</div>

<div class="glass-panel form-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--glass-border);">
        <div>
            <h3 style="color: var(--primary-neon); margin-bottom: 0.25rem;">Submitted Under Your Signature</h3>
            <div style="font-size: 0.85rem; color: var(--text-dim);">
                Timestamp: {{ $submission->created_at->format('M d, Y h:i A') }}
                @if($submission->created_at > $submission->assignment->due_date)
                    <span style="color: #f97316; margin-left: 1rem; font-weight: bold;">(LATE)</span>
                @endif
            </div>
        </div>
        <a href="{{ route('student.tracker') }}" style="padding: 0.5rem 1rem; border-radius: 6px; background: rgba(255,255,255,0.1); color: white; text-decoration: none; font-size: 0.85rem;">Back to Tracker</a>
    </div>

    @if($submission->file_path)
    <div style="margin-bottom: 2rem;">
        <h4 style="color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.85rem; margin-bottom: 0.5rem;">Attached File</h4>
        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(34, 211, 238, 0.05); border: 1px solid rgba(34, 211, 238, 0.2); border-radius: 8px;">
            <span style="font-size: 1.5rem;">📎</span>
            <span style="flex-grow: 1; font-family: monospace;">{{ basename($submission->file_path) }}</span>
            <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" style="padding: 0.4rem 1rem; background: var(--primary-neon); color: black; font-weight: bold; font-size: 0.75rem; border-radius: 4px; text-decoration: none;">DOWNLOAD / VIEW</a>
        </div>
    </div>
    @endif

    @if($submission->content)
    <div>
        <h4 style="color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.85rem; margin-bottom: 0.5rem;">Text Response</h4>
        <div class="content-box">{{ $submission->content }}</div>
    </div>
    @endif
</div>
@endsection
