@extends('layouts.auth')

@section('title', 'Login | BRACU HUB')

@section('content')
<div class="auth-card">
    <div class="brand">
        <h1>BRACU <span style="font-weight: 300;">HUB</span></h1>
        <p style="color: var(--text-dim); font-size: 0.9rem; margin-top: 0.5rem;">Secure Access Portal</p>
    </div>

    @if (session('status'))
        <div class="success-msg">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <ul class="error-list">
            @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">UNIVERSITY EMAIL</label>
            <input id="email" class="form-control" type="email" name="email" :value="old('email')" required autofocus placeholder="name@bracu.ac.bd" />
        </div>

        <div class="form-group">
            <label for="password">PASSWORD</label>
            <input id="password" class="form-control" type="password" name="password" required placeholder="••••••••" />
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-dim); cursor: pointer;">
                <input type="checkbox" name="remember" style="accent-color: var(--primary-neon);"> Remember Me
            </label>
            @if (Route::has('password.request'))
                <a class="auth-link" style="font-size: 0.85rem;" href="{{ route('password.request') }}">
                    Forgot Access Key?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-primary">
            INITIALIZE SESSION
        </button>
    </form>

    <div class="auth-footer">
        Don't have a record in the HUB? 
        <a href="{{ route('register') }}" class="auth-link">Create New Account</a>
    </div>
</div>
@endsection
