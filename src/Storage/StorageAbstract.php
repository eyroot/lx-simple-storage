<?php

namespace Lx\Storage;

abstract class StorageAbstract
{
	/**
	 * @var string
	 */
	protected $spaceName;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @param string $spaceName
	 * @param array $options
	 */
	public function __construct($spaceName, $options = array())
	{
		$this->spaceName = $spaceName;
		$this->options = $options;
	}
}

