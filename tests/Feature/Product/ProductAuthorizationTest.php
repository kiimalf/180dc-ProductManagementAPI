<?php

namespace Tests\Feature\Product;

use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_update_others_product(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $owner->id]);

        $token = auth('api')->login($otherUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/v1/products/{$product->id}", [
            'name' => 'Hacked Product',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_others_product(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $owner->id]);

        $token = auth('api')->login($otherUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(403);
    }
}
