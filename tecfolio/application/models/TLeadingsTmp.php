<?php

require_once('BaseTModels.class.php');

// t_leadingss テーブルクラス
class Class_Model_TLeadingsTmp extends BaseTModels
{
	const TABLE_NAME = 't_leadings_tmp';
	protected $_name   = Class_Model_TLeadingsTmp::TABLE_NAME;

	private function setJoinField($select)
	{
		$select
				->joinLeft(
					array('reserves' => 't_reserves'),
					'leadings.t_reserve_id = reserves.id',
					Class_Model_TReserves::fieldArray()
				)

				->joinLeft(
					array('shifts' => 'm_shifts'),
					'shifts.id = reserves.m_shift_id',
					Class_Model_MShifts::fieldArray()
				)

				->joinLeft(
					array('dockinds' => 'm_dockinds'),
					'dockinds.id = shifts.m_dockind_id',
					Class_Model_MDockinds::fieldArray()
				)

				->joinLeft(
					array('places' => 'm_places'),
					'places.id = shifts.m_place_id',
					Class_Model_MPlaces::fieldArray()
				)

				->joinLeft(
					array('members' => 'm_members'),
					'leadings.m_member_id_charge = members.id',
					Class_Model_MMembers::fieldArray()
				)
		;

		return $select;
	}

	// 指定の予約者のオブジェクトですべて取得
	public function GetSelectFromMemberId($m_member_id, $limit=0, $order=array())
	{
		$select = $this->select()->setIntegrityCheck(false)
				->from(array('leadings' => $this->_name), '*')

				->where('reserves.m_member_id_reserver = ?', $m_member_id);

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->setJoinField($select);
	}

	public function selectFromMemberId($m_member_id, $limit=0, $order=array())
	{
		$select = $this->GetSelectFromMemberId($m_member_id, $limit, $order);
		return $this->fetchAll($select);
	}
	

	// 指定の予約IDを持つオブジェクトですべて取得
	public function selectFromReserveId($t_reserve_id)
	{
		$select = $this->select();

		$select->setIntegrityCheck(false)
				->from(array('leadings' => $this->_name), '*')

				->where('leadings.t_reserve_id = ?', $t_reserve_id);

		return $this->fetchRow($this->setJoinField($select));
	}
	
	// 指定の予約IDを持つオブジェクトで指導コメント入力済みの行を取得
	public function selectInputCommentFromReserveId($t_reserve_id)
	{
		$select = $this->select();
	
		$select->setIntegrityCheck(false)
		->from(array('leadings' => $this->_name), '*')
	
		->where('leadings.t_reserve_id = ?', $t_reserve_id)
		->where('leadings.leading_comment != \'\'');
	
		return $this->fetchRow($this->setJoinField($select));
	}
	
	// 指定の予約IDと担当者IDを持つオブジェクトですべて取得
	public function selectFromReserveIdAndMemberId($t_reserve_id, $m_member_id_charge)
	{
		$select = $this->select();

		$select->setIntegrityCheck(false)
				->from(array('leadings' => $this->_name), '*')

				->where('leadings.t_reserve_id = ?', $t_reserve_id)
				->where('leadings.m_member_id_charge = ?', $m_member_id_charge)
				
				;

		return $this->fetchRow($this->setJoinField($select));
	}


	// 指定の担当者、予約日、連番で取得
	public function selectFromChargeIdAndReserve($m_member_id_charge, $reservationdate, $dayno)
	{
		$select = $this->select()->setIntegrityCheck(false)
				->from(
					array('leadings' => $this->_name), "*")

				->where('leadings.m_member_id_charge = ?', $m_member_id_charge)
				->where('shifts.dayno = ?', $dayno)
				->where('reserves.reservationdate = ?', $reservationdate)

				;

		return $this->fetchAll($this->setJoinField($select));
	}

	
	// 指定よ予約IDを持つオブジェクトをすべて削除
	public function deleteFromReserveId($t_reserve_id)
	{
		$where = $this->getAdapter()->quoteInto('t_reserve_id = ?' , $t_reserve_id);

		return $this->delete($where);
	}

}
