<?php

require_once('BaseTModels.class.php');

// t_reserves テーブルクラス
class Class_Model_TReserveHistory extends BaseTModels
{
	const TABLE_NAME = 't_reserve_history';
	protected $_name   = Class_Model_TReserveHistory::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TReserveHistory::TABLE_NAME;

		return array(
				$prefix . '_id' => 'id',
				
				$prefix . '_m_member_id_reserver' => 'm_member_id_reserver',
				$prefix . '_student_id' => 'student_id',
				$prefix . '_name_jp' => 'name_jp',
				$prefix . '_email' => 'email',
				
				$prefix . '_sex' => 'sex',
				$prefix . '_setti_cd' => 'setti_cd',
				$prefix . '_syozkcd1' => 'syozkcd1',
				$prefix . '_syozkcd2' => 'syozkcd2',
				$prefix . '_entrance_year' => 'entrance_year',
				$prefix . '_gaknenkn' => 'gaknenkn',
				
				$prefix . '_reservationdate' => 'reservationdate',
				$prefix . '_nendo' => 'nendo',
				
				$prefix . '_m_shift_id' => 'm_shift_id',
				
				$prefix . '_jwaricd' => 'jwaricd',
				$prefix . '_class_subject' => 'class_subject',
				$prefix . '_sekiji_top_kyoinmei' => 'sekiji_top_kyoinmei',
				
				$prefix . '_submitdate' => 'submitdate',
				$prefix . '_progress' => 'progress',
				$prefix . '_question' => 'question',
				
				$prefix . '_createdate' => 'createdate',
				$prefix . '_creator' => 'creator',
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
					
				$prefix . '_run_reserve' => 'run_reserve',
					
				$prefix . '_historyclass' => 'historyclass',
		);
	}

	private function joinField($select)
	{
		$select
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shifts.id = history.m_shift_id',
				Class_Model_MShifts::fieldArray()
		)
		
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)

		->joinLeft(
				array('reserver' => 'm_members'),
				'history.m_member_id_reserver = reserver.id',
				Class_Model_MMembers::fieldArray('reserver')
		)

		->joinLeft(
				array('reservecomments' => 't_reserve_comments'),
				'reservecomments.t_reserve_id = history.t_reserve_id',
				Class_Model_TReserveComments::fieldArray()
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
				array('syozoku1' => 't_syozoku1'),
				'reserver.setti_cd = syozoku1.setti_cd AND reserver.syozkcd1 = syozoku1.syozkcd1',
				Class_Model_TSyozoku1::fieldArray()
		)

// 		->joinLeft(
// 				array('subjects' => 'm_subjects'),
// 				'subjects.jyu_knr_no = history.m_subject_id',
// 				Class_Model_MSubjects::fieldArray()
// 		)
		
// 		->joinLeft(
// 				array('faculties' => 'm_faculties'),
// 				'reserver.m_faculty_id = faculties.id',
// 				Class_Model_MFaculties::fieldArray()
// 		)

		;

		return $select;
	}

	private function joinFieldLeftLeading($select)
	{
		$select
		->joinLeft(
				array('leadings' => 't_leadings'),
				'leadings.t_reserve_id = history.t_reserve_id',
				Class_Model_TLeadings::fieldArray()
		)

		// leading を参照してるのでこちらへ入れる
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				Class_Model_MMembers::fieldArray('charge')
		)
		;
		return $select;
	}

	private function joinFieldInnerLeading($select)
	{
		$select
		->join(
				array('leadings' => 't_leadings'),
				'leadings.t_reserve_id = history.id',
				Class_Model_TLeadings::fieldArray()
		)

		// leading を参照してるのでこちらへ入れる
		->joinLeft(
				array('charge' => 'm_members'),
				'leadings.m_member_id_charge = charge.id',
				Class_Model_MMembers::fieldArray('charge')
		)
		;
		return $select;
	}

	private function setJoinField($select)
	{
		return $this->joinFieldLeftLeading($this->joinField($select));
	}

	// t_leadings のみ inner join にする
	private function setJoinFieldInnerLeading($select)
	{
		return $this->joinFieldInnerLeading($this->joinField($select));
	}
	
	// 利用データダウンロード用データを取得する
	public function selectAllForDownload()
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(array('history' => $this->_name), '*')
		->order(array('history.reservationdate asc', 'shifts.dayno asc', 'history.id'));
	
		return $this->fetchAll($this->setJoinField($select));
	}

	// スタッフ用新着、スタッフのシフトと曜日(dow)に一致する行のみを取得
	// delete_flag=1の行は取得しない
	// 第一引数が0なら全取得(運営用)
	public function selectFromStaffNews($staffid, $limit=5, $offset=0)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subselect = $db->select()->from(
				array('staffshifts'=>'t_staffshifts'),
				array('staffshifts.m_shift_id', 'staffshifts.dow'));
		if(!empty($staffid))
			$subselect->where('staffshifts.m_member_id = ?', $staffid);
		
		// コメント
		$select1 = $this->select()->setIntegrityCheck(false)
		->from(
				array('history' => $this->_name), "*")
				->where('(history.m_shift_id, EXTRACT(DOW FROM history.reservationdate)) IN (?)', $subselect)
				->where('history.delete_flag != ?', '1')
				->where('leadings.m_member_id_charge = ?', $staffid)
				->where('history.historyclass = ?', '4')
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shifts.id = history.m_shift_id',
				Class_Model_MShifts::fieldArray()
		)
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)
		->joinLeft(
				array('places' => 'm_places'),
				'places.id = shifts.m_place_id',
				Class_Model_MPlaces::fieldArray()
		)
		->joinLeft(
				array('reserver' => 'm_members'),
				'history.m_member_id_reserver = reserver.id',
				Class_Model_MMembers::fieldArray('reserver')
		)
		->joinRight(
				array('leadings' => 't_leadings'),
				'leadings.t_reserve_id = history.t_reserve_id',
				Class_Model_TLeadings::fieldArray()
		)
		
		;
		// 新着種別
		$select1->columns(array('staffnews_type' => new Zend_Db_Expr("'comment'")));
		// ソート用カラムを作成
		$select1->columns(array('staffnews_date' => 'history.createdate'));
		
		
		// 予約
		$select2 = $this->select()->setIntegrityCheck(false)
		->from(
				array('history' => $this->_name), "*")
				->where('(history.m_shift_id, EXTRACT(DOW FROM history.reservationdate)) IN (?)', $subselect)
				->where('history.delete_flag != ?', '1')
				->where('history.historyclass != ?', '4')
		->joinLeft(
				array('shifts' => 'm_shifts'),
				'shifts.id = history.m_shift_id',
				Class_Model_MShifts::fieldArray()
		)
		->joinLeft(
				array('timetables' => 'm_timetables'),
				'shifts.dayno = timetables.id',
				Class_Model_MTimetables::fieldArray()
		)
		->joinLeft(
				array('places' => 'm_places'),
				'places.id = shifts.m_place_id',
				Class_Model_MPlaces::fieldArray()
		)
		->joinLeft(
				array('reserver' => 'm_members'),
				'history.m_member_id_reserver = reserver.id',
				Class_Model_MMembers::fieldArray('reserver')
		)
		->joinLeft(
				array('leadings' => 't_leadings'),
				'1 = 0',
				Class_Model_TLeadings::fieldArray()
		)
		;
		// 新着種別
		$select2->columns(array('staffnews_type' =>
				new Zend_Db_Expr("CASE 
						WHEN history.historyclass = '1'
						THEN 'insert'
						WHEN history.historyclass = '2'
						THEN 'update'
						WHEN history.historyclass = '3'
						THEN 'delete' END")));
		
		// ソート用カラムを作成
		$select2->columns(array('staffnews_date' => 'history.createdate'));
		
		
		// 二つの表をUNION
		$select = $this->select()
		->union(array($select1, $select2))
		->order('staffnews_date DESC');

		if ($limit > 0)
			$select->limit($limit, $offset);

		return $this->fetchAll($select);
	}

	public function selectFromStudentNews($studentid, $limit=5, $offset=0)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
			
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('history' => $this->_name), "*")
				->where('history.m_member_id_reserver = ?', $studentid)
				->where('history.delete_flag != ?', '1')
				;

		// 新着種別
		$select->columns(array('studentnews_type' =>
				new Zend_Db_Expr("CASE WHEN history.historyclass = '1'
						THEN 'insert'
						WHEN history.historyclass = '2'
						THEN 'update'
						WHEN history.historyclass = '3'
						THEN 'delete'
						WHEN history.historyclass = '4'
						THEN 'comment' END")));

		// ソート用カラムを作成
		$select->columns(array('studentnews_date' =>
				new Zend_Db_Expr("CASE WHEN reservecomments.lastupdate IS NOT NULL
						THEN reservecomments.lastupdate
						ELSE history.createdate END")));

		// ソート
		$select->order('studentnews_date DESC');

		if ($limit > 0)
			$select->limit($limit, $offset);

		$tmp = $this->setJoinField($select);

		/*
		 $frontController = Zend_Controller_Front::getInstance();
		$config = $frontController->getParam("bootstrap")->getOptions();

		$this->logpath = $config['trace']['log']['path'];
		$this->logname = $config['trace']['log']['name'];
		$logFile = $this->logpath . '/' . strtr($this->logname, array('#DT#' => strftime('%d')));
		$logger = new Zend_Log(new Zend_Log_Writer_Stream($logFile));
		$logger->log($tmp->__toString(), Zend_Log::DEBUG);

		return $this->fetchAll($tmp);
		*/
	}

	// 予約IDで一括更新する
	public function updateFromReserveId($t_reserve_id, $params)
	{
		if (!is_array($params))
			$params = array();

		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

		$this->update(
				$params,
				$this->getAdapter()->quoteInto('t_reserve_id = ?', $t_reserve_id)
		);
	}

}
