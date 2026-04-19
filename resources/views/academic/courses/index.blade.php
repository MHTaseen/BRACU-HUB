@extends('layouts.modern')

@section('title', 'University Curriculum | BRACU HUB')

@section('extra_css')
<style>
    .directory-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }
    .course-card {
        padding: 2rem;
        transition: transform 0.3s ease;
    }
    .course-card:hover {
        transform: translateY(-5px);
        border-color: var(--faculty-neon);
    }
    .subtext {
        font-size: 0.85rem;
        color: var(--text-dim);
    }
    .add-btn {
        display: inline-block;
        background: var(--faculty-neon);
        color: white;
        text-decoration: none;
        padding: 0.75rem 1.5rem;
        border-radius: 999px;
        font-weight: 600;
        margin-top: 1rem;
        transition: all 0.3s ease;
    }
    .add-btn:hover {
        box-shadow: 0 0 15px rgba(168, 85, 247, 0.4);
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">University <span class="neon-text" style="color: #a855f7; text-shadow: 0 0 10px #a855f7aa;">Curriculum</span></h1>
    <p class="page-subtitle">Manage courses and their content tags for the Concept Map.</p>
    <a href="{{ route('courses.create') }}" class="add-btn">+ ADD NEW COURSE</a>
</div>

@if(session('success'))
<div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; text-align: center;">
    {{ session('success') }}
</div>
@endif

<div class="directory-grid">
    @foreach($courses as $course)
    <div class="glass-panel course-card">
        <h3 style="color: var(--faculty-neon); margin-bottom: 0.5rem; font-size: 1.2rem;">{{ $course->code }}</h3>
        <h4 style="font-size: 1.1rem; margin-bottom: 1rem;">{{ $course->title }}</h4>
        <p class="subtext" style="margin-bottom: 0.5rem;">{{ Str::limit($course->description, 80) }}</p>
        <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--glass-border); padding-top: 1rem; margin-top: 1rem;">
            <span class="subtext">Credits: {{ $course->credits }}</span>
            <span class="subtext">Active Sections: {{ $course->sections_count }}</span>
        </div>
        <div style="margin-top: 1rem;">
            <a href="{{ route('sections.create', ['course_id' => $course->id]) }}" style="color: var(--faculty-neon); text-decoration: none; font-size: 0.85rem; font-weight: 600;">+ OPEN SECTION</a>
        </div>
    </div>
    @endforeach
</div>
@endsection
