<?php

use PHPUnit\Framework\TestCase;

use Lx\Storage\Factory as StorageFactory;
use Lx\Storage\StorageAbstract;
use Lx\Storage\Type\Json as StorageTypeJson;
use Lx\Storage\Type\JsonException;

class JsonTest extends TestCase
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
				StorageAbstract::FIELD_ID => 'id'
			)
		);
		$this->storage->clean();
	}

	public function tearDown()
	{
		$this->storage->deleteFile();
	}

	public function testInsert()
	{
		$this->storage->insert(array(
			'id' => 1,
			'title' => 'item 1'
		));
		$this->storage->insert(array(
			'id' => 2,
			'title' => 'item 2'
		));

		$item = $this->storage->getById(1);
		$this->assertEquals('item 1', $item['title']);
		$item = $this->storage->getById(2);
		$this->assertEquals('item 2', $item['title']);
	}

	public function testInsertException()
	{
		$this->expectException(JsonException::class);
		$this->expectExceptionMessage('Data does not contain id field named');
		$this->storage->insert(array(
			'title' => 'item 1'
		));
	}

	public function testInsertExceptionDuplicateId()
	{
		$this->storage->insert(array(
			'id' => 1,
			'title' => 'item 1'
		));
		$this->expectException(JsonException::class);
		$this->expectExceptionMessage('Item already exists with id');
		$this->storage->insert(array(
			'id' => 1,
			'title' => 'item 2'
		));
	}

	public function testUpdate()
	{
		$this->storage->insert(array(
			'id' => 1,
			'title' => 'item 1'
		));
		$this->storage->insert(array(
			'id' => 2,
			'title' => 'item 2'
		));

		$item = $this->storage->getById(1);
		$this->assertEquals('item 1', $item['title']);
		$item = $this->storage->getById(2);
		$this->assertEquals('item 2', $item['title']);

		$this->storage->update(array('title' => 'item 2 updated'), 2);
		$item = $this->storage->getById(2);
		$this->assertEquals('item 2 updated', $item['title']);
		$this->storage->update(array('title' => 'item 1 updated'), 1);
		$item = $this->storage->getById(1);
		$this->assertEquals('item 1 updated', $item['title']);

		// inexistent item
		$this->assertFalse($this->storage->update(array(), 3));
	}

	public function testUpdateException()
	{
		$this->storage->insert(array(
			'id' => 1,
			'title' => 'item 1'
		));

		$this->expectException(JsonException::class);
		$this->expectExceptionMessage('Data MUST not contain the id field name');
		$this->storage->update(array(
			'id' => 2,
			'title' => 'item 1'
		), 1);
	}

	public function testDelete()
	{
		$this->storage->insert(array(
			'id' => 1,
			'title' => 'item 1'
		));
		$this->storage->insert(array(
			'id' => 2,
			'title' => 'item 2'
		));

		$item = $this->storage->getById(1);
		$this->assertEquals('item 1', $item['title']);
		$item = $this->storage->getById(2);
		$this->assertEquals('item 2', $item['title']);

		$this->storage->delete(1);
		$this->assertNull($this->storage->getById(1));
		$this->assertNotNull($this->storage->getById(2));
		$this->storage->delete(2);
		$this->assertNull($this->storage->getById(2));

		$this->assertFalse($this->storage->delete(3));
	}

	public function testGetList()
	{
		$this->storage->insert(array(
			'id' => 1,
			'title' => 'item 1'
		));
		$this->storage->insert(array(
			'id' => 2,
			'title' => 'item 2'
		));

		$list = $this->storage->getList();
		$this->assertTrue(is_array($list));
		$this->assertEquals(2, count($list));
		$this->assertEquals(2, $list[2]['id']);
	}

	public function testGetListConditions()
	{
	    $this->storage->insert(array(
	        'id' => 1,
	        'title' => 'item 1'
	    ));
	    $this->storage->insert(array(
	        'id' => 2,
	        'title' => 'item 2'
	    ));

	    // test where condition
	    $list = $this->storage->getList(
	        array('title' => 'item 2')
	    );
	    $this->assertTrue(is_array($list));
	    $this->assertEquals(1, count($list));
	    $this->assertEquals(2, $list[0]['id']);

	    // test sorting
	    $list = $this->storage->getList(
	        array(),
	        array('title', 'desc', SORT_STRING)
	    );
	    $this->assertTrue(is_array($list));
	    $this->assertEquals(2, count($list));
	    $this->assertEquals(2, $list[0]['id']);
	    $this->assertEquals('item 2', $list[0]['title']);
	    $this->assertEquals(1, $list[1]['id']);
	    $this->assertEquals('item 1', $list[1]['title']);

	    // test limit
	    $list = $this->storage->getList(
	        array(), array(), array(1, 1)
	    );
	    $this->assertTrue(is_array($list));
	    $this->assertEquals(1, count($list));
	    $this->assertEquals(2, $list[0]['id']);
	    $this->assertEquals('item 2', $list[0]['title']);
	}

	public function testExceptionId()
	{
		$this->expectException(JsonException::class);
		$this->expectExceptionMessage('Id field name is not set via options');
		$storage = StorageFactory::create(StorageFactory::TYPE_JSON, 'items', array());
		$storage->getById(1);
	}

	public function testExceptionPath()
	{
		$this->expectException(JsonException::class);
		$this->expectExceptionMessage('Storage path is not set via options');
		$storage = StorageFactory::create(StorageFactory::TYPE_JSON, 'items', array(
			StorageAbstract::FIELD_ID => 'id'
		));
		$storage->insert(array(
			'id' => 1,
			'title' => 'item 1'
		));
	}

	public function testExceptionPathNotDir()
	{
		$this->expectException(JsonException::class);
		$this->expectExceptionMessage('Storage path is not a directory');
		$storage = StorageFactory::create(StorageFactory::TYPE_JSON, 'items', array(
			StorageAbstract::FIELD_ID => 'id',
			'path' => 'dirnotfound'
		));
		$storage->insert(array(
			'id' => 1,
			'title' => 'item 1'
		));
	}

	public function testExceptionSpaceEmpty()
	{
		$this->expectException(JsonException::class);
		$this->expectExceptionMessage('Space name is empty');
		$storage = StorageFactory::create(StorageFactory::TYPE_JSON, '', array(
			StorageAbstract::FIELD_ID => 'id',
			'path' => TESTING_PATH_STORAGE
		));
		$storage->insert(array(
			'id' => 1,
			'title' => 'item 1'
		));
	}

	public function testDeleteFile()
	{
		$this->assertFileExists(TESTING_PATH_STORAGE . '/items.json');
		$this->storage->deleteFile();
		$this->assertFileNotExists(TESTING_PATH_STORAGE . '/items.json');
	}

	public function testReadFromFile()
	{
		// read from inexistent file
		$this->storage->deleteFile();
		$this->assertFileNotExists(TESTING_PATH_STORAGE . '/items.json');
		$this->assertEquals(array(), $this->storage->getList());

		// read from empty file
		file_put_contents(TESTING_PATH_STORAGE . '/items.json', '');
		$this->assertEquals(array(), $this->storage->getList());
	}
}

