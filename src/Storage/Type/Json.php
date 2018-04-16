<?php

namespace Lx\Storage\Type;

use Lx\Storage\StorageInterface;
use Lx\Storage\StorageAbstract;
use Lx\Storage\Type\JsonException;

class Json extends StorageAbstract implements StorageInterface
{
	/**
	 * @param array $data
	 * @return bool
	 */
	public function insert($data)
	{
		$this->checkRequiredIdOption();

		$stored = $this->readFromFile();
		$stored[] = $data;
		return $this->writeToFile($stored);
	}
	
	/**
	 * @param array $data
	 * @param int $id
	 * @return bool
	 */
	public function update($data, $id)
	{
		$this->checkRequiredIdOption();

		$stored = $this->readFromFile();
		foreach ($stored as $k => $item) {
			if ($item[$this->options['id']] === $id) {
				foreach ($data as $fieldName => $fieldValue) {
					$stored[$k][$fieldName] = $fieldValue;
				}
			}
		}
		return $this->writeToFile($stored);
	}
	
	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete($id)
	{
		$this->checkRequiredIdOption();

		$stored = $this->readFromFile();
		foreach ($stored as $k => $item) {
			if ($item[$this->options['id']] === $id) {
				unset($stored[$k]);
			}
		}
		return $this->writeToFile($stored);
	}
	
	/**
	 * @param int $id
	 * @return null|array|mixed
	 */
	public function getById($id)
	{
		$this->checkRequiredIdOption();

		foreach ($this->readFromFile() as $item) {
			if ($item[$this->options['id']] === $id) {
				return $item;
			}
		}
		return null;
	}
	
	/**
	 * @param array $where
	 * @param array $sort
	 * @param array $limit
	 * @return array
	 */
	public function getList($where = array(), $sort = array(), $limit = array())
	{
		$stored = $this->readFromFile();
		
		//@TODO: implement support for additional arguments
		
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
	 * @param book $assoc
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
		if (!isset($this->options['id'])) {
			throw new JsonException('Id field name is not set via options');
		}
	}
}

