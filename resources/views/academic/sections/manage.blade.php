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
