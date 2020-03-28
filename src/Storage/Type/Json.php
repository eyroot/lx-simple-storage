<?php

namespace Lx\Storage\Type;

use Lx\Storage\StorageInterface;
use Lx\Storage\StorageAbstract;
use Lx\Storage\Support;

class Json extends StorageAbstract implements StorageInterface
{
	/**
	 * {@inheritDoc}
	 * @see \Lx\Storage\StorageInterface::init()
	 */
	public function init()
	{
		// set default options
		if (!isset($this->options[StorageAbstract::AUTOINCREMENT_ID])) {
			$this->options[StorageAbstract::AUTOINCREMENT_ID] = false;
		}

		// check required options
		$this->checkRequiredIdOption();
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	public function insert($data)
	{
		// autoincrement support
		if ($this->isAutoincrement()) {
			$data[$this->options[StorageAbstract::FIELD_ID]] = $this->getNextId();
		}

		// check field id is present
		if (!isset($data[$this->options[StorageAbstract::FIELD_ID]])) {
			throw new JsonException('Data does not contain id field named '
				. $this->options[StorageAbstract::FIELD_ID]);
		}

		// check field id doesn't already exist (prevent duplicates)
		$itemWithSameId = $this->getById($data[$this->options[StorageAbstract::FIELD_ID]]);
		if (null !== $itemWithSameId) {
			throw new JsonException('Item already exists with id '
				. $data[$this->options[StorageAbstract::FIELD_ID]]);
		}

		$stored = $this->readFromFile();
		$stored[$data[$this->options[StorageAbstract::FIELD_ID]]] = $data;
		return $this->writeToFile($stored);
	}

	/**
	 * @param array $data
	 * @param int $id
	 * @return bool
	 */
	public function update($data, $id)
	{
		if (isset($data[$this->options[StorageAbstract::FIELD_ID]])) {
			throw new JsonException('Data MUST not contain the id field name '
				. $this->options[StorageAbstract::FIELD_ID]);
		}

		$item = $this->getById($id);

		if (!(is_array($item) && !empty($item))) {
			// item does not exist
			return false;
		}

		foreach ($data as $fieldName => $fieldValue) {
			$item[$fieldName] = $fieldValue;
		}

		$stored = $this->readFromFile();
		$stored[$id] = $item;

		return $this->writeToFile($stored);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete($id)
	{
		$stored = $this->readFromFile();
		if (isset($stored[$id])) {
			unset($stored[$id]);
			return $this->writeToFile($stored);
		}
		return false;
	}

	/**
	 * @param int $id
	 * @return null|array|mixed
	 */
	public function getById($id)
	{
		$stored = $this->readFromFile();
		if (isset($stored[$id])) {
			return $stored[$id];
		}
		return null;
	}

	/**
	 * @param array $where - ex: ['name' => 'item 1']
	 * @param array $sort - ex: ['name', 'asc', SORT_STRING]
	 * @param array $limit - ex: [1, 2]
	 * @return array
	 */
	public function getList($where = array(), $sort = array(), $limit = array())
	{
		$stored = $this->readFromFile();

		// where condition
		if (count($where) > 0) {
			$result = array();
			foreach ($stored as $key => $item) {
				foreach ($where as $name => $value) {
					if (!(isset($item[$name]) && $value === $item[$name])) {
						continue 2;
					}
				}
				$result[] = $item;
			}
			$stored = $result;
		}

		// sort
		if (isset($sort[0])) {
			$sortField = $sort[0];
			$sortDirection = isset($sort[1]) ? $sort[1] : 'asc';
			$sortType = isset($sort[2]) ? $sort[2] : SORT_STRING;
			$stored = Support::arraySort($stored, $sortField, $sortType, $sortDirection);
		}

		// limit
		if (isset($limit[0]) && isset($limit[1])) {
			$offset = $limit[0];
			$length = $limit[1];
			$stored = array_slice($stored, $offset, $length);
		}

		return $stored;
	}

	/**
	 * @param array $data
	 * @return string
	 */
	private function encode($data)
	{
		return json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @param string $data
	 * @param bool $assoc
	 * @return array
	 */
	private function decode($data, $assoc = true)
	{
		return json_decode($data, $assoc);
	}

	/**
	 * @return string
	 * @throws JsonException
	 */
	private function getFilePath()
	{
		if (!isset($this->options['path'])) {
			throw new JsonException('Storage path is not set via options');
		}
		if (!is_dir($this->options['path'])) {
			throw new JsonException('Storage path is not a directory: ' . $this->options['path']);
		}
		if (!(strlen($this->spaceName) > 0)) {
			throw new JsonException('Space name is empty');
		}
		return $this->options['path'] . '/' . $this->spaceName . '.json';
	}

	/**
	 * @return array|mixed
	 */
	private function readFromFile()
	{
		$file = $this->getFilePath();
		if (!is_file($file)) {
			// read from inexistent file
			return array();
		}
		$content = trim(file_get_contents($file));
		if (!(strlen($content) > 0 )) {
			// read from empty file
			return array();
		}
		return $this->decode($content);
	}

	/**
	 * @param array|mixed $data
	 * @return bool
	 */
	private function writeToFile($data)
	{
		return file_put_contents($this->getFilePath(), $this->encode($data))
			? true : false;
	}

	/**
	 * @return bool
	 */
	public function deleteFile()
	{
		return is_file($this->getFilePath())
			? unlink($this->getFilePath()) : true;
	}

	/**
	 * @return bool
	 */
	public function clean()
	{
		return $this->writeToFile(array());
	}

	/**
	 * @throws JsonException
	 */
	private function checkRequiredIdOption()
	{
		if (!isset($this->options[StorageAbstract::FIELD_ID])) {
			throw new JsonException('Id field name is not set via options');
		}
	}

	/**
	 * @return bool
	 */
	private function isAutoincrement()
	{
		return $this->options[StorageAbstract::AUTOINCREMENT_ID];
	}

	/**
	 * @return int
	 */
	private function getNextId()
	{
		$stored = $this->readFromFile();
		if (count($stored) > 0) {
			return max(array_keys($stored)) + 1;
		}
		return 1;
	}
}

