<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Http\Response;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
    public function testClass(): void
    {
        $response = new Response('Hello', 200);
        $this->assertEquals('Hello', $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
