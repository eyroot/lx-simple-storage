<?php

use PHPUnit\Framework\TestCase;

use Lx\Storage\Factory as StorageFactory;
use Lx\Storage\StorageAbstract;
use Lx\Storage\Type\Json as StorageTypeJson;

class JsonAutoincrementTest extends TestCase
{
	/**
	 * @var StorageTypeJson
	 */
	private $storage;

	public function setUp()
	{
		$this->storage = StorageFactory::create(
			StorageFactory::TYPE_JSON, 'items', array(
				'path' => TESTING_PATH_STORAGE,
				StorageAbstract::FIELD_ID => 'id',
				StorageAbstract::AUTOINCREMENT_ID => true
			)
		);
		$this->storage->clean();
	}

	public function tearDown()
	{
		$this->storage->deleteFile();
	}

	public function testAutoincrement()
	{
		$this->storage->insert(array(
			'title' => 'autoincrement item 1'
		));
		$this->storage->insert(array(
			'title' => 'autoincrement item 2'
		));
		$this->storage->insert(array(
			'title' => 'autoincrement item 3'
		));

		$list = $this->storage->getList();
		$this->assertEquals(3, count($list));
		$this->assertEquals('autoincrement item 1', $list[1]['title']);
		$this->assertEquals('autoincrement item 2', $list[2]['title']);
		$this->assertEquals('autoincrement item 3', $list[3]['title']);

		$this->storage->delete(2);
		$this->assertEquals(2, count($this->storage->getList()));

		$this->storage->insert(array(
			'title' => 'autoincrement item 4'
		));
		$list = $this->storage->getList();
		$this->assertEquals(3, count($list));
		$this->assertEquals('autoincrement item 1', $list[1]['title']);
		$this->assertEquals('autoincrement item 3', $list[3]['title']);
		$this->assertEquals('autoincrement item 4', $list[4]['title']);

		$item3 = $this->storage->getById(3);
		$this->assertEquals('autoincrement item 3', $item3['title']);
	}
}
