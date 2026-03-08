@extends('layouts.modern')

@section('title', 'Mark Attendance | BRACU HUB')

@section('extra_css')
<style>
    .selection-card {
        padding: 2.5rem;
        margin-bottom: 2rem;
    }

    .attendance-card {
        padding: 2.5rem;
    }

    .form-row {
        display: flex;
        gap: 1.5rem;
        align-items: flex-end;
    }

    select, input[type="date"] {
        flex: 1;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 0.85rem 1.25rem;
        color: white;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    select:focus, input:focus {
        outline: none;
        border-color: var(--faculty-neon);
        background: rgba(0, 0, 0, 0.3);
    }

    .btn-secondary {
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
        border: 1px solid var(--glass-border);
        padding: 0.85rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .attendance-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.75rem;
        margin-top: 1.5rem;
    }

    .attendance-table th {
        text-align: left;
        padding: 0 1rem;
        color: var(--text-dim);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .attendance-row {
        background: rgba(255, 255, 255, 0.02);
        transition: all 0.3s ease;
    }

    .attendance-row:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .attendance-row td {
        padding: 1.25rem 1rem;
    }

    .attendance-row td:first-child { border-radius: 12px 0 0 12px; }
    .attendance-row td:last-child { border-radius: 0 12px 12px 0; }

    .radio-group {
        display: flex;
        gap: 1rem;
    }

    .status-btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s ease;
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-dim);
    }

    input[type="radio"] { display: none; }

    input[value="present"]:checked + .status-btn { background: rgba(16, 185, 129, 0.1); border-color: #34d399; color: #34d399; box-shadow: 0 0 10px rgba(16, 185, 129, 0.2); }
    input[value="late"]:checked + .status-btn { background: rgba(245, 158, 11, 0.1); border-color: #fbbf24; color: #fbbf24; box-shadow: 0 0 10px rgba(245, 158, 11, 0.2); }
    input[value="absent"]:checked + .status-btn { background: rgba(239, 68, 68, 0.1); border-color: #f87171; color: #f87171; box-shadow: 0 0 10px rgba(239, 68, 68, 0.2); }

    .btn-submit {
        background: var(--faculty-neon);
        color: white;
        border: none;
        padding: 1.25rem;
        border-radius: 12px;
        font-weight: 700;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 2rem;
        letter-spacing: 0.05em;
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(168, 85, 247, 0.3);
    }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Attendance <span class="neon-text" style="text-shadow: 0 0 10px var(--faculty-neon)aa;">Protocol</span></h1>
    <p class="page-subtitle">Synchronize class attendance data across the student network.</p>
</div>

@if(session('success'))
    <div class="glass-panel" style="padding: 1rem 2rem; border-color: rgba(16, 185, 129, 0.3); color: #34d399; margin-bottom: 2rem; text-align: center;">
        ⚡ {{ session('success') }}
    </div>
@endif

<div class="glass-panel selection-card">
    <form method="GET" action="{{ route('attendance.create') }}" class="form-row">
        <div style="flex: 2;">
            <label style="display: block; font-size: 0.75rem; color: var(--text-dim); margin-bottom: 0.5rem; text-transform: uppercase;">SELECT FREQUENCY (SECTION)</label>
            <select name="section_id" id="section_id" required>
                <option value="">Awaiting section input...</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->course->code }} • Section {{ $section->section_number }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-secondary">INITIALIZE SCAN</button>
    </form>
</div>

@if($selectedSection)
<div class="glass-panel attendance-card">
    <form method="POST" action="{{ route('attendance.store') }}">
        @csrf
        <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
        
        <div style="margin-bottom: 2.5rem; max-width: 320px;">
            <label style="display: block; font-size: 0.75rem; color: var(--text-dim); margin-bottom: 0.5rem; text-transform: uppercase;">SESSION TIMESTAMP</label>
            <input type="date" name="class_date" id="class_date" value="{{ date('Y-m-d') }}" required>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Subject ID (Student)</th>
                    <th>Status Assignment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr class="attendance-row">
                    <td>
                        <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;">{{ $student->name }}</div>
                        <div style="color: var(--text-dim); font-size: 0.8rem;">#{{ $student->id }}</div>
                    </td>
                    <td>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="attendance[{{ $student->id }}]" value="present" required checked>
                                <span class="status-btn">PRESENT</span>
                            </label>
                            <label>
                                <input type="radio" name="attendance[{{ $student->id }}]" value="late">
                                <span class="status-btn">LATE</span>
                            </label>
                            <label>
                                <input type="radio" name="attendance[{{ $student->id }}]" value="absent">
                                <span class="status-btn">ABSENT</span>
                            </label>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; padding: 3rem; color: var(--text-dim);">No signals found for this section.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($students->isNotEmpty())
            <button type="submit" class="btn-submit">UPLOAD RECORDS TO HUB</button>
        @endif
    </form>
</div>
@endif
@endsection
