<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_MMythemes extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_mythemes';
	protected $_name   = Class_Model_Tecfolio_MMythemes::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_MMythemes::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			
			$prefix . '_m_member_id' => 'm_member_id',
			$prefix . '_name_jp' => 'name_jp',
			$prefix . '_syzkcd_c' => 'syzkcd_c',
			$prefix . '_name' => 'name',
			$prefix . '_disabled_flag' => 'disabled_flag',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	public function selectFromMemberId($id)
	{
		$select = $this->select();
		$select->where('id like \'THEME_ID%\'');
		$select->where('m_member_id = ?', $id)
			->where('disabled_flag != \'1\'')
			->order(array('order_num asc'));
	
		return $this->fetchAll($select);
	}
	
	// m_member_idから順序の最大値を取得
	public function selectMaxOrderFromMemberId($id)
	{
		$select = $this->select();
		$select->where('id like \'THEME_ID%\'');
		$select->where('m_member_id = ?', $id)
		->order(array('order_num desc'))
		->limit(1,0);
	
		return $this->fetchRow($select);
	}
	
	// メンバーIDとテーマ名から選択(挿入時重複チェック)
	public function selectFromMemberIdAndName($m_member_id, $name)
	{
		$select = $this->select();
		$select->where('id like \'THEME_ID%\'');
		$select->where('m_member_id = ?', $m_member_id);
		$select->where('name = ?', $name);
	
		return $this->fetchRow($select);
	}
	
	// IDを除外してテーマ名とメンバーIDから選択(更新時重複チェック)
	public function selectFromIdAndNameAndMemberId($id, $name, $memberid)
	{
		$select = $this->select();
		$select->where('id != ?', $id);
		$select->where('name = ?', $name);
		$select->where('m_member_id = ?', $memberid);
	
		return $this->fetchRow($select);
	}
	
	public function selectMentorFromMentorId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('mytheme'=>'m_mythemes'), '*'
		)
		->joinLeft(
				array('mentor' => 't_mentors'),
				'mytheme.id = mentor.m_mytheme_id',
				Class_Model_Tecfolio_TMentors::fieldArray()
		)
		->joinLeft(
				array('profile' => 't_profiles'),
				'mentor.creator = profile.m_member_id', 
				Class_Model_Tecfolio_TProfiles::fieldArray()
		)
		->joinLeft(
				array('members' => 'm_members'),
				'mentor.creator = members.id',
				array('m_members_student_id_jp' => 'student_id_jp', 'm_members_email' => 'email')
		)
		;
		
		$select->where('mentor.id = ?', $id)
		->where('mytheme.disabled_flag != \'1\'')
		->order(array('mytheme.order_num asc'));
		
		return $this->fetchRow($select);
	}
	
	// preDispatch内でIDを判別する際の処理
	public function selectFromIdAndMemberId($id, $memberid)
	{
		$select = $this->select();
		$select->where('id = ?', $id)
		->where('m_member_id = ?', $memberid)
		->where('disabled_flag != \'1\'')
		;
	
		return $this->fetchRow($select);
	}
	
	// 利用しないテーマを取得
	public function selectDisabledFromMemberId($id)
	{
		$select = $this->select();
		$select->where('id like \'THEME_ID%\'');
		$select->where('m_member_id = ?', $id)
		->where('disabled_flag = \'1\'')
		->order(array('order_num asc'));
	
		return $this->fetchAll($select);
	}
	
	// 順序入替
	// @param	$direction		0=一つ下, 1=一つ上
	public function selectTargetFromIdAndMemberId($id, $memberid, $direction)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$sub = $db->select()
		->from(
				array('mytheme'=>'m_mythemes'),
				array('order_num')
		)
		->where('id = ?', $id);
		
		$select = $this->select();
		if($direction != 0)
		{
			$select
				->where('m_member_id = ?', $memberid)
				->where('order_num < ?', $sub)
				->order('order_num desc');
		}
		else
		{
			$select
				->where('m_member_id = ?', $memberid)
				->where('order_num > ?', $sub)
				->order('order_num asc');
		}
		
		$select
			->where('id like \'THEME_ID%\'')
			->where('disabled_flag != \'1\'')
			->limit(1, 0);
		
		return $this->fetchRow($select);
	}
}

