<?php

require_once('BaseTModels.class.php');

class Class_Model_TReminders extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	protected $_name   = 't_reminders';

	public function selectFromStatus($status)
	{
		$select = $this->select();
		$select->where('status = ?', $status);
		return $this->fetchAll($select);
	}

	public function selectFromReserveId($t_reserve_id)
	{
		$select = $this->select();
		$select->where('t_reserve_id = ?', $t_reserve_id);
		return $this->fetchRow($select);
	}

	public function deleteFromReserveId($t_reserve_id)
	{
		$where = $this->getAdapter()->quoteInto('t_reserve_id = ?' , $t_reserve_id);

		return $this->delete($where);
	}

}

