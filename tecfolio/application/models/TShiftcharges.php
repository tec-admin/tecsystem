<?php

require_once('BaseTModels.class.php');

class Class_Model_TShiftcharges extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	protected $_name   = 't_shiftcharges';

	public function selectShift($m_shift_id, $reservationdate)
	{
		$select = $this->select();
		$select->where('m_shift_id = ?', $m_shift_id);
		$select->where('reservationdate = ?', $reservationdate);
		return $this->fetchAll($select);
	}
	
	// 関大：場所・シフト連番・日付で取得
	public function selectShiftKwl($m_place_id, $dayno, $reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_place_id = ?', $m_place_id)
				->where('shifts.dayno = ?', $dayno)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shiftcharges' => $this->_name), "*")
	
				->where('shiftcharges.m_shift_id IN (?)', $subselect)
				->where('shiftcharges.reservationdate = ?', $reservationdate)
				;
	
		return $this->fetchAll($select);
	}
	
	// 津大：シフト種別・シフト連番・日付で取得
	public function selectShiftTwc($m_dockind_id, $dayno, $reservationdate)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// id降順だと、アカデミック(shiftclass=1)の場合'1,2'ではなく'1'が返る
		// 予約数は'1,2'、受入数・担当者数は'1'で取得する必要がある
		// ※シフト種別が変更される予定はなし
		$shiftclass = Class_Model_MLShiftclasses::getShiftclassFromDockindId($m_dockind_id, 'id desc');
		
		$shiftclasses = explode(",", $shiftclass['m_shiftclass_id']);
		
		$subselect1 = $db->select()->from(
				array('dockinds'=>'m_dockinds'),
				array('dockinds.id'))
				->where('dockinds.shiftclass = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$subselect1->orWhere('dockinds.shiftclass = ?', $shiftclasses[1]);
		}
		
		$subselect2 = $db->select()->from(
				array('shifts'=>'m_shifts'),
				array('shifts.id'))
				->where('shifts.m_dockind_id IN ?', $subselect1)
				->where('shifts.dayno = ?', $dayno)
				;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('shiftcharges' => $this->_name), "*")
	
				->where('shiftcharges.m_shift_id IN (?)', $subselect2)
				->where('shiftcharges.reservationdate = ?', $reservationdate)
				;
	
		return $this->fetchAll($select);
	}

	// シフトマスタIDで取得
	public function selectFromShiftId($m_shift_id)
	{
		$select = $this->select();
		$select->where('m_shift_id = ?', $m_shift_id);
		return $this->fetchAll($select);
	}

	// 指定の期間内をすべて取得
	public function selectFromTerm($startdate, $enddate)
	{
		$select = $this->select();

		$select->where("'" . $startdate . "' <= reservationdate AND " . "'" . $enddate . "' >= reservationdate");

		return $this->fetchAll($select);
	}
}

