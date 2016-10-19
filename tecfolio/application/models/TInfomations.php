<?php

require_once('BaseTModels.class.php');

// t_infomations テーブルクラス
class Class_Model_TInfomations extends BaseTModels
{
	const TABLE_NAME = 't_infomations';
	protected $_name   = Class_Model_TInfomations::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TInfomations::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_title'				=> 'title',
			$prefix . '_body'				=> 'body',
			$prefix . '_m_range_id' 		=> 'm_range_id',
			$prefix . '_m_member_id_from'	=> 'm_member_id_from',
			$prefix . '_m_member_id_to'		=> 'm_member_id_to',
			$prefix . '_t_course_id'		=> 't_course_id',
			$prefix . '_subtitle'			=> 'subtitle',
			$prefix . '_startdate'			=> 'startdate',
			$prefix . '_enddate'			=> 'enddate',

			$prefix . '_createdate'			=> 'createdate',
			$prefix . '_creator'			=> 'creator',
			$prefix . '_lastupdate'			=> 'lastupdate',
			$prefix . '_lastupdater'		=> 'lastupdater',

			$prefix . '_calendar_flag'		=> 'calendar_flag',
			$prefix . '_allday_flag'		=> 'allday_flag',
		);
	}

	public function selectFromMemberIdAll($m_member_id, $limit=0)
	{
		$select = $this->GetSelectFromMemberIdAll($m_member_id, $limit);

		return $this->fetchAll($select);
	}


	// キーワードタグのIDから検索しselectオブジェクトを返す
	public function GetSelectFromMemberIdAll($m_member_id, $limit=0)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$courseids = $db->select()->from(	// 参加コースのコースID
										array('membercourses'=>'t_member_courses'),
										array('membercourses.t_course_id'))
							->where('membercourses.m_member_id = ?', $m_member_id);

		$select = $this->select()->setIntegrityCheck(false)
				->from(array('info' => $this->_name), '*')

				// 対象の情報を取得
				->joinLeft(
					array('members' => 'm_members'),
					'members.id = info.m_member_id_from OR members.id = info.m_member_id_to',
					Class_Model_MMembers::fieldArray()
				)

				->joinLeft(
					array('course' => 't_courses'),
					'course.id = info.t_course_id',
					array(
						't_courses_id' => 'id',
						't_courses_title' => 'title',
						't_courses_startdate' => 'startdate',
						't_courses_enddate' => 'enddate',
						't_courses_sort_no' => 'sort_no',
						't_courses_datalink_course_id' => 'datalink_course_id',
						't_courses_createdate' => 'createdate',
						't_courses_creator' => 'creator',
						't_courses_lastupdate' => 'lastupdate',
						't_courses_lastupdater' => 'lastupdater',
					)
				)

				->where('info.m_range_id <>  ?', 0)
				->orWhere('info.m_member_id_from = ?', $m_member_id)
				->orWhere('info.m_member_id_to = ?', $m_member_id)
				->orWhere('info.t_course_id IN (?)', $courseids)

				->order(array('info.id'));
		if ($limit > 0)
			$select->limit($limit, 0);
		return $select;
	}

	public function selectFromAll($limit=0)
	{
		$select = $this->GetSelectFromMemberIdAll($m_member_id, $limit);

		return $this->fetchAll($select);
	}


	public function selectFromCourseId($t_course_id, $limit=0)
	{
		$select = $this->select();
		$select->where('t_course_id = ?', $t_course_id)
				->order(array('id'));

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->fetchAll($select);
	}

	
	public function selectFromDate($startdate, $enddate, $order=array())
	{
		$select = $this->select();
		$select->where('(startdate <= to_date(?,\'yyyy/mm/dd\')', $enddate);
		$select->where('enddate >= ?)', $startdate);
		$select->orWhere('(startdate <= to_date(?,\'yyyy/mm/dd\')', $enddate);
		$select->where('startdate >= ?', $startdate);
		$select->where('allday_flag = \'1\')');
	
		if (count($order) > 0)
			$select->order($order);
	
		return $this->fetchAll($select);
	}
	
	public function selectLimitedAll($order="", $limit="")
	{
		if (empty($order))
			$select = $this->select();
		else
			$select = $this->select()->order($order);
		
		if (!empty($limit))
			$select->limit($limit);
	
		return $this->fetchAll($select);
	}

}
