@extends('layouts.modern')

@section('title', 'Add Course | BRACU HUB')

@section('extra_css')
<style>
    .form-card { max-width: 800px; margin: 0 auto; padding: 3rem; }
    .form-group { margin-bottom: 2rem; }
    label { display: block; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-dim); font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase; }
    input, textarea { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 12px; padding: 1rem; color: white; transition: all 0.3s; }
    input:focus, textarea:focus { outline: none; border-color: var(--faculty-neon); }
    .btn-submit { background: var(--faculty-neon); color: white; border: none; padding: 1rem; border-radius: 12px; width: 100%; font-weight: 600; cursor: pointer; transition: 0.3s; }
    .btn-submit:hover { box-shadow: 0 0 15px rgba(168,85,247,0.4); transform: translateY(-2px); }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Add <span class="neon-text" style="color: #a855f7;">Course</span></h1>
    <p class="page-subtitle">Define the course details and content tags for the Concept Map.</p>
</div>

<div class="glass-panel form-card">
    @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('courses.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label>Course Code</label>
                <input type="text" name="code" value="{{ old('code') }}" required placeholder="e.g. CSE471">
            </div>
            <div class="form-group">
                <label>Credits</label>
                <input type="number" name="credits" value="{{ old('credits') }}" required placeholder="3" min="1" max="6" step="0.5">
            </div>
        </div>

        <div class="form-group">
            <label>Course Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. System Analysis and Design">
        </div>

        <div class="form-group">
            <label>Course Description</label>
            <textarea name="description" rows="3" required placeholder="Brief overview of the course...">{{ old('description') }}</textarea>
        </div>

        <div class="form-group" style="padding: 1.5rem; border: 1px solid var(--faculty-neon); border-radius: 12px; background: rgba(168,85,247,0.05);">
            <label style="color: var(--faculty-neon);">📚 Course Contents & Tags (For Concept Map)</label>
            <p style="font-size: 0.8rem; color: var(--text-dim); margin-bottom: 1rem;">Enter comma-separated topics mapping to this course to link it on the Concept Map.</p>
            <input type="text" name="course_contents" value="{{ old('course_contents') }}" placeholder="e.g. Algorithms, Data Models, AI, Ethics">
        </div>

        <button type="submit" class="btn-submit">INITIALIZE COURSE</button>
    </form>
</div>
@endsection
