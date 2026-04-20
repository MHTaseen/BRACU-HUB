@extends('layouts.modern')

@section('title', 'View Submissions | BRACU HUB')

@section('extra_css')
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
    .status-missing  { background: rgba(239, 68, 68, 0.1);  color: #ef4444; border-color: #ef4444; }
    .status-submitted { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: #10b981; }
    .status-late     { background: rgba(249, 115, 22, 0.1);  color: #f97316; border-color: #f97316; }
    .status-graded   { background: rgba(168, 85, 247, 0.1);  color: #a855f7; border-color: #a855f7; }

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

    /* Grade Entry Panel */
    .grade-panel {
        margin-top: 1.25rem;
        padding: 1.25rem;
        background: rgba(168, 85, 247, 0.05);
        border: 1px solid rgba(168, 85, 247, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .grade-panel label {
        font-size: 0.8rem;
        color: var(--faculty-neon);
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .grade-input {
        width: 110px;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(168, 85, 247, 0.4);
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
        color: white;
        font-size: 1rem;
        font-weight: 600;
        transition: border-color 0.2s;
    }
    .grade-input:focus {
        outline: none;
        border-color: var(--faculty-neon);
        box-shadow: 0 0 10px rgba(168, 85, 247, 0.3);
    }

    .btn-grade {
        padding: 0.5rem 1.25rem;
        background: var(--faculty-neon);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-grade:hover {
        filter: brightness(1.15);
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(168, 85, 247, 0.4);
    }

    .marks-display {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--faculty-neon);
    }

    .success-flash {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid #10b981;
        color: #10b981;
        border-radius: 10px;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Mission <span class="neon-text" style="color: var(--faculty-neon);">Reports</span></h1>
    <p class="page-subtitle">{{ $assignment->section->course->code }} • {{ $assignment->title }}</p>
</div>

<div style="max-width: 900px; margin: 0 auto;">

    @if(session('grade_success'))
        <div class="success-flash">✅ {{ session('grade_success') }}</div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h3 style="color: white; margin-bottom: 0.25rem;">Section {{ $assignment->section->section_number }} Roster</h3>
            <div style="font-size: 0.85rem; color: var(--text-dim);">
                Due: {{ $assignment->due_date->format('M d, Y h:i A') }}
                &nbsp;•&nbsp;
                Max Marks: <span style="color: var(--faculty-neon); font-weight: 700;">{{ $assignment->max_marks }}</span>
                &nbsp;•&nbsp;
                Weight: <span style="color: var(--faculty-neon); font-weight: 700;">{{ $assignment->weight }}%</span>
            </div>
        </div>
        <a href="{{ route('assignments.create') }}" style="padding: 0.5rem 1rem; border-radius: 6px; background: rgba(255,255,255,0.1); color: white; text-decoration: none; font-size: 0.85rem;">Back to Deployments</a>
    </div>

    @php
        $students    = $assignment->section->students;
        $submissions = $assignment->submissions->keyBy('student_id');
    @endphp

    <div class="submission-grid">
        @foreach($students as $student)
            @php
                $sub      = $submissions->get($student->id);
                $isLate   = $sub && $sub->created_at > $assignment->due_date;
                $isGraded = $sub && $sub->marks_obtained !== null;
            @endphp

            <div class="glass-panel submission-card" style="flex-direction: column; align-items: stretch;">
                {{-- Top row: student info + status badge --}}
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="student-info">
                        <div class="student-name">{{ $student->name }}</div>
                        <div class="student-id">{{ $student->email }}</div>
                    </div>

                    <div style="text-align: right;">
                        @if($sub)
                            @if($isGraded)
                                <span class="status-badge status-graded">GRADED</span>
                            @elseif($isLate)
                                <span class="status-badge status-late">LATE SUBMISSION</span>
                            @else
                                <span class="status-badge status-submitted">SUBMITTED</span>
                            @endif
                            <div style="font-size: 0.75rem; color: var(--text-dim); margin-top: 0.4rem;">
                                {{ $sub->created_at->format('M d h:i A') }}
                            </div>
                        @else
                            <span class="status-badge status-missing">NO SUBMISSION</span>
                        @endif
                    </div>
                </div>

                {{-- Submission content --}}
                @if($sub)
                    <div style="border-top: 1px solid var(--glass-border); margin-top: 1rem; padding-top: 1rem;">
                        @if($sub->file_path)
                            <a href="{{ route('submissions.download', $sub->id) }}" target="_blank" class="file-link">
                                📎 Download Attached File
                            </a>
                        @endif

                        @if($sub->content)
                            <div class="content-box">{{ $sub->content }}</div>
                        @endif

                        {{-- ── Grade Entry Panel ── --}}
                        <div class="grade-panel">
                            @if($isGraded)
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">Marks Awarded</div>
                                    <span class="marks-display">{{ $sub->marks_obtained }} / {{ $assignment->max_marks }}</span>
                                    <span style="font-size: 0.85rem; color: var(--text-dim); margin-left: 0.5rem;">
                                        ({{ round(($sub->marks_obtained / $assignment->max_marks) * 100, 1) }}%)
                                    </span>
                                </div>
                                <div style="flex: 1;"></div>
                            @endif

                            <form method="POST" action="{{ route('submissions.grade', $sub->id) }}" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                                @csrf
                                @method('PATCH')
                                <label for="marks-{{ $sub->id }}">
                                    {{ $isGraded ? 'Update Marks' : 'Enter Marks' }}
                                </label>
                                <input
                                    type="number"
                                    name="marks_obtained"
                                    id="marks-{{ $sub->id }}"
                                    class="grade-input"
                                    step="0.5"
                                    min="0"
                                    max="{{ $assignment->max_marks }}"
                                    value="{{ $sub->marks_obtained ?? '' }}"
                                    placeholder="0 – {{ $assignment->max_marks }}"
                                    required
                                >
                                <span style="font-size: 0.85rem; color: var(--text-dim);">/ {{ $assignment->max_marks }}</span>
                                <button type="submit" class="btn-grade">
                                    {{ $isGraded ? '✏️ Update' : '✅ Save Marks' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
