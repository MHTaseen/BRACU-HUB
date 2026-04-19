@extends('layouts.modern')

@section('title', 'Manage Section | BRACU HUB')

@section('extra_css')
<style>
    .split-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 2rem; }
    .glass-panel { padding: 2rem; }
    .form-group { margin-bottom: 1.5rem; }
    label { display: block; font-weight: 600; margin-bottom: 0.75rem; color: var(--text-dim); font-size: 0.9rem; text-transform: uppercase; }
    select, input { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--glass-border); border-radius: 12px; padding: 1rem; color: white; }
    select:focus { outline: none; border-color: var(--faculty-neon); }
    .btn-submit { background: var(--faculty-neon); color: white; border: none; padding: 1rem; border-radius: 12px; width: 100%; font-weight: 600; cursor: pointer; transition: 0.3s; }
    .btn-submit:hover { box-shadow: 0 0 15px rgba(168,85,247,0.4); transform: translateY(-2px); }
    
    .student-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 1rem; border-bottom: 1px solid var(--glass-border);
    }
    .student-item:last-child { border-bottom: none; }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">{{ $section->course->code }} <span class="neon-text" style="color: #a855f7;">Section {{ $section->section_number }}</span></h1>
    <p class="page-subtitle">Schedule: {{ $section->schedule }} | Room: {{ $section->room }}</p>
</div>

@if(session('success'))
<div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; text-align: center;">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
    <ul style="margin: 0; padding-left: 1.25rem;">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
</div>
@endif

<div class="split-layout">
    <!-- Add Student Form -->
    <div class="glass-panel" style="height: fit-content;">
        <h3 style="color: var(--faculty-neon); margin-bottom: 1.5rem;">Enroll Student</h3>
        
        <form action="{{ route('sections.enroll', $section->id) }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label>Select Student</label>
                <!-- We use Select2 for simple searchable dropdown -->
                <select name="student_id" id="student_select" required style="width: 100%; color: black;">
                    <option value="">Search by Name or Email...</option>
                    @foreach($allStudents as $student)
                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-submit">ADD TO ROSTER</button>
        </form>
    </div>

    <!-- Roster List -->
    <div class="glass-panel">
        <h3 style="color: var(--faculty-neon); margin-bottom: 1.5rem;">Section Roster ({{ $section->students->count() }})</h3>
        
        @if($section->students->count() === 0)
            <div style="text-align: center; color: var(--text-dim); padding: 2rem;">
                No students enrolled yet.
            </div>
        @else
            <div>
                @foreach($section->students as $student)
                <div class="student-item">
                    <div>
                        <div style="font-weight: 600;">{{ $student->name }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-dim);">{{ $student->email }}</div>
                    </div>
                    <div style="background: rgba(16, 185, 129, 0.1); color: #34d399; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600;">
                        ACTIVE
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Course Materials Section -->
<div class="glass-panel" style="margin-top: 2rem;">
    <h3 style="color: var(--faculty-neon); margin-bottom: 1.5rem;">Course Materials Repository</h3>
    
    <div class="split-layout" style="margin-top: 0;">
        <!-- Upload Form -->
        <div>
            <form action="{{ route('materials.store', $section->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required placeholder="e.g. Week 1 Slides">
                </div>

                <div class="form-group">
                    <label>Material Type</label>
                    <select name="type" required>
                        <option value="lecture">Lecture / Slides</option>
                        <option value="assignment_brief">Assignment Brief</option>
                        <option value="supplementary">Supplementary Resource</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>File (Max 20MB)</label>
                    <input type="file" name="material_file" required style="padding: 0.75rem;">
                </div>

                <div class="form-group">
                    <label>Description (Optional)</label>
                    <textarea name="description" rows="3" style="width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); border-radius: 12px; padding: 1rem; color: white;" placeholder="Brief details about the file..."></textarea>
                </div>

                <button type="submit" class="btn-submit">UPLOAD MATERIAL</button>
            </form>
        </div>

        <!-- Materials List -->
        <div>
            @if($section->materials->count() === 0)
                <div style="text-align: center; color: var(--text-dim); padding: 2rem; border: 1px dashed var(--glass-border); border-radius: 12px;">
                    No materials uploaded for this section yet.
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($section->materials()->latest()->get() as $material)
                        <div style="background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); border-radius: 12px; padding: 1rem; display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <span style="background: rgba(168,85,247,0.2); color: var(--faculty-neon); padding: 0.1rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: bold; text-transform: uppercase;">
                                        {{ str_replace('_', ' ', $material->type) }}
                                    </span>
                                    <div style="font-weight: 600;">{{ $material->title }}</div>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--text-dim); margin-bottom: 0.5rem;">Uploaded {{ $material->created_at->diffForHumans() }}</div>
                                @if($material->description)
                                    <div style="font-size: 0.85rem; color: #ccc;">{{ Str::limit($material->description, 60) }}</div>
                                @endif
                                <div style="margin-top: 0.5rem;">
                                    <a href="{{ route('materials.download', $material->id) }}" style="color: var(--primary-neon); text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; gap: 0.2rem;">
                                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                            <form action="{{ route('materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Delete this material?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: transparent; border: 1px solid #ef4444; color: #ef4444; border-radius: 6px; padding: 0.3rem 0.6rem; cursor: pointer; transition: 0.2s;">
                                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('extra_js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Initialize searchable dropdown
    $(document).ready(function() {
        $('#student_select').select2({
            placeholder: "Search by Name or Email...",
            allowClear: true
        });
    });
</script>
@endsection
