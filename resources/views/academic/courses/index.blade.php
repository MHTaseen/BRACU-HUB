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
            @if($course->sections->count() > 0)
                <div style="margin-bottom: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                    <h5 style="color: var(--text-dim); margin-bottom: 0.5rem; font-size: 0.85rem; text-transform: uppercase;">Your Sections</h5>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        @foreach($course->sections as $section)
                            <a href="{{ route('sections.manage', $section->id) }}" style="display: inline-flex; align-items: center; gap: 0.25rem; background: rgba(168, 85, 247, 0.15); border: 1px solid var(--faculty-neon); color: white; padding: 0.3rem 0.7rem; border-radius: 8px; font-size: 0.85rem; text-decoration: none; transition: 0.2s;">
                                Section {{ $section->section_number }}
                                <svg style="width: 14px; height: 14px; margin-left: 0.2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            <a href="{{ route('sections.create', ['course_id' => $course->id]) }}" style="color: var(--faculty-neon); text-decoration: none; font-size: 0.85rem; font-weight: 600;">+ OPEN NEW SECTION</a>
        </div>
    </div>
    @endforeach
</div>
@endsection
