@extends('layouts.modern')

@section('title', 'Smart Revision Planner | BRACU HUB')

@section('extra_css')
<style>
    .planner-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 2rem; }
    .course-card { padding: 2rem; }
    .course-code { font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem; }
    .course-title { font-size: 1.4rem; margin-bottom: 1.5rem; color: var(--primary-neon); }
    
    .checklist-item {
        display: flex; align-items: center; gap: 1rem;
        padding: 1rem; border: 1px solid var(--glass-border);
        border-radius: 8px; margin-bottom: 0.75rem;
        transition: all 0.2s; background: rgba(0,0,0,0.2);
    }
    
    .checklist-item:hover { border-color: rgba(34, 211, 238, 0.4); transform: translateX(5px); }

    .custom-checkbox {
        width: 24px; height: 24px; border: 2px solid var(--text-dim);
        border-radius: 6px; cursor: pointer; display: flex;
        justify-content: center; align-items: center; transition: all 0.2s;
        flex-shrink: 0;
    }

    .custom-checkbox.checked {
        background: var(--primary-neon); border-color: var(--primary-neon);
    }

    .custom-checkbox.checked::after {
        content: '✓'; color: black; font-weight: 900; font-size: 14px;
    }

    .tag-name { font-weight: 500; font-size: 1rem; transition: all 0.2s; }
    .checklist-item.completed .tag-name { text-decoration: line-through; color: var(--text-dim); }

    .progress-bar-container {
        width: 100%; height: 6px; background: rgba(255,255,255,0.1);
        border-radius: 999px; margin-bottom: 1.5rem; overflow: hidden;
    }

    .progress-bar {
        height: 100%; background: var(--primary-neon);
        width: 0%; transition: width 0.4s ease-out;
    }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Smart <span class="neon-text">Revision Planner</span></h1>
    <p class="page-subtitle">Algorithms automatically parsed lecture topics to generate your exam prep checklist.</p>
</div>

<div class="planner-grid">
    @forelse($revisionPlan as $courseId => $data)
        <div class="glass-panel course-card" id="course-card-{{ $courseId }}">
            <div class="course-code">EXAM TARGET • {{ $data['course']->code }}</div>
            <h3 class="course-title">{{ $data['course']->title }}</h3>
            
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--text-dim); margin-bottom: 0.5rem;">
                <span>Revision Progress</span>
                <span class="progress-text">0%</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar"></div>
            </div>

            <div class="tags-container">
                @forelse($data['tags'] as $tag)
                    <div class="checklist-item" data-course="{{ $courseId }}" onclick="toggleCheckbox(this)">
                        <div class="custom-checkbox"></div>
                        <span class="tag-name">{{ $tag->name }}</span>
                    </div>
                @empty
                    <div style="color: var(--text-dim); font-size: 0.85rem; font-style: italic;">
                        Instructor has not provided concept tags for this course yet.
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="glass-panel" style="grid-column: 1/-1; padding: 5rem; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
            <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">No Study Plans Active</h3>
            <p style="color: var(--text-dim);">You are not currently enrolled in any courses to generate a study plan.</p>
        </div>
    @endforelse
</div>
@endsection

@section('extra_js')
<script>
    function toggleCheckbox(element) {
        element.classList.toggle('completed');
        const checkbox = element.querySelector('.custom-checkbox');
        checkbox.classList.toggle('checked');
        updateProgress(element.dataset.course);
    }

    function updateProgress(courseId) {
        const card = document.getElementById('course-card-' + courseId);
        const total = card.querySelectorAll('.checklist-item').length;
        if(total === 0) return;
        const completed = card.querySelectorAll('.checklist-item.completed').length;
        const percentage = Math.round((completed / total) * 100);
        
        card.querySelector('.progress-bar').style.width = percentage + '%';
        card.querySelector('.progress-text').innerText = percentage + '%';
    }
</script>
@endsection
