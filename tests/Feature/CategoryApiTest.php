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
final class CategoryApiTest extends CIUnitTestCase
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

    public function testIndexReturnsPaginatedCategories(): void
    {
        $result = $this->get('api/categories');

        $result->assertOK();

        $body = $this->decode($result);
        $this->assertSame('Categories retrieved successfully', $body['message']);
        $this->assertGreaterThanOrEqual(3, $body['data']['pagination']['total']);
    }

    public function testShowReturnsCategoryDetail(): void
    {
        $result = $this->get('api/categories/1');

        $result->assertOK();

        $body = $this->decode($result);
        $this->assertSame('success', $body['status']);
        $this->assertSame('1', (string) $body['data']['id']);
    }

    public function testShowReturnsNotFoundForUnknownId(): void
    {
        $result = $this->get('api/categories/9999');

        $result->assertStatus(404);

        $body = $this->decode($result);
        $this->assertSame('error', $body['status']);
        $this->assertSame('Category not found', $body['message']);
    }

    public function testCreateCategorySuccess(): void
    {
        $result = $this->withBodyFormat('json')->post('api/categories', [
            'name'        => 'Toys',
            'description' => 'Kids toys and games',
        ]);

        $result->assertStatus(201);

        $body = $this->decode($result);
        $this->assertSame('success', $body['status']);
        $this->assertSame('Toys', $body['data']['name']);

        $this->seeInDatabase('categories', ['name' => 'Toys']);
    }

    public function testCreateCategoryValidationError(): void
    {
        $result = $this->withBodyFormat('json')->post('api/categories', [
            'name' => 'EL',
        ]);

        $result->assertStatus(422);

        $body = $this->decode($result);
        $this->assertSame('error', $body['status']);
        $this->assertArrayHasKey('name', $body['errors']);
    }

    public function testCreateCategoryDuplicateNameIsRejected(): void
    {
        $result = $this->withBodyFormat('json')->post('api/categories', [
            'name'        => 'Electronics',
            'description' => 'Duplicate of seeded category',
        ]);

        $result->assertStatus(422);

        $body = $this->decode($result);
        $this->assertArrayHasKey('name', $body['errors']);
    }

    public function testUpdateCategorySuccess(): void
    {
        $result = $this->withBodyFormat('json')->put('api/categories/2', [
            'description' => 'Updated grocery description',
        ]);

        $result->assertOK();

        $body = $this->decode($result);
        $this->assertSame('Updated grocery description', $body['data']['description']);

        $this->seeInDatabase('categories', [
            'id'          => 2,
            'description' => 'Updated grocery description',
        ]);
    }

    public function testDeleteCategoryWithoutProductsSuccess(): void
    {
        // A freshly created category has no related products yet.
        $created  = $this->withBodyFormat('json')->post('api/categories', ['name' => 'Toys']);
        $newId    = $this->decode($created)['data']['id'];

        $result = $this->delete('api/categories/' . $newId);

        $result->assertOK();

        $body = $this->decode($result);
        $this->assertSame('Category deleted successfully', $body['message']);

        $this->dontSeeInDatabase('categories', ['id' => $newId]);
    }

    public function testDeleteCategoryWithProductsIsRejected(): void
    {
        // Electronics (id 1) has seeded products.
        $result = $this->delete('api/categories/1');

        $result->assertStatus(409);

        $body = $this->decode($result);
        $this->assertSame('error', $body['status']);

        $this->seeInDatabase('categories', ['id' => 1]);
    }
}
