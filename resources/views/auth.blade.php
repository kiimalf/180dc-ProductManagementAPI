@extends('layouts.app')

@section('content')
<div id="auth-forms">
    <div id="login-form">
        <h2>Login</h2>
        <div id="login-error" class="error"></div>
        <form onsubmit="handleLogin(event)">
            <input type="email" id="login-email" placeholder="Email" required>
            <input type="password" id="login-password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            Don't have an account? <a href="#" onclick="toggleForm('register')">Register</a>
        </p>
    </div>

    <div id="register-form" class="hidden">
        <h2>Register</h2>
        <div id="register-error" class="error"></div>
        <form onsubmit="handleRegister(event)">
            <input type="text" id="register-name" placeholder="Name" required>
            <input type="email" id="register-email" placeholder="Email" required>
            <input type="password" id="register-password" placeholder="Password" required minlength="8">
            <input type="password" id="register-password-confirm" placeholder="Confirm Password" required minlength="8">
            <button type="submit">Register</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            Already have an account? <a href="#" onclick="toggleForm('login')">Login</a>
        </p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Redirect to dashboard if already logged in
    if (localStorage.getItem('jwt_token')) {
        window.location.href = '/dashboard';
    }

    function toggleForm(formType) {
        document.getElementById('login-form').classList.toggle('hidden', formType !== 'login');
        document.getElementById('register-form').classList.toggle('hidden', formType !== 'register');
        document.getElementById('login-error').innerText = '';
        document.getElementById('register-error').innerText = '';
    }

    async function handleLogin(e) {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        const errorDiv = document.getElementById('login-error');
        
        errorDiv.innerText = '';
        const res = await fetchApi('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });

        if (res && res.status === 200) {
            localStorage.setItem('jwt_token', res.data.data.access_token);
            window.location.href = '/dashboard';
        } else if (res) {
            errorDiv.innerText = res.data.message || 'Login failed';
        }
    }

    async function handleRegister(e) {
        e.preventDefault();
        const name = document.getElementById('register-name').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;
        const password_confirmation = document.getElementById('register-password-confirm').value;
        const errorDiv = document.getElementById('register-error');
        
        errorDiv.innerText = '';
        
        if (password !== password_confirmation) {
            errorDiv.innerText = 'Passwords do not match';
            return;
        }

        const res = await fetchApi('/auth/register', {
            method: 'POST',
            body: JSON.stringify({ name, email, password, password_confirmation })
        });

        if (res && res.status === 201) {
            localStorage.setItem('jwt_token', res.data.data.access_token);
            window.location.href = '/dashboard';
        } else if (res) {
            if (res.data.errors) {
                errorDiv.innerText = Object.values(res.data.errors).flat().join('\n');
            } else {
                errorDiv.innerText = res.data.message || 'Registration failed';
            }
        }
    }
</script>
@endsection
