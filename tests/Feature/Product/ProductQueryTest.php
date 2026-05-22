<?php

namespace Tests\Feature\Product;

use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductQueryTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        return ['user' => $user, 'token' => $token];
    }

    public function test_can_paginate_products(): void
    {
        $auth = $this->authenticate();
        Product::factory()->count(15)->create(['user_id' => $auth['user']->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_can_search_products_by_name(): void
    {
        $auth = $this->authenticate();
        Product::factory()->create(['user_id' => $auth['user']->id, 'name' => 'Apple iPhone 15']);
        Product::factory()->create(['user_id' => $auth['user']->id, 'name' => 'Samsung Galaxy S24']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->getJson('/api/v1/products?search=iPhone');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Apple iPhone 15');
    }

    public function test_can_sort_products_by_price(): void
    {
        $auth = $this->authenticate();
        Product::factory()->create(['user_id' => $auth['user']->id, 'name' => 'Cheap', 'price' => 10]);
        Product::factory()->create(['user_id' => $auth['user']->id, 'name' => 'Expensive', 'price' => 100]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->getJson('/api/v1/products?sort=price&direction=desc');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Expensive')
            ->assertJsonPath('data.1.name', 'Cheap');
    }
}
