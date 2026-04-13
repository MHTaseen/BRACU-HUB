@extends('layouts.modern')

@section('title', 'Submit Assignment | BRACU HUB')

@section('extra_css')
<style>
    .form-card { max-width: 800px; margin: 0 auto; padding: 3rem; }
    .form-group { margin-bottom: 2rem; }
    label { display: block; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-dim); font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase; }
    textarea { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 12px; padding: 1rem; color: white; transition: all 0.3s; min-height: 300px; font-family: inherit; }
    textarea:focus { outline: none; border-color: var(--primary-neon); box-shadow: 0 0 10px rgba(34, 211, 238, 0.2); }
    .btn-submit { background: var(--primary-neon); color: black; border: none; padding: 1rem; border-radius: 12px; width: 100%; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 1.1rem; }
    .btn-submit:hover { box-shadow: 0 0 15px rgba(34, 211, 238, 0.4); transform: translateY(-2px); }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Submit <span class="neon-text">Task</span></h1>
    <p class="page-subtitle">{{ $assignment->section->course->title }} • {{ $assignment->title }}</p>
</div>

<div class="glass-panel form-card">
    @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--glass-border);">
        <h3 style="color: var(--primary-neon); margin-bottom: 0.5rem;">Instructions</h3>
        <p style="color: var(--text-dim); line-height: 1.6;">{{ $assignment->description }}</p>
        <div style="margin-top: 1rem; display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-dim);">
            <span><strong>Weight:</strong> {{ $assignment->weight }}%</span>
            <span><strong>Due:</strong> <span style="color: {{ $assignment->due_date < now() ? '#ef4444' : 'var(--text-main)' }}">{{ $assignment->due_date->format('M d, Y h:i A') }}</span></span>
        </div>
    </div>

    <form action="{{ route('submissions.store', $assignment->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label>Attach File (Optional)</label>
            <input type="file" name="submission_file" style="width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 12px; padding: 1rem; color: white; margin-bottom: 1rem;">
        </div>

        <div class="form-group">
            <label>Submission Content (Optional if file attached)</label>
            <p style="font-size: 0.8rem; color: var(--text-dim); margin-bottom: 1rem;">
                Type your response here or paste a link to your external document. Once submitted, your timestamp will be officially recorded.
            </p>
            <textarea name="content" placeholder="Enter your work or paste a link..."></textarea>
        </div>

        <button type="submit" class="btn-submit">CONFIRM SUBMISSION</button>
    </form>
</div>
@endsection
