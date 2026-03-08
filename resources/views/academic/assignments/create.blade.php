@extends('layouts.modern')

@section('title', 'Create Assignment | BRACU HUB')

@section('extra_css')
<style>
    .form-card {
        max-width: 800px;
        margin: 0 auto;
        padding: 3rem;
    }

    .form-group {
        margin-bottom: 2rem;
    }

    label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: var(--text-dim);
        font-size: 0.9rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    input, select, textarea {
        width: 100%;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        color: white;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: var(--faculty-neon);
        box-shadow: 0 0 15px rgba(168, 85, 247, 0.2);
        background: rgba(0, 0, 0, 0.3);
    }

    .btn-primary {
        background: var(--faculty-neon);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 12px;
        font-weight: 600;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(168, 85, 247, 0.4);
        filter: brightness(1.1);
    }

    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        filter: grayscale(1);
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        display: none;
        border: 1px solid transparent;
        font-weight: 500;
    }

    .alert-success {
        display: block;
        background: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.3);
        color: #34d399;
    }

    .alert-danger {
        display: block;
        background: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.3);
        color: #f87171;
    }

    .conflict-warning {
        background: rgba(245, 158, 11, 0.1);
        border-color: rgba(245, 158, 11, 0.3);
        color: #fbbf24;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="page-header" style="text-align: center;">
    <h1 class="page-title">Issue <span class="neon-text" style="text-shadow: 0 0 10px var(--faculty-neon)aa;">Mission</span></h1>
    <p class="page-subtitle">Deploy new assignments and verify student workload using the Conflict Radar.</p>
</div>

<div class="glass-panel form-card">
    @if(session('success'))
        <div class="alert alert-success">
            ✨ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="conflict-alert" class="alert"></div>

    <form id="assignment-form" action="{{ route('assignments.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="section_id">Target Squadron (Section)</label>
            <select name="section_id" id="section_id" required>
                <option value="">Select a section...</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->course->code }} - Section {{ $section->section_number }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="title">Mission Title</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. Neural Network Architecture">
        </div>

        <div class="form-group">
            <label for="description">Intelligence (Guidelines)</label>
            <textarea name="description" id="description" rows="4" placeholder="Briefing details for the students...">{{ old('description') }}</textarea>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="weight">Impact Weight (%)</label>
                <input type="number" name="weight" id="weight" step="0.5" min="0" max="100" value="{{ old('weight') }}" required placeholder="15">
            </div>

            <div class="form-group">
                <label for="due_date">Deadline (Radar Sync)</label>
                <input type="date" name="due_date" id="due_date" 
                       min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" 
                       value="{{ old('due_date') }}" required>
            </div>
        </div>

        <button type="submit" class="btn-primary" id="submit-btn" disabled>
            DEPLOY ASSIGNMENT
        </button>
    </form>
</div>
@endsection

@section('extra_js')
<script>
    const sectionSelect = document.getElementById('section_id');
    const dateInput = document.getElementById('due_date');
    const conflictAlert = document.getElementById('conflict-alert');
    const submitBtn = document.getElementById('submit-btn');
    const assignmentForm = document.getElementById('assignment-form');

    async function checkConflicts() {
        const sectionId = sectionSelect.value;
        const dueDate = dateInput.value;

        if (!sectionId || !dueDate) {
            submitBtn.disabled = true;
            conflictAlert.style.display = 'none';
            return;
        }

        conflictAlert.className = 'alert';
        conflictAlert.innerHTML = '🛰️ Scanning for schedule conflicts...';
        conflictAlert.style.display = 'block';
        submitBtn.disabled = true;

        try {
            const response = await fetch("{{ route('assignments.check_conflict') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ section_id: sectionId, due_date: dueDate })
            });

            const contentType = response.headers.get("content-type");
            if (!response.ok || !contentType || !contentType.includes("application/json")) {
                let errorTitle = '❌ Radar Malfunction';
                let errorDetail = 'The HUB core returned an invalid signal.';
                
                if (response.status === 422 && contentType && contentType.includes("application/json")) {
                    const errorData = await response.json();
                    errorTitle = '⚠️ VALIDATION ERROR';
                    errorDetail = '';
                    for (const field in errorData.errors) {
                        errorDetail += `• ${errorData.errors[field][0]}<br>`;
                    }
                } else if (!response.ok) {
                    errorDetail = `📡 Signal Error: Status ${response.status} from HUB core.`;
                }

                conflictAlert.className = 'alert alert-danger';
                conflictAlert.innerHTML = `<strong>${errorTitle}</strong><br>${errorDetail}`;
                submitBtn.disabled = true;
                return;
            }

            const result = await response.json();

            if (result.has_conflict) {
                conflictAlert.className = 'alert alert-danger';
                let msg = `⚠️ <strong>CRITICAL CONFLICT:</strong> ${result.conflict_count} student(s) have a major exam or other event on this day:<br><ul style="margin-top:0.5rem">`;
                result.conflicts.forEach(c => {
                    msg += `<li>${c.title} (${c.type})</li>`;
                });
                msg += `</ul>`;
                conflictAlert.innerHTML = msg;
                submitBtn.disabled = false; // Allow override but keep warning
            } else {
                conflictAlert.className = 'alert alert-success';
                conflictAlert.innerHTML = '✅ <strong>CLEAR SKIES:</strong> No conflicts detected for this mission date.';
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Radar Error:', error);
            conflictAlert.className = 'alert alert-danger';
            conflictAlert.innerHTML = '❌ <strong>RADAR CRITICAL FAILURE:</strong> Unable to process data from the HUB core.';
            submitBtn.disabled = true;
        }
    }

    sectionSelect.addEventListener('change', checkConflicts);
    dateInput.addEventListener('change', checkConflicts);
    
    // Initial check if values exist (e.g. on validation error redirect)
    if (sectionSelect.value && dateInput.value) {
        checkConflicts();
    }

    assignmentForm.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'UPLOADING TO HUB...';
    });
</script>
@endsection
