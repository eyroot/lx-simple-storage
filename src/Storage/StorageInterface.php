<?php

namespace Lx\Storage;

interface StorageInterface
{
	/**
	 * @param array $data
	 * @return bool
	 */
	public function insert($data);
	
	/**
	 * @param array $data
	 * @param int $id
	 * @return bool
	 */
	public function update($data, $id);
	
	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete($id);
	
	/**
	 * @param int $id
	 * @return null|array|mixed
	 */
	public function getById($id);
	
	/**
	 * @param array $where
	 * @param array $sort
	 * @param array $limit
	 * @return array
	 */
	public function getList($where = array(), $sort = array(), $limit = array());
}

