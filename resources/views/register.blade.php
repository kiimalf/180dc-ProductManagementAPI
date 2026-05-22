@extends('layouts.app')

@section('content')
<div style="max-width: 400px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title text-center">Create Account</h2>
        </div>
        <div id="register-error" class="error hidden"></div>
        <form onsubmit="handleRegister(event)">
            <div class="form-group">
                <label for="register-name">Full Name</label>
                <input type="text" id="register-name" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label for="register-email">Email Address</label>
                <input type="email" id="register-email" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label for="register-password">Password</label>
                <input type="password" id="register-password" placeholder="Min. 8 characters" required minlength="8">
            </div>
            <div class="form-group">
                <label for="register-password-confirm">Confirm Password</label>
                <input type="password" id="register-password-confirm" placeholder="Re-type password" required minlength="8">
            </div>
            <button type="submit" class="btn" style="width: 100%">Register</button>
        </form>
        
        <div class="text-center mt-4 text-sm">
            Already have an account? <a href="/" style="color: var(--primary); font-weight: 600; text-decoration: none;">Sign in here</a>
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

    async function handleRegister(e) {
        e.preventDefault();
        const name = document.getElementById('register-name').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;
        const password_confirmation = document.getElementById('register-password-confirm').value;
        
        showError('register-error', '');
        
        if (password !== password_confirmation) {
            showError('register-error', 'Passwords do not match');
            return;
        }

        const btn = e.target.querySelector('button');
        btn.disabled = true;
        btn.innerText = 'Registering...';

        const res = await fetchApi('/auth/register', {
            method: 'POST',
            body: JSON.stringify({ name, email, password, password_confirmation })
        });

        if (res && res.status === 201) {
            localStorage.setItem('jwt_token', res.data.data.access_token);
            window.location.href = '/dashboard';
        } else if (res) {
            if (res.data.errors) {
                showError('register-error', Object.values(res.data.errors).flat().join('\n'));
            } else {
                showError('register-error', res.data.message || 'Registration failed');
            }
            btn.disabled = false;
            btn.innerText = 'Register';
        }
    }
</script>
@endsection
