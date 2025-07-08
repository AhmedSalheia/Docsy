<?php

namespace Ahmedsalheia\Docsy\Tests\Unit;

use Ahmedsalheia\Docsy\DocsyParam;
use Ahmedsalheia\Docsy\DocsyRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocsyRequestTest extends TestCase
{
    public function test__construct()
    {
        $request = new DocsyRequest('get', '/api/user/data?sort=-age', "Get User Date","Get Logged In User Data", requires_auth: true);

        $this->assertInstanceOf(DocsyRequest::class, $request);
        $this->assertEquals('get', $request->method);
        $this->assertEquals('/api/user/data', $request->uri);
        $this->assertEquals("Get User Date", $request->name);
        $this->assertEquals("Get Logged In User Data", $request->description);
        $this->assertNull($request->getParent());
        $this->assertIsArray($request->headers);
        $this->assertArrayHasKey('Authorization', $request->headers);
        $this->assertIsArray($request->queryParams);
        $this->assertArrayHasKey('sort', $request->queryParams);
        $this->assertInstanceOf(DocsyParam::class, $request->queryParams['sort']);
        $this->assertEquals('-age', $request->queryParams['sort']->example);
    }
}
