@extends('layouts.modern')

@section('title', 'Course Repository | BRACU HUB')

@section('extra_css')
<style>
    .repo-container { margin-top: 2rem; display: flex; flex-direction: column; gap: 2rem; }
    .course-card { padding: 0; overflow: hidden; }
    .course-header {
        background: linear-gradient(90deg, rgba(34,211,238,0.1), transparent);
        padding: 1.5rem 2rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--glass-border);
    }
    .course-header:hover { background: rgba(34,211,238,0.15); }
    .course-body { padding: 2rem; display: none; }
    .course-body.active { display: block; }
    
    .material-group { margin-bottom: 2rem; }
    .material-group:last-child { margin-bottom: 0; }
    .group-title {
        color: var(--primary-neon);
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .material-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
    
    .material-item {
        background: rgba(0,0,0,0.3);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 1rem;
        transition: 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 120px;
    }
    .material-item:hover {
        border-color: var(--primary-neon);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(34,211,238,0.1);
    }
    
    .material-icon {
        width: 40px; height: 40px;
        border-radius: 8px;
        background: rgba(34,211,238,0.1);
        color: var(--primary-neon);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1rem;
    }
    
    .btn-download {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: rgba(34,211,238,0.1);
        color: var(--primary-neon);
        border: 1px solid var(--primary-neon);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 1rem;
        transition: 0.3s;
        width: fit-content;
    }
    .btn-download:hover { background: var(--primary-neon); color: var(--bg-deep); }
    
    .chevron { transition: transform 0.3s; }
    .course-header.open .chevron { transform: rotate(180deg); }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Course <span class="neon-text">Vault</span></h1>
    <p class="page-subtitle">Access your lecture materials, assignments, and resources</p>
</div>

<div class="repo-container">
    @forelse($sections as $section)
        @php
            $materials = $section->materials;
            $lectures = $materials->where('type', 'lecture');
            $assignments = $materials->where('type', 'assignment_brief');
            $supplementary = $materials->where('type', 'supplementary');
        @endphp
        <div class="glass-panel course-card">
            <div class="course-header" onclick="toggleCourse(this)">
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">Section {{ $section->section_number }}</div>
                    <h2 style="margin-top: 0.25rem;">{{ $section->course->code }} - {{ $section->course->title }}</h2>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="background: rgba(255,255,255,0.1); padding: 0.3rem 0.8rem; border-radius: 999px; font-size: 0.85rem;">
                        {{ $materials->count() }} Files
                    </span>
                    <svg class="chevron" style="width: 24px; height: 24px; color: var(--text-dim);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
            </div>
            
            <div class="course-body">
                @if($materials->count() === 0)
                    <div style="text-align: center; color: var(--text-dim); padding: 2rem; border: 1px dashed var(--glass-border); border-radius: 12px;">
                        No materials have been uploaded for this course yet.
                    </div>
                @else
                    
                    @if($lectures->count() > 0)
                    <div class="material-group">
                        <div class="group-title">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Lectures & Slides
                        </div>
                        <div class="material-list">
                            @foreach($lectures as $material)
                                @include('academic.student.partials.material-card', ['material' => $material])
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($assignments->count() > 0)
                    <div class="material-group">
                        <div class="group-title">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Assignment Briefs
                        </div>
                        <div class="material-list">
                            @foreach($assignments as $material)
                                @include('academic.student.partials.material-card', ['material' => $material])
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($supplementary->count() > 0)
                    <div class="material-group">
                        <div class="group-title">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Supplementary Resources
                        </div>
                        <div class="material-list">
                            @foreach($supplementary as $material)
                                @include('academic.student.partials.material-card', ['material' => $material])
                            @endforeach
                        </div>
                    </div>
                    @endif

                @endif
            </div>
        </div>
    @empty
        <div class="glass-panel" style="text-align: center; padding: 4rem 2rem;">
            <svg style="width: 48px; height: 48px; color: var(--text-dim); margin-bottom: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <h2 style="color: var(--text-dim);">You are not enrolled in any courses</h2>
            <p style="color: var(--text-dim);">When you enroll, your course materials will appear here.</p>
        </div>
    @endforelse
</div>
@endsection

@section('extra_js')
<script>
    function toggleCourse(header) {
        header.classList.toggle('open');
        const body = header.nextElementSibling;
        body.classList.toggle('active');
    }
</script>
@endsection
