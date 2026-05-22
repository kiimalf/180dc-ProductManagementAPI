<?php

namespace Tests\Feature\Product;

use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        return ['user' => $user, 'token' => $token];
    }

    public function test_user_can_create_product(): void
    {
        $auth = $this->authenticate();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->postJson('/api/v1/products', [
            'name' => 'New Product',
            'description' => 'A nice product',
            'price' => 99.99,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'user_id' => $auth['user']->id,
        ]);
    }

    public function test_user_can_view_single_product(): void
    {
        $auth = $this->authenticate();
        $product = Product::factory()->create(['user_id' => $auth['user']->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', $product->name);
    }

    public function test_user_can_update_product(): void
    {
        $auth = $this->authenticate();
        $product = Product::factory()->create(['user_id' => $auth['user']->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->patchJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Product',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Product');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
        ]);
    }

    public function test_user_can_delete_product(): void
    {
        $auth = $this->authenticate();
        $product = Product::factory()->create(['user_id' => $auth['user']->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
