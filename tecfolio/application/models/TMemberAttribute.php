<?php

require_once('BaseTModels.class.php');

class Class_Model_TMemberAttribute extends BaseTModels
{
	const TABLE_NAME = 't_member_attribute';
	protected $_name = Class_Model_TMemberAttribute::TABLE_NAME;

	public static function fieldArray($prefix = "")
	{
		if ($prefix == '')
			$prefix = Class_Model_MMembers::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			$prefix . '_roles' => 'roles',
		);
	}

	public function selectAllUser()
	{
		$select = $this->select();
		$select->order('id asc');
		return $this->fetchAll($select);
	}
}

