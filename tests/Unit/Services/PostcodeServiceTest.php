<?php

namespace Tests\Unit\Services;

use App\Services\PostcodeService;
use Tests\TestCase;

class PostcodeServiceTest extends TestCase
{
    private PostcodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PostcodeService();
    }

    public function test_valid_postcodes(): void
    {
        $this->assertTrue($this->service->isValidPostcode('1234AB'));
        $this->assertTrue($this->service->isValidPostcode('1234 AB'));
        $this->assertTrue($this->service->isValidPostcode('9999ZZ'));
        $this->assertTrue($this->service->isValidPostcode('1000aa'));
    }

    public function test_invalid_postcodes(): void
    {
        $this->assertFalse($this->service->isValidPostcode('0123AB'));  // Starts with 0
        $this->assertFalse($this->service->isValidPostcode('12345'));   // No letters
        $this->assertFalse($this->service->isValidPostcode('ABCD12')); // Wrong format
        $this->assertFalse($this->service->isValidPostcode(''));        // Empty
        $this->assertFalse($this->service->isValidPostcode(null));     // Null
        $this->assertFalse($this->service->isValidPostcode('123AB'));   // Too few digits
    }

    public function test_normalize_postcode_removes_spaces(): void
    {
        $this->assertEquals('1234AB', $this->service->normalizePostcode('1234 AB'));
        $this->assertEquals('1234AB', $this->service->normalizePostcode('1234AB'));
    }

    public function test_normalize_postcode_uppercases(): void
    {
        $this->assertEquals('1234AB', $this->service->normalizePostcode('1234ab'));
        $this->assertEquals('1234AB', $this->service->normalizePostcode('1234 ab'));
    }

    public function test_lookup_returns_null_for_invalid_postcode(): void
    {
        $result = $this->service->lookup('0000XX', '1');

        $this->assertNull($result);
    }
}
