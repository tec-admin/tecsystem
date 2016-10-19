<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TMentors extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_mentors';
	protected $_name   = Class_Model_Tecfolio_TMentors::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TMentors::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			$prefix . '_m_mytheme_id' => 'm_mytheme_id',
			$prefix . '_mentor_number' => 'mentor_number',
			$prefix . '_m_member_id' => 'm_member_id',
			$prefix . '_name_jp' => 'name_jp',
			$prefix . '_syzkcd_c' => 'syzkcd_c',
			$prefix . '_agreement_flag' => 'agreement_flag',
			
			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	public function selectFromMythemeId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('mentors'=>'t_mentors'), '*'
		)
		->where('m_mytheme_id = ?', $id)
		;
	
		return $this->fetchRow($select);
	}
	
	public function selectFromIdAndMemberId($id, $memberid)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('mentors'=>'t_mentors'), '*'
		)
		->where('id = ?', $id)
		->where('m_member_id = ?', $memberid)
		;
	
		return $this->fetchRow($select);
	}
	
	// MyテーマIDからプロフィールを含めて選択
	public function selectProfileFromMythemeId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('mentors'=>'t_mentors'), '*'
		)
		->where('m_mytheme_id = ?', $id)
		->where('mentors.agreement_flag != \'2\'')
		->joinLeft(
				array('profiles' => 't_profiles'),
				'mentors.m_member_id = profiles.m_member_id',
				array('input_name')
		)
		;
	
		return $this->fetchRow($select);
	}
	
	// メンバーID(依頼者ID)からその名前を含めて選択
	public function selectFromMemberId($id)
	{
		// MyテーマをjoinRightし、授業科目担当としてのメンター項目を除外する
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('mentors'=>'t_mentors'), '*'
		)
		->where('mentors.m_member_id = ?', $id)
		->where('mentors.agreement_flag = \'1\'')
		->joinLeft(
				array('requester' => 'm_members'),
				'mentors.creator = requester.id',
				array('requester_name_jp' => 'name_jp')
		)
		->joinRight(
				array('mytheme' => 'm_mythemes'),
				'mentors.m_mytheme_id = mytheme.id',
				array('mytheme_name' => 'name')
		)
		;
		
		return $this->fetchAll($select);
	}
	
	// 授業科目設定時、科目ごとに担当教諭をメンターとしてデータ作成する
	public function insertFromMSubjects($kyoincd, $jyu_knr_no, $jwaricd, $prefix, $mentor_id, $member_id, $member_name, $member_syzkcd_c)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		// IDの元となる乱数にシーケンス値を加え、科目ごとのメンターIDとする
		$select = $db->select()
		->from(
				array('subjects' => 'm_subjects'),
				array('id'  => new Zend_Db_Expr("'" . $prefix . "' || (" . $mentor_id . " + nextval('m_mentor_id_seq') )"), 'm_mytheme_id' => "('SUBJ_' || jyu_nendo || jyu_knr_no || kyoincd || jwaricd)", 
						'mentor_number' => new Zend_Db_Expr("1"), 'm_member_id'  => "('" . $member_id . "')", 'name_jp'  => "('" . $member_name . "')", 'syzkcd_c'  => "('" . $member_syzkcd_c . "')", 'agreement_flag'  => "('1')"
				)
		)
		->where('kyoincd = ?', $kyoincd)
		;
	
		$str = "";
		foreach($jyu_knr_no as $k => $val)
		{
			$str .= "('" . $val . "','" . $jwaricd[$k] . "'),";
		}
	
		$select->where('(jyu_knr_no, jwaricd) IN ('. substr($str, 0, -1) . ')');
		
		
		
		$select->columns(array('createdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('creator' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
		
		$this->insertSelect('t_mentors', $select, $this::fieldArray());
	}
	
	// 生徒用新着取得
	public function selectFromStudentNews($studentid, $limit=5, $offset=0)
	{
		// メンター用
		$select3 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('members' => 'm_members'), array('MyID' => 'id')
		)
		->where('members.id = ?', $studentid);
	
		$select3
		->joinRight(
				array('mentors' => 't_mentors'),
				'members.id = mentors.m_member_id',
				Class_Model_Tecfolio_TMentors::fieldArray()
		)
		->joinLeft(
				array('requester' => 'm_members'),
				'mentors.creator = requester.id',
				array('requester_id' => 'id', 'requester_name_jp' => 'name_jp')
		)
		->where('mentors.m_member_id = ?', $studentid)
		->where('mentors.m_mytheme_id not like \'SUBJ%\'')
		;
		// ソート用カラムを作成
		$select3->columns(array('studentnews_date' => 'mentors.lastupdate'));
	
		// メンター依頼者用
		$select4 = $this->select()->distinct()->setIntegrityCheck(false)
		->from(
				array('members' => 'm_members'), array('MyID' => 'id')
		)
		->where('members.id = ?', $studentid);
	
		$select4
		->joinRight(
				array('mentors' => 't_mentors'),
				'members.id = mentors.creator',
				Class_Model_Tecfolio_TMentors::fieldArray()
		)
		->joinLeft(
				array('requester' => 'm_members'),
				'mentors.creator = requester.id',
				array('requester_id' => 'id', 'requester_name_jp' => 'name_jp')
		)
		->where('requester.id = ?', $studentid)
		->where('mentors.m_mytheme_id not like \'SUBJ%\'')
		;
		// ソート用カラムを作成
		$select4->columns(array('studentnews_date' => 'mentors.lastupdate'));
	
		// 二つの表をUNION
		$select = $this->select()
		->union(array($select3, $select4))
		->order('studentnews_date DESC');
	
		if ($limit > 0)
			$select->limit($limit, $offset);
	
		return $this->fetchAll($select);
	}
}

