<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management API Demo</title>
    <style>
        :root {
            --bg-color: #f8fafc;
            --text-main: #334155;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --primary: #0f172a;
            --primary-hover: #334155;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --card-bg: #ffffff;
            --radius: 6px;
        }

        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            background: var(--bg-color); 
            color: var(--text-main); 
            margin: 0; 
            padding: 0; 
            line-height: 1.5;
        }
        
        .navbar {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.125rem;
            color: var(--primary);
        }

        .navbar-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .container { 
            max-width: 900px; 
            margin: 2rem auto; 
            padding: 0 1rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
        }

        .card-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        h1, h2, h3 { color: var(--primary); margin-top: 0; }
        
        .hidden { display: none !important; }
        
        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        input, select { 
            width: 100%; 
            padding: 0.5rem 0.75rem; 
            border: 1px solid var(--border-color); 
            border-radius: var(--radius); 
            box-sizing: border-box; 
            font-size: 0.95rem;
            transition: border-color 0.15s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--text-muted);
        }

        .btn { 
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 0.5rem 1rem;
            background: var(--primary); 
            color: #fff; 
            cursor: pointer; 
            border: 1px solid transparent; 
            border-radius: var(--radius); 
            font-weight: 500; 
            font-size: 0.95rem;
            transition: background-color 0.15s, color 0.15s;
        }
        .btn:hover { background: var(--primary-hover); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: var(--danger-hover); }
        
        .btn-outline { background: transparent; color: var(--primary); border: 1px solid var(--border-color); }
        .btn-outline:hover { background: var(--bg-color); }

        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }

        .error { color: var(--danger); font-size: 0.875rem; margin-bottom: 1rem; padding: 0.5rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius); }
        
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 0.5rem; }
        .gap-4 { gap: 1rem; }
        .mt-4 { margin-top: 1rem; }
        .mb-4 { margin-bottom: 1rem; }
        
        .text-center { text-align: center; }
        .text-muted { color: var(--text-muted); }
        .text-sm { font-size: 0.875rem; }

        .grid-2 { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        @media (min-width: 768px) {
            .grid-2 { grid-template-columns: 1fr 1fr; }
            .grid-2-form { display: grid; grid-template-columns: 2fr 3fr 1fr; gap: 1rem; align-items: start; }
        }

        #toast { 
            position: fixed; 
            bottom: 20px; 
            right: 20px; 
            background: var(--primary); 
            color: white; 
            padding: 0.75rem 1.5rem; 
            border-radius: var(--radius); 
            opacity: 0; 
            transition: opacity 0.3s, transform 0.3s; 
            transform: translateY(20px);
            pointer-events: none; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 50;
        }
        #toast.show { opacity: 1; transform: translateY(0); }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }

        /* Product List Styles */
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-bottom: 0.75rem;
            background: #fff;
        }

        .product-item:hover {
            border-color: #cbd5e1;
        }

        .product-meta {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .price-badge {
            display: inline-block;
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }

        .pagination { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-top: 1.5rem; 
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            Product Management API
        </div>
        <div class="navbar-nav" id="nav-actions">
            <!-- Will be populated by JS based on auth state -->
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>
    
    <div id="toast"></div>

    <script>
        const API_URL = '/api/v1';

        // Check Auth and setup Nav
        document.addEventListener('DOMContentLoaded', () => {
            const navActions = document.getElementById('nav-actions');
            const token = localStorage.getItem('jwt_token');
            
            if (token) {
                navActions.innerHTML = `
                    <span class="text-sm text-muted" style="margin-right: 10px;">Authenticated</span>
                    <button onclick="globalLogout()" class="btn btn-outline btn-sm">Logout</button>
                `;
            } else {
                navActions.innerHTML = `
                    <span class="text-sm text-muted">Guest Mode</span>
                `;
            }
        });

        function globalLogout() {
            localStorage.removeItem('jwt_token');
            window.location.href = '/';
        }

        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        async function fetchApi(endpoint, options = {}) {
            const token = localStorage.getItem('jwt_token');
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            
            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            try {
                const response = await fetch(`${API_URL}${endpoint}`, {
                    ...options,
                    headers: { ...headers, ...options.headers }
                });

                const data = await response.json();
                
                if (response.status === 401) {
                    localStorage.removeItem('jwt_token');
                    window.location.href = '/';
                    return null;
                }
                
                return { status: response.status, data };
            } catch (error) {
                console.error('API Error:', error);
                showToast('A network error occurred.');
                return null;
            }
        }

        function escapeHtml(unsafe) {
            return (unsafe || '').toString()
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }
    </script>
    @yield('scripts')
</body>
</html>
