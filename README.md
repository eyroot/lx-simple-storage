# lx-simple-storage

Storage solution with JSON and PHP.

## Usage

### Basic

```php
use Lx\Storage\Factory as StorageFactory;
use Lx\Storage\StorageAbstract;

// initialize storage of type json
$storage = StorageFactory::create(
	StorageFactory::TYPE_JSON, 'items', array(
		'path' => '/path/directory/storage/json',
		StorageAbstract::FIELD_ID => 'id' // name of the id field
	)
);

// insert items
$storage->insert(array(
	'id' => 1,
	'title' => 'item 1'
));
$storage->insert(array(
	'id' => 2,
	'title' => 'item 2'
));

// get items by id
$item1 = $storage->getById(1);
$item2 = $storage->getById(2);

// update item with id 2
$storage->update(array('title' => 'item 2 title updated'), 2);

// retrieve items
$list = $storage->getList();

// delete item with id 1
$storage->delete(1);

```

### Autoincrement ids feature enabled

```php
use Lx\Storage\Factory as StorageFactory;
use Lx\Storage\StorageAbstract;

// initialize storage of type json
$storage = StorageFactory::create(
	StorageFactory::TYPE_JSON, 'items', array(
		'path' => '/path/directory/storage/json',
		StorageAbstract::FIELD_ID => 'id' // name of the id field,
		StorageAbstract::AUTOINCREMENT_ID => true
	)
);

// insert items
$storage->insert(array(
	'title' => 'item 1'
));
$storage->insert(array(
	'title' => 'item 2'
));

// get items by id
$item1 = $storage->getById(1);
$item2 = $storage->getById(2);

```

## Development set-up

* Clone project locally:

```
git clone https://github.com/eyroot/lx-simple-storage lx-simple-storage
cd lx-simple-storage
```

* Set-up project and install composer deps:

```
composer install
```

* Run unit testing:
! in case you get the "Error: No code coverage driver is available" when running the test suite, remember to install and enable the php-xdebug extension !

```
cd testing/
mkdir data-storage
../vendor/bin/phpunit
```

* Check the code coverage of tests by opening in browser:

```
file:///tmp/coverage-lx-simple-storage/index.html
```

