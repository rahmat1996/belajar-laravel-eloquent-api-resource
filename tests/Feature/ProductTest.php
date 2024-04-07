<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function testProduct()
    {
        $this->seed([CategorySeeder::class, ProductSeeder::class]);
        $product = Product::first();
        $this->get("/api/products/$product->id")
            ->assertStatus(200)
            ->assertHeader("X-Powered-By", "Rahmat")
            ->assertJson([
                "value" => [
                    "name" => $product->name,
                    "category" => [
                        "id" => $product->category->id,
                        "name" => $product->category->name
                    ],
                    "price" => $product->price,
                    "is_expensive" => $product->price > 500,
                    "created_at" => $product->created_at->toJson(),
                    "updated_at" => $product->updated_at->toJson()
                ]
            ]);
    }

    public function testCollectionWrap()
    {
        $this->seed([CategorySeeder::class, ProductSeeder::class]);
        $response = $this->get("/api/products")
            ->assertStatus(200)
            ->assertHeader("X-Powered-By", "Rahmat");

        $names = $response->json("data.*.name");

        for ($i = 0; $i < 5; $i++) {
            $this->assertContains("Product $i of Food", $names);
        }

        for ($i = 0; $i < 5; $i++) {
            $this->assertContains("Product $i of Gadget", $names);
        }
    }

    public function testProductPaging()
    {
        $this->seed([CategorySeeder::class, ProductSeeder::class]);
        $response = $this->get("/api/products-paging")->assertStatus(200);

        $this->assertNotNull($response->json("data"));
        $this->assertNotNull($response->json("links"));
        $this->assertNotNull($response->json("meta"));
    }

    public function testAdditionalMetadata()
    {
        $this->seed([CategorySeeder::class, ProductSeeder::class]);
        $product = Product::first();
        $response = $this->get("/api/products-debug/$product->id")
            ->assertStatus(200)
            ->assertJson([
                "author" => "Rahmat",
                "data" => [
                    "id" => $product->id,
                    "name" => $product->name,
                    "price" => $product->price,
                ]
            ]);

        $this->assertNotNull($response->json("server_time"));
    }
}
