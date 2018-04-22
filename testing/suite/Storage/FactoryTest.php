<?php

use PHPUnit\Framework\TestCase;

use Lx\Storage\Factory as StorageFactory;
use Lx\Storage\StorageAbstract;
use Lx\Storage\Type\Json as StorageTypeJson;
use Lx\Storage\StorageException;

class FactoryTest extends TestCase
{
	public function testCreate()
	{
		$storage = StorageFactory::create(
			StorageFactory::TYPE_JSON,
			'items',
			array(StorageAbstract::FIELD_ID => 'id')
		);
		$this->assertInstanceOf(StorageTypeJson::class, $storage);
	}

	public function testCreateException()
	{
		$this->expectException(StorageException::class);
		$this->expectExceptionMessage('Unsupported storage type');
		StorageFactory::create(
			'unknown_type',
			'items',
			array(StorageAbstract::FIELD_ID => 'id')
		);
	}
}

