<?php

namespace Lx\Storage;

use Lx\Storage\StorageException;
use Ls\Storage\StorageInterface;

class Factory
{
	const TYPE_JSON = 'json';
	
	/*
	 * @var array
	 */
	private static $storageTypes = array(
		self::TYPE_JSON
	);

	/**
	 * @param string $storageType
	 * @param string $spaceName
	 * @return StorageInterface
	 */
	public static function create($storageType, $spaceName, $options = array())
	{
		if (!in_array(strtolower($storageType), self::$storageTypes, true)) {
			throw new StorageException('Unsupported storage type: ' . $storageType);
		}
		
		$className = 'Lx\\Storage\\Type\\' . ucwords(strtolower($storageType));
		return new $className($spaceName, $options);
	}
}

