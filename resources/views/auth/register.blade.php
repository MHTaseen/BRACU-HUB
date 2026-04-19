@extends('layouts.auth')

@section('title', 'Register | BRACU HUB')

@section('content')
<div class="auth-card">
    <div class="brand">
        <h1>JOIN <span style="font-weight: 300;">HUB</span></h1>
        <p style="color: var(--text-dim); font-size: 0.9rem; margin-top: 0.5rem;">Academic Identity Creation</p>
    </div>

    @if ($errors->any())
        <ul class="error-list">
            @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">FULL NAME</label>
            <input id="name" class="form-control" type="text" name="name" :value="old('name')" required autofocus placeholder="John Doe" />
        </div>

        <div class="form-group">
            <label for="email">UNIVERSITY EMAIL</label>
            <input id="email" class="form-control" type="email" name="email" :value="old('email')" required placeholder="ID@g.bracu.ac.bd" />
            <small class="hint">
                📡 <strong>FACULTY:</strong> @bracu.ac.bd<br>
                📡 <strong>STUDENT:</strong> @g.bracu.ac.bd
            </small>
        </div>

        <div class="form-group">
            <label for="password">ACCESS KEY (PASSWORD)</label>
            <input id="password" class="form-control" type="password" name="password" required placeholder="Min. 8 characters" />
        </div>

        <div class="form-group">
            <label for="password_confirmation">RE-VERIFY KEY</label>
            <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required placeholder="••••••••" />
        </div>

        <button type="submit" class="btn-primary">
            REGISTER IDENTITY
        </button>
    </form>

    <div class="auth-footer">
        Already have a record? 
        <a href="{{ route('login') }}" class="auth-link">Return to Portal</a>
    </div>
</div>
@endsection
