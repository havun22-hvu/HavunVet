<?php

namespace Tests\Unit\Services;

use App\Services\PostcodeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PostcodeServiceFullTest extends TestCase
{
    private PostcodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PostcodeService();
    }

    // --- normalizePostcode ---

    public function test_normalize_postcode_removes_spaces(): void
    {
        $this->assertEquals('1234AB', $this->service->normalizePostcode('1234 AB'));
    }

    public function test_normalize_postcode_uppercases(): void
    {
        $this->assertEquals('1234AB', $this->service->normalizePostcode('1234ab'));
    }

    // --- isValidPostcode ---

    public function test_valid_postcode(): void
    {
        $this->assertTrue($this->service->isValidPostcode('1234AB'));
        $this->assertTrue($this->service->isValidPostcode('1234 AB'));
        $this->assertTrue($this->service->isValidPostcode('9999zz'));
    }

    public function test_invalid_postcode_starting_with_zero(): void
    {
        $this->assertFalse($this->service->isValidPostcode('0123AB'));
    }

    public function test_invalid_postcode_empty(): void
    {
        $this->assertFalse($this->service->isValidPostcode(''));
        $this->assertFalse($this->service->isValidPostcode(null));
    }

    public function test_invalid_postcode_wrong_format(): void
    {
        $this->assertFalse($this->service->isValidPostcode('ABCD12'));
        $this->assertFalse($this->service->isValidPostcode('12345'));
    }

    // --- lookup ---

    public function test_lookup_with_invalid_postcode_returns_null(): void
    {
        $result = $this->service->lookup('INVALID', '10');
        $this->assertNull($result);
    }

    public function test_lookup_successful(): void
    {
        Cache::flush();

        Http::fake([
            'api.pdok.nl/*' => Http::response([
                'response' => [
                    'docs' => [[
                        'straatnaam' => 'Hoofdstraat',
                        'huisnummer' => 10,
                        'huisletter' => null,
                        'huisnummertoevoeging' => null,
                        'postcode' => '1234AB',
                        'woonplaatsnaam' => 'Amsterdam',
                        'gemeentenaam' => 'Amsterdam',
                        'provincienaam' => 'Noord-Holland',
                        'centroide_ll' => 'POINT(4.8952 52.3702)',
                    ]],
                ],
            ]),
        ]);

        $result = $this->service->lookup('1234AB', '10');

        $this->assertNotNull($result);
        $this->assertEquals('Hoofdstraat', $result['street']);
        $this->assertEquals('Amsterdam', $result['city']);
        $this->assertEquals('Noord-Holland', $result['province']);
        $this->assertNotNull($result['latitude']);
        $this->assertNotNull($result['longitude']);
        $this->assertNotNull($result['full_address']);
    }

    public function test_lookup_no_results_returns_null(): void
    {
        Cache::flush();

        Http::fake([
            'api.pdok.nl/*' => Http::response([
                'response' => ['docs' => []],
            ]),
        ]);

        $result = $this->service->lookup('1234AB', '999');
        $this->assertNull($result);
    }

    public function test_lookup_api_failure_returns_null(): void
    {
        Cache::flush();

        Http::fake([
            'api.pdok.nl/*' => Http::response([], 500),
        ]);

        $result = $this->service->lookup('1234AB', '10');
        $this->assertNull($result);
    }

    public function test_lookup_api_exception_returns_null(): void
    {
        Cache::flush();

        Http::fake([
            'api.pdok.nl/*' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        $result = $this->service->lookup('1234AB', '10');
        $this->assertNull($result);
    }

    public function test_lookup_caches_result(): void
    {
        Cache::flush();

        Http::fake([
            'api.pdok.nl/*' => Http::response([
                'response' => [
                    'docs' => [[
                        'straatnaam' => 'Teststraat',
                        'huisnummer' => 1,
                        'postcode' => '1234AB',
                        'woonplaatsnaam' => 'Test',
                    ]],
                ],
            ]),
        ]);

        // First call hits API
        $result1 = $this->service->lookup('1234AB', '1');
        // Second call should use cache
        $result2 = $this->service->lookup('1234AB', '1');

        $this->assertEquals($result1, $result2);
        Http::assertSentCount(1);
    }

    // --- getDistance ---

    public function test_get_distance_between_two_addresses(): void
    {
        Cache::flush();

        Http::fake([
            'api.pdok.nl/*' => Http::sequence()
                ->push([
                    'response' => [
                        'docs' => [[
                            'straatnaam' => 'Straat A',
                            'huisnummer' => 1,
                            'postcode' => '1000AA',
                            'woonplaatsnaam' => 'A',
                            'centroide_ll' => 'POINT(4.8952 52.3702)',
                        ]],
                    ],
                ])
                ->push([
                    'response' => [
                        'docs' => [[
                            'straatnaam' => 'Straat B',
                            'huisnummer' => 1,
                            'postcode' => '2000BB',
                            'woonplaatsnaam' => 'B',
                            'centroide_ll' => 'POINT(4.4777 51.9244)',
                        ]],
                    ],
                ]),
        ]);

        $distance = $this->service->getDistance('1000AA', '1', '2000BB', '1');

        $this->assertNotNull($distance);
        $this->assertIsFloat($distance);
        $this->assertGreaterThan(0, $distance);
    }

    public function test_get_distance_returns_null_when_address_not_found(): void
    {
        Cache::flush();

        Http::fake([
            'api.pdok.nl/*' => Http::response([
                'response' => ['docs' => []],
            ]),
        ]);

        $distance = $this->service->getDistance('1234AB', '1', '5678CD', '1');
        $this->assertNull($distance);
    }

    // --- formatAddress (tested via lookup) ---

    public function test_lookup_formats_address_with_letter_and_addition(): void
    {
        Cache::flush();

        Http::fake([
            'api.pdok.nl/*' => Http::response([
                'response' => [
                    'docs' => [[
                        'straatnaam' => 'Kerkstraat',
                        'huisnummer' => 10,
                        'huisletter' => 'A',
                        'huisnummertoevoeging' => 'II',
                        'postcode' => '1234AB',
                        'woonplaatsnaam' => 'Utrecht',
                    ]],
                ],
            ]),
        ]);

        $result = $this->service->lookup('1234AB', '10');
        $this->assertStringContainsString('Kerkstraat', $result['full_address']);
        $this->assertStringContainsString('10A', $result['full_address']);
        $this->assertStringContainsString('II', $result['full_address']);
    }

    // --- extractLat/extractLon (tested via lookup) ---

    public function test_lookup_without_coordinates(): void
    {
        Cache::flush();

        Http::fake([
            'api.pdok.nl/*' => Http::response([
                'response' => [
                    'docs' => [[
                        'straatnaam' => 'Test',
                        'huisnummer' => 1,
                        'postcode' => '1234AB',
                        'woonplaatsnaam' => 'Test',
                    ]],
                ],
            ]),
        ]);

        $result = $this->service->lookup('1234AB', '1');
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
    }
}
