@extends('layouts.app')

@section('content')
<div class="header">
    <h2>Dashboard</h2>
    <button onclick="logout()" class="btn-outline" style="width: auto;">Logout</button>
</div>

<div style="margin-bottom: 20px;">
    <h3>Create Product</h3>
    <form onsubmit="createProduct(event)" style="display: flex; gap: 10px;">
        <input type="text" id="new-name" placeholder="Name" required>
        <input type="text" id="new-desc" placeholder="Description">
        <input type="number" id="new-price" placeholder="Price" step="0.01" required>
        <button type="submit" style="width: auto; padding: 10px 20px;">Add</button>
    </form>
</div>

<div class="header" style="margin-top: 40px;">
    <h3>Your Products</h3>
    <div style="display: flex; gap: 10px;">
        <input type="text" id="search" placeholder="Search..." onkeyup="debounceSearch()" style="width: 200px;">
        <select id="sort" onchange="loadProducts()" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="created_at">Newest First</option>
            <option value="price_asc">Price: Low to High</option>
            <option value="price_desc">Price: High to Low</option>
            <option value="name_asc">Name: A-Z</option>
        </select>
    </div>
</div>

<div id="products-list">
    <!-- Products will be loaded here -->
</div>

<div class="pagination">
    <button onclick="changePage(-1)" id="prev-btn" class="btn-outline" disabled>Previous</button>
    <span id="page-info">Page 1</span>
    <button onclick="changePage(1)" id="next-btn" class="btn-outline" disabled>Next</button>
</div>
@endsection

@section('scripts')
<script>
    let currentPage = 1;
    let lastPage = 1;
    let searchTimeout = null;

    if (!localStorage.getItem('jwt_token')) {
        window.location.href = '/';
    }

    function logout() {
        localStorage.removeItem('jwt_token');
        window.location.href = '/';
    }

    function parseSort() {
        const val = document.getElementById('sort').value;
        if (val === 'created_at') return { sort: 'created_at', direction: 'desc' };
        if (val === 'price_asc') return { sort: 'price', direction: 'asc' };
        if (val === 'price_desc') return { sort: 'price', direction: 'desc' };
        if (val === 'name_asc') return { sort: 'name', direction: 'asc' };
        return { sort: 'created_at', direction: 'desc' };
    }

    async function loadProducts() {
        const search = document.getElementById('search').value;
        const { sort, direction } = parseSort();
        
        let url = `/products?page=${currentPage}&sort=${sort}&direction=${direction}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;

        const res = await fetchApi(url);
        if (res && res.status === 200) {
            renderProducts(res.data.data);
            
            currentPage = res.data.meta.current_page;
            lastPage = res.data.meta.last_page;
            
            document.getElementById('page-info').innerText = `Page ${currentPage} of ${lastPage}`;
            document.getElementById('prev-btn').disabled = currentPage <= 1;
            document.getElementById('next-btn').disabled = currentPage >= lastPage;
        }
    }

    function renderProducts(products) {
        const list = document.getElementById('products-list');
        if (products.length === 0) {
            list.innerHTML = '<p style="text-align: center; color: #666;">No products found.</p>';
            return;
        }

        list.innerHTML = products.map(p => `
            <div class="product-item" id="product-${p.id}">
                <div>
                    <strong>${escapeHtml(p.name)}</strong> - $${p.price}
                    <div style="font-size: 0.9em; color: #666;">${escapeHtml(p.description || '')}</div>
                </div>
                <div class="product-actions">
                    <button onclick="deleteProduct(${p.id})" class="btn-danger">Delete</button>
                </div>
            </div>
        `).join('');
    }

    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadProducts();
        }, 500);
    }

    async function createProduct(e) {
        e.preventDefault();
        const name = document.getElementById('new-name').value;
        const description = document.getElementById('new-desc').value;
        const price = document.getElementById('new-price').value;

        const res = await fetchApi('/products', {
            method: 'POST',
            body: JSON.stringify({ name, description, price })
        });

        if (res && res.status === 201) {
            showToast('Product created successfully');
            document.getElementById('new-name').value = '';
            document.getElementById('new-desc').value = '';
            document.getElementById('new-price').value = '';
            loadProducts();
        } else if (res) {
            showToast(res.data.message || 'Failed to create product');
        }
    }

    async function deleteProduct(id) {
        if (!confirm('Are you sure you want to delete this product?')) return;

        const res = await fetchApi(`/products/${id}`, {
            method: 'DELETE'
        });

        if (res && res.status === 200) {
            showToast('Product deleted');
            loadProducts();
        } else if (res) {
            showToast(res.data.message || 'Failed to delete product');
        }
    }

    function changePage(delta) {
        currentPage += delta;
        loadProducts();
    }

    function escapeHtml(unsafe) {
        return (unsafe || '').toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Initial load
    loadProducts();
</script>
@endsection
