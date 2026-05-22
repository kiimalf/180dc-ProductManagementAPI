@extends('layouts.app')

@section('content')

<!-- Product Creation Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Product</h3>
    </div>
    <form onsubmit="createProduct(event)">
        <div class="grid-2-form">
            <div class="form-group">
                <label for="new-name">Product Name</label>
                <input type="text" id="new-name" placeholder="E.g. Mechanical Keyboard" required>
            </div>
            <div class="form-group">
                <label for="new-desc">Description</label>
                <input type="text" id="new-desc" placeholder="Brief details about the product...">
            </div>
            <div class="form-group">
                <label for="new-price">Price ($)</label>
                <input type="number" id="new-price" placeholder="0.00" step="0.01" min="0" required>
            </div>
        </div>
        <div class="flex justify-between items-center mt-4">
            <span class="text-sm text-muted">All products are tied to your account.</span>
            <button type="submit" class="btn" id="btn-create">Create Product</button>
        </div>
    </form>
</div>

<!-- Product Management Card -->
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h3 class="card-title">Inventory</h3>
        
        <div class="flex gap-2">
            <input type="text" id="search" placeholder="Search products..." onkeyup="debounceSearch()" style="width: 250px;">
            <select id="sort" onchange="loadProducts()" style="width: auto;">
                <option value="created_at_desc">Newest First</option>
                <option value="created_at_asc">Oldest First</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="name_asc">Name: A-Z</option>
            </select>
        </div>
    </div>

    <div id="loading-state" class="loading">
        Loading products...
    </div>

    <div id="products-list" class="hidden">
        <!-- Products will be loaded here -->
    </div>

    <div class="pagination">
        <button onclick="changePage(-1)" id="prev-btn" class="btn btn-outline btn-sm" disabled>Previous</button>
        <span id="page-info" class="text-sm font-medium">Page 1</span>
        <button onclick="changePage(1)" id="next-btn" class="btn btn-outline btn-sm" disabled>Next</button>
    </div>
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

    function parseSort() {
        const val = document.getElementById('sort').value;
        const [sort, direction] = val.includes('_') ? [val.substring(0, val.lastIndexOf('_')), val.substring(val.lastIndexOf('_') + 1)] : ['created_at', 'desc'];
        return { sort, direction };
    }

    async function loadProducts() {
        document.getElementById('loading-state').classList.remove('hidden');
        document.getElementById('products-list').classList.add('hidden');
        
        const search = document.getElementById('search').value;
        const { sort, direction } = parseSort();
        
        let url = `/products?page=${currentPage}&sort=${sort}&direction=${direction}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;

        const res = await fetchApi(url);
        
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('products-list').classList.remove('hidden');

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
            list.innerHTML = `
                <div class="text-center" style="padding: 3rem 1rem;">
                    <div style="color: var(--text-muted); margin-bottom: 0.5rem;">
                        <svg style="width: 48px; height: 48px; margin: 0 auto;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <p>No products found.</p>
                </div>
            `;
            return;
        }

        list.innerHTML = products.map(p => `
            <div class="product-item" id="product-${p.id}">
                <div>
                    <div class="flex items-center">
                        <strong>${escapeHtml(p.name)}</strong>
                        <span class="price-badge">$${parseFloat(p.price).toFixed(2)}</span>
                    </div>
                    <div class="product-meta" style="margin-bottom: 8px;">
                        ${escapeHtml(p.description || 'No description provided.')} 
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); border-top: 1px dashed var(--border-color); padding-top: 6px; display: inline-block;">
                        Owner: <strong>${escapeHtml(p.owner.name || 'Unknown')}</strong>
                    </div>
                </div>
                <div>
                    <button onclick="deleteProduct(${p.id})" class="btn btn-danger btn-sm">Delete</button>
                </div>
            </div>
        `).join('');
    }

    function debounceSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadProducts();
        }, 400);
    }

    async function createProduct(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-create');
        btn.disabled = true;
        btn.innerText = 'Creating...';

        const name = document.getElementById('new-name').value;
        const description = document.getElementById('new-desc').value;
        const price = document.getElementById('new-price').value;

        const res = await fetchApi('/products', {
            method: 'POST',
            body: JSON.stringify({ name, description, price })
        });

        btn.disabled = false;
        btn.innerText = 'Create Product';

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
            showToast(res.data.message || 'Failed to delete product. You can only delete your own products.');
        }
    }

    function changePage(delta) {
        currentPage += delta;
        loadProducts();
    }

    // Initial load
    loadProducts();
</script>
@endsection
