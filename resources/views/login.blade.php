@extends('layouts.app')

@section('content')
<div style="max-width: 400px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title text-center">Sign In</h2>
        </div>
        <div id="login-error" class="error hidden"></div>
        <form onsubmit="handleLogin(event)">
            <div class="form-group">
                <label for="login-email">Email Address</label>
                <input type="email" id="login-email" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label for="login-password">Password</label>
                <input type="password" id="login-password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn" style="width: 100%">Sign In</button>
        </form>
        
        <div class="text-center mt-4 text-sm">
            Don't have an account? <a href="/register" style="color: var(--primary); font-weight: 600; text-decoration: none;">Register here</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    if (localStorage.getItem('jwt_token')) {
        window.location.href = '/dashboard';
    }

    function showError(elementId, message) {
        const el = document.getElementById(elementId);
        if (message) {
            el.innerText = message;
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }

    async function handleLogin(e) {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        
        showError('login-error', '');
        const btn = e.target.querySelector('button');
        btn.disabled = true;
        btn.innerText = 'Signing in...';

        const res = await fetchApi('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });

        if (res && res.status === 200) {
            localStorage.setItem('jwt_token', res.data.data.access_token);
            window.location.href = '/dashboard';
        } else if (res) {
            showError('login-error', res.data.message || 'Login failed. Please check your credentials.');
            btn.disabled = false;
            btn.innerText = 'Sign In';
        }
    }
</script>
@endsection
