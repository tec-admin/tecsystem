<?php

require_once('BaseTModels.class.php');

// t_reserves テーブルクラス
class Class_Model_TReserveComments extends BaseTModels
{
	const TABLE_NAME = 't_reserve_comments';
	protected $_name   = Class_Model_TReserveComments::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TReserveComments::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_t_reserve_id' => 't_reserve_id',
			$prefix . '_reservecomment' => 'reservecomment',

// 			$prefix . '_createdate' => 'createdate',
// 			$prefix . '_creator' => 'creator',
// 			$prefix . '_lastupdate' => 'lastupdate',
// 			$prefix . '_lastupdater' => 'lastupdater',
		);
	}

	public function selectFromReserveId($t_reserve_id)
	{
		$select = $this->select();

		$select->setIntegrityCheck(false)
				->from(
					array('reservecomments' => $this->_name), "*")

				->where('reservecomments.t_reserve_id = ?', $t_reserve_id)
				->order(array('reservecomments.id asc'));
		
		//return $this->fetchAll($select);	// とりあえずTReservesと一対一だが将来的に拡張する可能性あり
		return $this->fetchRow($select);
	}

	public function deleteFromReserveId($t_reserve_id)
	{
		$where = $this->getAdapter()->quoteInto('t_reserve_id = ?' , $t_reserve_id);

		return $this->delete($where);
	}

}
