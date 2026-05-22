<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    public function index()
    {
        $query = Product::with('user');

        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        $sortField = request('sort', 'created_at');
        $sortDirection = request('direction', 'desc');
        
        $allowedSorts = ['name', 'price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $products = $query->paginate(10);

        return ProductResource::collection($products)->additional([
            'success' => true,
            'message' => 'Products retrieved successfully.',
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = auth('api')->user()->products()->create($request->validated());

        return $this->successResponse('Product created successfully.', new ProductResource($product), 201);
    }

    public function show(Product $product)
    {
        return $this->successResponse('Product retrieved successfully.', new ProductResource($product->load('user')));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $product->update($request->validated());

        return $this->successResponse('Product updated successfully.', new ProductResource($product));
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return $this->successResponse('Product deleted successfully.');
    }
}
