<?php

namespace Tests\Feature;

use App\Database\Seeds\DatabaseSeeder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;

/**
 * @internal
 */
final class ProductApiTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $refresh    = true;
    protected $seed       = DatabaseSeeder::class;
    protected $namespace  = 'App';

    private function decode(TestResponse $result): array
    {
        return json_decode($result->getJSON(), true);
    }

    public function testIndexReturnsPaginatedProducts(): void
    {
        $result = $this->get('api/products');

        $result->assertOK();

        $body = $this->decode($result);
        $this->assertSame('success', $body['status']);
        $this->assertGreaterThanOrEqual(4, $body['data']['pagination']['total']);
        $this->assertArrayHasKey('category_name', $body['data']['items'][0]);
    }

    public function testIndexFiltersByCategory(): void
    {
        $result = $this->get('api/products?category_id=1');

        $result->assertOK();

        $body = $this->decode($result);
        foreach ($body['data']['items'] as $product) {
            $this->assertSame(1, (int) $product['category_id']);
        }
    }

    public function testShowReturnsProductDetail(): void
    {
        $result = $this->get('api/products/1');

        $result->assertOK();

        $body = $this->decode($result);
        $this->assertSame('success', $body['status']);
        $this->assertSame('1', (string) $body['data']['id']);
        $this->assertArrayHasKey('category_name', $body['data']);
    }

    public function testShowReturnsNotFoundForUnknownId(): void
    {
        $result = $this->get('api/products/9999');

        $result->assertStatus(404);

        $body = $this->decode($result);
        $this->assertSame('Product not found', $body['message']);
    }

    public function testCreateProductSuccess(): void
    {
        $result = $this->withBodyFormat('json')->post('api/products', [
            'category_id' => 1,
            'name'        => 'Mechanical Keyboard',
            'sku'         => 'ELEC-100',
            'price'       => 350000,
            'stock'       => 25,
            'description' => 'RGB mechanical keyboard',
        ]);

        $result->assertStatus(201);

        $body = $this->decode($result);
        $this->assertSame('success', $body['status']);
        $this->assertSame('Mechanical Keyboard', $body['data']['name']);

        $this->seeInDatabase('products', ['sku' => 'ELEC-100']);
    }

    public function testCreateProductValidationErrors(): void
    {
        $result = $this->withBodyFormat('json')->post('api/products', [
            'category_id' => 9999,
            'name'        => 'X',
            'sku'         => 'ELEC-001',
            'price'       => -5,
        ]);

        $result->assertStatus(422);

        $body = $this->decode($result);
        $this->assertSame('error', $body['status']);
        $this->assertArrayHasKey('category_id', $body['errors']);
        $this->assertArrayHasKey('name', $body['errors']);
        $this->assertArrayHasKey('sku', $body['errors']);
        $this->assertArrayHasKey('price', $body['errors']);
    }

    public function testUpdateProductSuccess(): void
    {
        $result = $this->withBodyFormat('json')->put('api/products/1', [
            'price' => 135000,
            'stock' => 40,
        ]);

        $result->assertOK();

        $body = $this->decode($result);
        $this->assertEquals(135000, $body['data']['price']);
        $this->assertSame('40', (string) $body['data']['stock']);

        $this->seeInDatabase('products', [
            'id'    => 1,
            'price' => 135000,
            'stock' => 40,
        ]);
    }

    public function testUpdateProductNotFound(): void
    {
        $result = $this->withBodyFormat('json')->put('api/products/9999', ['stock' => 10]);

        $result->assertStatus(404);
    }

    public function testDeleteProductSuccess(): void
    {
        $result = $this->delete('api/products/1');

        $result->assertOK();

        $body = $this->decode($result);
        $this->assertSame('Product deleted successfully', $body['message']);

        $this->dontSeeInDatabase('products', ['id' => 1]);
    }
}
