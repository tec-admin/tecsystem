<?php

require_once('BaseTModels.class.php');

class Class_Model_TMaxlimits extends BaseTModels
{
	/**
	 * @var string 対応テーブル名
	 */
	const TABLE_NAME = 't_maxlimits';
	protected $_name   = Class_Model_TMaxlimits::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TMaxlimits::TABLE_NAME;

		return array(
				$prefix . '_reservationdate' => 'reservationdate',
				$prefix . '_m_shiftclass_id' => 'm_shiftclass_id',
				$prefix . '_maxlimit' => 'maxlimit',

				$prefix . '_createdate' => 'createdate',
				$prefix . '_creator' => 'creator',
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// 指定のシフト種別・学期期間・曜日に属する受入上限を取得
	public function selectMaxlimitFromShiftclassAndTermIdAndDow($shiftclass, $termid, $dow)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subSelect1 = $db->select()
		->from('m_terms', array('startdate' => 'startdate'))
		->where('id = ?', $termid);
		
		$subSelect2 = $db->select()
		->from('m_terms', array('enddate' => 'enddate'))
		->where('id = ?', $termid);
	
		$select = $this->select()
				->where('EXTRACT(DOW FROM to_timestamp(CAST(reservationdate AS TEXT),\'yyyy/mm/dd\')) = ?', $dow)
				->where('m_shiftclass_id = ?', $shiftclass)
				->where('reservationdate >= ?', $subSelect1)
				->where('reservationdate <= ?', $subSelect2)
		;
	
		return $this->fetchAll($select);
	}
	
	// 指定のシフト種別・日付に属する受入上限を取得
	public function selectMaxlimitFromShiftclassAndDate($shiftclass, $date)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$select = $this->select()
		->where('reservationdate = ?', $date)
		->where('m_shiftclass_id = ?', $shiftclass)
		;
	
		return $this->fetchRow($select);
	}
	
	// 学期単位での受入上限設定
	public function updateFromShiftclassAndTermIdAndDow($shiftclass, $termid, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
		
		$subSelect1 = $db->select()
		->from('m_terms', array('startdate' => 'startdate'))
		->where('id = ?', $termid);
		
		$subSelect2 = $db->select()
		->from('m_terms', array('enddate' => 'enddate'))
		->where('id = ?', $termid);
	
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto('m_shiftclass_id = ?', $shiftclass) . 
				$this->getAdapter()->quoteInto(' AND reservationdate >= ?', $subSelect1) .
				$this->getAdapter()->quoteInto(' AND reservationdate <= ?', $subSelect2) .
				$this->getAdapter()->quoteInto(" AND EXTRACT(DOW FROM to_timestamp(CAST(reservationdate AS TEXT),'yyyy/mm/dd')) = ?", $dow)
		);
	}
	
	// 日付単位での受入上限設定
	public function updateFromShiftclassAndDate($shiftclass, $date, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto('m_shiftclass_id = ?', $shiftclass) . 
				$this->getAdapter()->quoteInto(" AND reservationdate = ?", $date)
		);
	}
	
	// 学期単位での受入数データを新規挿入
	public function insertFromShiftclassAndTermIdAndDow($shiftclass, $termid, $dow, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		if (!is_array($params))
			$params = array();
	
		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$subSelectTermStart = $db->select()
		->from('m_terms', array('startdate' => 'startdate'))
		->where('id = ?', $termid);
	
		$subSelectTermEnd = $db->select()
		->from('m_terms', array('enddate' => 'enddate'))
		->where('id = ?', $termid);
	
		$subSelectShiftId = $db->select()
		->from('m_terms', '')
		->join( array('insert_date' => new Zend_Db_Expr("(SELECT (" . $subSelectTermStart . ") + i as d from generate_series(0, (".$subSelectTermEnd.") - (".$subSelectTermStart.")) as i)"))
				, '1=1'
				, array('startdate' => 'insert_date.d'))
				->where('startdate >= ?',  $subSelectTermStart)
				->where('startdate <= ?',  $subSelectTermEnd)
				->where('EXTRACT(DOW FROM to_timestamp(CAST(insert_date.d AS TEXT),\'yyyy/mm/dd\')) = ?', $dow)
				;
	
		$subSelectShiftId->columns(array('m_shiftclass_id' => new Zend_Db_Expr("'" . $shiftclass . "'")));
		$subSelectShiftId->columns(array('maxlimit' => new Zend_Db_Expr("'" . $params["maxlimit"] . "'")));
		$subSelectShiftId->columns(array('createdate' => new Zend_Db_Expr("'" . $params["createdate"] . "'")));
		$subSelectShiftId->columns(array('creator' => new Zend_Db_Expr("'" . $params["creator"] . "'")));
		$subSelectShiftId->columns(array('lastupdate' => new Zend_Db_Expr("'" . $params["lastupdate"] . "'")));
		$subSelectShiftId->columns(array('lastupdater' => new Zend_Db_Expr("'" . $params["lastupdater"] . "'")));
		
		$this->insertSelect('t_maxlimits', $subSelectShiftId, $this::fieldArray());
	}
	
	// 日付期間のデータを選択
	public function selectFromDates($weektop, $weekend)
	{
		$select = $this->select()
			->from('t_maxlimits', 
					array(
							'reservationdate' 	=> 'reservationdate', 
							'dow' 				=> new Zend_Db_Expr("EXTRACT(DOW FROM to_timestamp(CAST(reservationdate AS TEXT),'yyyy/mm/dd'))"),
							'm_shiftclass_id'	=> 'm_shiftclass_id',
							'maxlimit'			=> 'maxlimit'
		))
				->where('reservationdate >= ?', $weektop)
				->where('reservationdate <= ?', $weekend)
		;
		
		return $this->fetchAll($select);
	}
	
	// 学期期間のデータを選択
	public function selectFromTermId($termid)
	{
		$select = $this->select()->setIntegrityCheck(false)
			->from('t_maxlimits',
				array(
						'reservationdate' 	=> 'reservationdate',
						'dow' 				=> new Zend_Db_Expr("EXTRACT(DOW FROM to_timestamp(CAST(reservationdate AS TEXT),'yyyy/mm/dd'))"),
						'm_shiftclass_id'	=> 'm_shiftclass_id',
						'maxlimit'			=> 'maxlimit'
				))
			->join(
				array('terms' => 'm_terms'),
				't_maxlimits.reservationdate BETWEEN terms.startdate AND terms.enddate',
				array('terms.id' => 'terms.id')
			)
				->where('terms.id = ?', $termid)
				->order(array('terms.startdate asc'))
				;
		
		return $this->fetchAll($select);
	}
}



