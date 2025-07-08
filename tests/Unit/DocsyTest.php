<?php

namespace Ahmedsalheia\Docsy\Tests\Unit;

use Ahmedsalheia\Docsy\Docsy;
use Ahmedsalheia\Docsy\DocsyCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocsyTest extends TestCase
{
    #[Test]
    public function test__getCollection(): void
    {
        $docsy = (new Docsy())->addCollection('Named_Collection');

        $this->assertInstanceOf(DocsyCollection::class, $docsy->collection('Named_Collection'));
    }
    #[Test]
    public function test__addDefaultCollection(): void
    {
        $default_collection_name = config('docsy.default_collection.name');
        $docsy = new Docsy();

        $this->assertInstanceOf(Docsy::class, $docsy);
        $this->assertInstanceOf(DocsyCollection::class, $docsy->collection());
        $this->assertArrayHasKey($default_collection_name, $docsy->collections());

    }

    #[Test]
    public function test__addNamedCollection(): void
    {
        $docsy = (new Docsy())->addCollection('Named_Collection');

        $this->assertArrayHasKey('Named_Collection', $docsy->collections());
    }

    #[Test]
    public function test__addDuplicatedCollection(): void
    {
        $docsy = (new Docsy())->addCollection('Named_Collection');

        $this->assertCount(2, $docsy->collections());
    }

    #[Test]
    public function test__returnNullOnNotExistingCollection(): void
    {
        $docsy = new Docsy();

        $this->assertNull($docsy->collection('NotExisting_Collection'));
    }

    public function test__getAllCollections(): void
    {
        $default_collection_name = config('docsy.default_collection.name');
        $docsy = (new Docsy())->addCollection('Named_Collection');

        $this->assertIsArray($docsy->collections());
        $this->assertCount(2, $docsy->collections());
        $this->assertInstanceOf(DocsyCollection::class, array_values($docsy->collections())[0]);
        $this->assertArrayHasKey($default_collection_name, $docsy->collections());
    }
}