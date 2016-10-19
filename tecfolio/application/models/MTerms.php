<?php

require_once('BaseMModels.class.php');

/**
 * 大分類（マスター）モデルクラス
 *
 * 対応テーブル : m_terms
 *
 * @author		satake
 * @version		0.0.1
 */
class Class_Model_MTerms extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
     protected $_name   = 'm_terms';


	// 指定の日付が属する期間のマスタを取得
	public function getTermFromDate($date = '')
	{
		if (empty($date))
			$date = Zend_Registry::get('nowdate');	// 指定がなければ現在の日付

		$db = Zend_Db_Table::getDefaultAdapter();

		$select = $this->select()->setIntegrityCheck(false)
				->from(
					array('term' => $this->_name),
					array(
						'term.id',
						'term.name',
						'term.startdate',
						'term.enddate',
						'term.shift_startdate',
						'term.shift_enddate',
						'term.year',
						'term.createdate',
						'term.creator',
						'term.lastupdate',
						'term.lastupdater',
					)
				)

				->where('term.startdate <= ? AND term.enddate >= ?', $date, $date)

				->order(array('term.id'));

		return $this->fetchRow($select);
	}

	// 指定の日付が属する期間の次のマスタを取得
	public function getNextTermFromDate($date = '')
	{
		if (empty($date))
		{
			$date = Zend_Registry::get('nowdate');	// 指定がなければ現在の日付
		}

		$term = Class_Model_MTerms::getTermFromDate($date);

		$db = Zend_Db_Table::getDefaultAdapter();

		$select = $this->select()->setIntegrityCheck(false)
			->from(
				array('term' => $this->_name),
				array(
						'term.id',
						'term.name',
						'term.startdate',
						'term.enddate',
						'term.shift_startdate',
						'term.shift_enddate',
						'term.year',
						'term.createdate',
						'term.creator',
						'term.lastupdate',
						'term.lastupdater',
				)
		)

		->where('term.startdate > ?', $term->enddate)

		->order(array('term.startdate'));

		return $this->fetchRow($select);
	}

	// 指定の日付が属する期間とその次のマスタを取得
	public function getThisTermAndNextTermFromDate($date = '')
	{
		if (empty($date))
		{
			$date = Zend_Registry::get('nowdate');	// 指定がなければ現在の日付
		}

		$db = Zend_Db_Table::getDefaultAdapter();

		$select = $this->select()->setIntegrityCheck(false)
			->from(
				array('term' => $this->_name),
				array(
						'term.id',
						'term.name',
						'term.startdate',
						'term.enddate',
						'term.shift_startdate',
						'term.shift_enddate',
						'term.year',
						'term.createdate',
						'term.creator',
						'term.lastupdate',
						'term.lastupdater',
				)
		)

		->where('term.enddate >= ?', $date)
		->limit('2')

		->order(array('term.startdate'));
		
		return $this->fetchAll($select);
	}
	
	
	/***** 以下こいわ *****/
	
	
	//学期の更新
	public function updateFromId($id, $params)
	{
		if (!is_array($params))
			$params = array();

		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

		$this->update(
			$params,
			$this->getAdapter()->quoteInto("id = ?", $id)
		);
	}
	//学期の新規作成
	public function insertyear($params)
	{
		if (!is_array($params))
			$params = array();

		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
		$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

		return parent::insert($params);
	}
	//学期の表示をStartdateの順に並べる
	public function orderstartdate()
	{
		$select = $this->select();

		$select->order('startdate');

		return $this->fetchAll($select);
	}
	//年度にいくつ学期があるか調べる

	public function termsyear($toyear)
	{
		$select = $this->select();

		$select->where('year = ?', $toyear);

		return $this->fetchAll($select);
	}
	
	
	/***** 以上こいわ *****/
	
	
	// 最古の開始日と最新の終了日を取得
	public function getMinandMaxDate()
	{
		$select = $this->select()
		->from(
				array('term' => $this->_name),
				array(
						new Zend_Db_Expr("MIN(term.startdate) as mindate"),
						new Zend_Db_Expr("MAX(term.enddate) as maxdate"),
				)
		);

		return $this->fetchRow($select);
	}
	
	// 年度のみを取得
	public function getYears()
	{
		$select = $this->select()
		->from(
				array('term' => $this->_name),
				array(
						new Zend_Db_Expr("distinct year")
				)
		);
	
		$select->order('year asc');
		
		return $this->fetchAll($select);
	}
	
	// 年度から学期を取得
	public function selectFromYear($year)
	{
		$select = $this->select()
		->from(
				array('term' => $this->_name), '*'
		)
		->where('year = ?', $year);
		
		$select->order('startdate asc');
		
		return $this->fetchAll($select);
	}
	
	// 一つ前の年度を取得
	public function getPreviousTermFromStartDate($startdate)
	{
		$select = $this->select()
		->from(
				array('term' => $this->_name), '*'
		)
		->where('enddate = (date ? - 1)', $startdate);
		
		return $this->fetchRow($select);
	}
	
	// 引数termidでの予約が存在するかを調べる
	// @param	termid		学期ID
	// @return				placeidに一致する予約件数を返す
	public function getReservedTermId($termid)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('terms' => $this->_name), "*")
				->join(
						array('shifts' => 'm_shifts'),
						'shifts.m_term_id = terms.id',
						Class_Model_MShifts::fieldArray()
				)
				->join(
						array('reserves' => 't_reserves'),
						'reserves.m_shift_id = shifts.id',
						Class_Model_TReserves::fieldArray()
				);
		$select->where('terms.id = ?', $termid);
		$select->order('terms.id asc');
		
		return count($this->fetchAll($select));
	}
	
	// 登録されている最新年度を取得
	public function getLatestYear()
	{
		$select = $this->select()
		->from(
				array('term' => $this->_name),
				array(
						new Zend_Db_Expr("distinct year")
				)
		);
	
		$select->order('year desc');
		$select->limit(1,0);
		
		return $this->fetchRow($select);
	}
}

