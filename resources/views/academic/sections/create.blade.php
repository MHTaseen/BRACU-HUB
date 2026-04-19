@extends('layouts.modern')

@section('title', 'Open Section | BRACU HUB')

@section('extra_css')
<style>
    .form-card { max-width: 600px; margin: 0 auto; padding: 3rem; }
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
    <h1 class="page-title">Open <span class="neon-text" style="color: #a855f7;">Section</span></h1>
    <p class="page-subtitle">Open a new section for {{ $course->code }}: {{ $course->title }}</p>
</div>

<div class="glass-panel form-card">
    @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sections.store') }}" method="POST">
        @csrf
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label>Section Number</label>
                <input type="number" name="section_number" value="{{ old('section_number') }}" required placeholder="e.g. 1" min="1">
            </div>
            <div class="form-group">
                <label>Room</label>
                <input type="text" name="room" value="{{ old('room') }}" required placeholder="e.g. UB30203">
            </div>
        </div>

        <div class="form-group">
            <label>Schedule</label>
            <input type="text" name="schedule" value="{{ old('schedule') }}" required placeholder="e.g. Sun, Tue 09:30 AM - 10:50 AM">
        </div>

        <button type="submit" class="btn-submit">OPEN SECTION</button>
    </form>
</div>
@endsection
