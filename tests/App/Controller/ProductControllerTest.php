<?php

declare(strict_types = 1);

namespace App\Tests\App\Controller;

use App\Enum\ProductTypeEnum;
use App\Enum\ProductUnitEnum;
use App\Service\UnitConverter;
use App\Factory\ProductFactory;
use App\DataFixtures\ProductFixtures;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Exception\InvalidProductTypeException;
use App\Exception\InvalidProductUnitException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class ProductControllerTest extends WebTestCase
{
    use ResetDatabase;

    protected $client;
    protected AbstractDatabaseTool $databaseTool;

    public function setUp(): void
    {
        parent::setUp();

        $this->client        = static::createClient();
        $this->databaseTool  = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    private function makeRequest(string $type, string $endPoint, array $data = [], int $status = Response::HTTP_OK): array
    {
        $client = $this->client;

        $client->request($type, $endPoint, content: json_encode($data));

        $response = $client->getResponse();

        $this->assertEquals($status, $response->getStatusCode());

        $responseData = $response->getContent();

        $this->assertIsString($responseData);

        return json_decode($responseData, true);
    }

    public function test_index()
    {
        $this->databaseTool->loadFixtures([ProductFixtures::class]);

        $responseData = $this->makeRequest(
            'GET',
            '/api/products'
        );

        $data = $responseData['data'];

        $this->assertTrue($responseData['success']);
        $this->assertNotEmpty($data);
        $this->assertEquals([
            'id'       => 1,
            'name'     => 'Carrot',
            'type'     => 'vegetable',
            'quantity' => 10922,
            'unit'     => 'g',
        ], $data[0]);
    }

    public function test_index_filtering_collection_by_type()
    {
        $this->databaseTool->loadFixtures([ProductFixtures::class]);

        $type         = ProductTypeEnum::FRUIT;
        $responseData = $this->makeRequest(
            'GET',
            "/api/products?type={$type}"
        );

        $data = $responseData['data'];

        $this->assertTrue($responseData['success']);
        $this->assertNotEmpty($data);
        $this->assertEquals($type, $data[0]['type']);
    }

    public function test_index_filtering_collection_by_incorrect_type()
    {
        $this->databaseTool->loadFixtures([ProductFixtures::class]);

        $responseData = $this->makeRequest(
            'GET',
            '/api/products?type=test',
            status: Response::HTTP_INTERNAL_SERVER_ERROR
        );

        $this->assertFalse($responseData['success']);
        $this->assertEquals((new InvalidProductTypeException())->getMessage(), $responseData['message']);
    }

    public function test_index_convert_unit_to_kg()
    {
        $this->databaseTool->loadFixtures([ProductFixtures::class]);

        $convert_to   = ProductUnitEnum::KILO_GRAM;
        $responseData = $this->makeRequest(
            'GET',
            "/api/products?convert_to={$convert_to}"
        );

        $item = ProductFactory::createItem([
            'id'       => 1,
            'name'     => 'Carrot',
            'type'     => 'vegetable',
            'quantity' => 10922,
            'unit'     => 'g',
        ]);
        $item->setQuantity(UnitConverter::convert($item, $convert_to));
        $item->setUnit($convert_to);

        $data = $responseData['data'];

        $this->assertTrue($responseData['success']);
        $this->assertNotEmpty($data);

        $firstItem = $data[0];
        $this->assertEquals($item->getUnit(), $firstItem['unit']);
        $this->assertEquals($item->getQuantity(), $firstItem['quantity']);
    }

    public function test_convert_invalid_unit_throws_exception()
    {
        $item = ProductFactory::createItem([
            'id'       => 1,
            'name'     => 'Carrot',
            'type'     => 'vegetable',
            'quantity' => 10922,
            'unit'     => 'g',
        ]);

        $this->expectException(InvalidProductUnitException::class);

        UnitConverter::convert($item, 'test');
    }

    public function test_index_search()
    {
        $this->databaseTool->loadFixtures([ProductFixtures::class]);

        $responseData = $this->makeRequest(
            'GET',
            '/api/products?search=pp',
        );

        $data = $responseData['data'];

        $this->assertTrue($responseData['success']);
        $this->assertNotEmpty($data);
        $this->assertTrue(str_contains($data[0]['name'], 'pp'));
    }

    public function test_store()
    {
        $item = [
            'name'     => 'Carrot',
            'type'     => 'vegetable',
            'quantity' => 10922,
            'unit'     => 'g',
        ];
        $responseData = $this->makeRequest(
            'POST',
            '/api/products',
            $item,
            Response::HTTP_CREATED
        );

        $data = $responseData['data'];

        $this->assertTrue($responseData['success']);
        $this->assertNotEmpty($data);
        $this->assertCount(1, $data);
        $this->assertEquals($item['quantity'], $data[0]['quantity']);
    }

    public function test_product_dto_validation()
    {
        $item = [
            'name'     => 'test',
            'type'     => 'test',
            'quantity' => 1111,
            'unit'     => 'test',
        ];

        $responseData = $this->makeRequest(
            'POST',
            '/api/products',
            $item,
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $this->assertFalse($responseData['success']);
        $this->assertNotEmpty($responseData['data']['errors']);
    }

    public function test_delete()
    {
        // store new item
        $responseData = $this->makeRequest(
            'POST',
            '/api/products',
            [
                'name'     => 'Test',
                'type'     => 'vegetable',
                'quantity' => 10922,
                'unit'     => 'g',
            ],
            Response::HTTP_CREATED
        );

        $data = $responseData['data'];

        $this->assertTrue($responseData['success']);
        $this->assertNotEmpty($data);
        $this->assertCount(1, $data);
        $this->assertEquals('Test', $data[0]['name']);

        // delete the created item
        $responseData = $this->makeRequest('DELETE', '/api/products/1', status:Response::HTTP_OK);

        $this->assertTrue($responseData['success']);
        $this->assertEmpty($responseData['data']);
    }

    public function test_delete_invalid_id_throws_exception()
    {
        $responseData = $this->makeRequest(
            'DELETE',
            '/api/products/1',
            status:Response::HTTP_INTERNAL_SERVER_ERROR
        );

        $this->assertFalse($responseData['success']);
        $this->assertEmpty($responseData['data']);
        $this->assertEquals('entity not found', $responseData['message']);
    }
}
