<?php

require_once('BaseTModels.class.php');

class Class_Model_TClosuredates extends BaseTModels
{
	/**
	 * @var string 対応テーブル名
	 */
	const TABLE_NAME = 't_closuredates';
	protected $_name   = Class_Model_TClosuredates::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TClosuredates::TABLE_NAME;

		return array(
				$prefix . '_closuredate' => 'closuredate',
				$prefix . '_m_place_id' => 'm_shift_id',
				
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// 学期期間と場所で選択
	public function selectFromTermIdAndPlaceId($termid, $placeid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$startdate = $db->select()->from(
					array('terms'=>'m_terms'),
					array(new Zend_Db_Expr("DATE_TRUNC('month', to_timestamp(CAST(terms.startdate AS TEXT),'yyyy/mm/dd'))"))
				)
				->where('terms.id = ?', $termid)
				;
	
		$enddate = $db->select()->from(
					array('terms'=>'m_terms'),
					array(new Zend_Db_Expr("DATE_TRUNC('month', to_timestamp(CAST(terms.enddate AS TEXT),'yyyy/mm/dd')  + '1 months')  + '-1 days'"))
				)
				->where('terms.id = ?', $termid)
				;
	
		$select = $this->select()
			->where('closuredate >= ?', $startdate)
			->where('closuredate <= ?', $enddate)
			->where('m_place_id = ?', $placeid);
	
		return $this->fetchAll($select);
	}
	
	// 閉室日をバルクインサート
	function insert($reservationdates, $m_place_id, $creator)
	{
		if(empty($reservationdates)) exit();
		sort($reservationdates);
		
		$db = $this->getadapter();	// DBアダプタ取得
		
		$sql = "INSERT INTO t_closuredates(closuredate, m_place_id, lastupdate, lastupdater) VALUES";
		
		foreach($reservationdates as $rdate)
		{
			$sql .= "('" . $rdate . "', :m_place_id, :lastupdate, :lastupdater),";
		}
		
		// 最後の,を削除
		$sql = substr($sql, 0, strlen($sql) - 1);
		$stt = $db->prepare($sql);
		
		$stt->bindValue(':m_place_id', $m_place_id, Zend_Db::PARAM_INT);
		$stt->bindValue(':lastupdate', Zend_Registry::get('nowdatetime'), Zend_Db::PARAM_STR);
		$stt->bindValue(':lastupdater', $creator, Zend_Db::PARAM_INT);
		
		$stt->execute();
	}
	
	// 学期期間で削除
	public function deleteFromTermIdAndPlaceId($termid, $placeid)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// 学期開始月の月初日
		$startdate = $db->select()->from(
					array('terms'=>'m_terms'),
					array(new Zend_Db_Expr("DATE_TRUNC('month', to_timestamp(CAST(terms.startdate AS TEXT),'yyyy/mm/dd'))"))
				)
				->where('terms.id = ?', $termid)
				;
		
		// 学期開始月の月末日
		$enddate = $db->select()->from(
					array('terms'=>'m_terms'),
					array(new Zend_Db_Expr("DATE_TRUNC('month', to_timestamp(CAST(terms.enddate AS TEXT),'yyyy/mm/dd')  + '1 months')  + '-1 days'"))
				)
				->where('terms.id = ?', $termid)
				;
	
		$where = $this->getAdapter()->quoteInto('closuredate >= ?', $startdate) .
			$this->getAdapter()->quoteInto(' AND closuredate <= ?', $enddate) .
			$this->getAdapter()->quoteInto(' AND m_place_id = ?', $placeid);
	
		return $this->delete($where);
	}
	
	// 場所と日付で選択
	public function selectFromYmdAndPlaceId($ymd, $placeid)
	{
		$select = $this->select()
		->where('closuredate = ?', $ymd)
		->where('m_place_id = ?', $placeid);
	
		return $this->fetchAll($select);
	}
}

