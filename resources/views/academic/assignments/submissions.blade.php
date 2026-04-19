@extends('layouts.modern')

@section('title', 'View Submissions | BRACU HUB')

@section('extra_css')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .submission-grid {
        display: grid;
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .submission-card {
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .student-info { margin-bottom: 0.5rem; }
    .student-name { font-size: 1.2rem; font-weight: 600; color: white; }
    .student-id { font-size: 0.85rem; color: var(--text-dim); }

    .status-badge {
        padding: 0.4rem 1rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid transparent;
    }
    .status-missing { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: #ef4444; }
    .status-submitted { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: #10b981; }
    .status-late { background: rgba(249, 115, 22, 0.1); color: #f97316; border-color: #f97316; }

    .content-box { 
        background: rgba(0, 0, 0, 0.3); 
        border: 1px solid var(--glass-border); 
        border-radius: 8px; 
        padding: 1rem; 
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #ccc;
        white-space: pre-wrap;
    }

    .file-link {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.5rem 1rem; background: rgba(168, 85, 247, 0.1);
        border: 1px solid var(--faculty-neon); color: var(--faculty-neon);
        border-radius: 6px; text-decoration: none; font-size: 0.85rem;
        margin-top: 1rem; transition: 0.2s;
    }
    .file-link:hover { background: var(--faculty-neon); color: black; }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Mission <span class="neon-text" style="color: var(--faculty-neon);">Reports</span></h1>
    <p class="page-subtitle">{{ $assignment->section->course->code }} • {{ $assignment->title }}</p>
</div>

<div style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h3 style="color: white; margin-bottom: 0.25rem;">Section {{ $assignment->section->section_number }} Roster</h3>
            <div style="font-size: 0.85rem; color: var(--text-dim);">Due: {{ $assignment->due_date->format('M d, Y h:i A') }}</div>
        </div>
        <a href="{{ route('assignments.create') }}" style="padding: 0.5rem 1rem; border-radius: 6px; background: rgba(255,255,255,0.1); color: white; text-decoration: none; font-size: 0.85rem;">Back to Deployments</a>
    </div>

    @php
        $students = $assignment->section->students;
        $submissions = $assignment->submissions->keyBy('student_id');
    @endphp

    <div class="submission-grid">
        @foreach($students as $student)
            @php
                $sub = $submissions->get($student->id);
            @endphp
            <div class="glass-panel submission-card" style="flex-direction: column; align-items: stretch;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="student-info">
                        <div class="student-name">{{ $student->name }}</div>
                        <div class="student-id">{{ $student->email }}</div>
                    </div>
                    
                    <div>
                        @if($sub)
                            @if($sub->created_at > $assignment->due_date)
                                <span class="status-badge status-late">LATE SUBMISSION</span>
                            @else
                                <span class="status-badge status-submitted">SUBMITTED</span>
                            @endif
                            <div style="font-size: 0.75rem; color: var(--text-dim); text-align: right; margin-top: 0.4rem;">
                                {{ $sub->created_at->format('M d h:i A') }}
                            </div>
                        @else
                            <span class="status-badge status-missing">NO SUBMISSION</span>
                        @endif
                    </div>
                </div>

                @if($sub)
                    <div style="border-top: 1px solid var(--glass-border); margin-top: 1rem; padding-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                            <div style="flex: 1; min-width: 250px;">
                                @if($sub->file_path)
                                    <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" class="file-link" style="margin-top: 0;">
                                        📎 Download Attached File
                                    </a>
                                @endif

                                @if($sub->content)
                                    <div class="content-box" style="{{ !$sub->file_path ? 'margin-top:0;' : '' }}">{{ $sub->content }}</div>
                                @endif
                            </div>

                            {{-- Grading Panel --}}
                            <div style="background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.2); padding: 1rem; border-radius: 8px; min-width: 200px;">
                                <label style="display: block; font-size: 0.75rem; color: var(--faculty-neon); text-transform: uppercase; font-weight: bold; margin-bottom: 0.5rem;">
                                    Give Marks (Max: {{ $assignment->weight }})
                                </label>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <input type="number" step="0.1" min="0" max="{{ $assignment->weight }}" id="marks-{{ $sub->id }}" value="{{ $sub->marks }}" 
                                        style="width: 80px; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); color: white; border-radius: 6px; padding: 0.4rem; text-align: center;">
                                    <span style="color: var(--text-dim); font-size: 0.9rem;">/ {{ $assignment->weight }}</span>
                                    <button onclick="saveMarks({{ $sub->id }})" id="btn-marks-{{ $sub->id }}"
                                        style="padding: 0.4rem 0.8rem; background: var(--faculty-neon); border: none; color: white; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-left: auto;">
                                        SAVE
                                    </button>
                                </div>
                                <div id="status-{{ $sub->id }}" style="font-size: 0.75rem; margin-top: 0.5rem; min-height: 15px;"></div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('extra_js')
<script>
    async function saveMarks(submissionId) {
        const input = document.getElementById('marks-' + submissionId);
        const btn = document.getElementById('btn-marks-' + submissionId);
        const status = document.getElementById('status-' + submissionId);
        
        const marks = input.value;
        const maxMarks = {{ $assignment->weight }};

        if (marks === '') {
            status.innerHTML = '<span style="color: #f87171;">Please enter a mark.</span>';
            return;
        }

        if (parseFloat(marks) < 0 || parseFloat(marks) > maxMarks) {
            status.innerHTML = '<span style="color: #f87171;">Marks must be between 0 and ' + maxMarks + '.</span>';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '...';
        status.innerHTML = '';

        try {
            const response = await fetch(`/submissions/${submissionId}/marks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ marks: marks })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                status.innerHTML = '<span style="color: #10b981;">✅ Saved!</span>';
            } else {
                status.innerHTML = `<span style="color: #f87171;">❌ Error: ${data.message || 'Validation failed'}</span>`;
            }
        } catch (e) {
            status.innerHTML = '<span style="color: #f87171;">❌ Network error</span>';
        }

        btn.disabled = false;
        btn.innerHTML = 'SAVE';
    }
</script>
@endsection
