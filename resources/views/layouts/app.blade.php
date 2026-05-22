<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management API Demo</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f4f4f5; color: #333; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1, h2 { color: #111; }
        .hidden { display: none !important; }
        input, button { padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; width: 100%; box-sizing: border-box; }
        button { background: #000; color: #fff; cursor: pointer; border: none; font-weight: bold; }
        button:hover { background: #333; }
        .product-item { border-bottom: 1px solid #eee; padding: 15px 0; display: flex; justify-content: space-between; align-items: center; }
        .product-item:last-child { border-bottom: none; }
        .product-actions button { width: auto; margin-left: 10px; padding: 5px 10px; font-size: 0.9em; }
        .btn-danger { background: #e3342f; }
        .btn-danger:hover { background: #cc1f1a; }
        .btn-outline { background: transparent; color: #000; border: 1px solid #000; }
        .btn-outline:hover { background: #f4f4f5; }
        .error { color: #e3342f; font-size: 0.9em; margin-bottom: 10px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 20px; margin-bottom: 20px; }
        .pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        .pagination button { width: auto; }
        #toast { position: fixed; bottom: 20px; right: 20px; background: #333; color: white; padding: 10px 20px; border-radius: 4px; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
        #toast.show { opacity: 1; }
    </style>
</head>
<body>
    <div class="container">
        @yield('content')
    </div>
    
    <div id="toast"></div>

    <script>
        const API_URL = '/api/v1';

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
    </script>
    @yield('scripts')
</body>
</html>
