<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TProfiles extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME		= 't_profiles';
	protected $_name		= Class_Model_Tecfolio_TProfiles::TABLE_NAME;
	protected $_primary		= 'm_member_id';

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TProfiles::TABLE_NAME;

		return array(
			$prefix . '_m_member_id' => 'm_member_id',
			
			$prefix . '_nickname'		=> 'nickname',
			$prefix . '_languages'		=> 'languages',
			$prefix . '_email_2'		=> 'email_2',
			$prefix . '_email_3'		=> 'email_3',
			$prefix . '_input_name'		=> 'input_name',
			$prefix . '_image_name'		=> 'image_name',
			$prefix . '_speciality'		=> 'speciality',
			$prefix . '_seminar'		=> 'seminar',
			$prefix . '_highschool'		=> 'highschool',
			$prefix . '_birthday'		=> 'birthday',
			$prefix . '_sex'			=> 'sex',
			$prefix . '_birthplace'		=> 'birthplace',
			$prefix . '_mentor_flag'	=> 'mentor_flag',
			$prefix . '_hobby'			=> 'hobby',
			$prefix . '_ability'		=> 'ability',
			$prefix . '_likes'			=> 'likes',
			$prefix . '_dislikes'		=> 'dislikes',
			$prefix . '_personality'	=> 'personality',
			$prefix . '_strength'		=> 'strength',
			$prefix . '_weekness'		=> 'weekness',
			$prefix . '_cert_1'			=> 'cert_1',
			$prefix . '_cert_2'			=> 'cert_2',
			$prefix . '_cert_3'			=> 'cert_3',
			$prefix . '_cert_4'			=> 'cert_4',
			$prefix . '_cert_5'			=> 'cert_5',
			$prefix . '_pr'				=> 'pr',
			$prefix . '_memories'		=> 'memories',
			$prefix . '_tried'			=> 'tried',
			$prefix . '_succeeded'		=> 'succeeded',
			$prefix . '_failed'			=> 'failed',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// オーバーライド
	public function selectFromId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
			->from(
				array('members' => 'm_members'), 
				Class_Model_Tecfolio_MMembers::fieldArray()
			)
			->where('members.id = ?', $id)
			
			->joinLeft(
				array('profile' => 't_profiles'),
				'members.id = profile.m_member_id',
				'*'
			)
			->joinLeft(
					array('syozoku1' => 't_syozoku1'),
					'syozoku1.setti_cd = members.setti_cd AND syozoku1.syozkcd1 = members.gakubucd',
					array('syozoku1_szknam_c' => 'szknam_c')
			)
			->joinLeft(
					array('syozoku2' => 't_syozoku2'),
					'syozoku2.setti_cd = members.setti_cd AND syozoku2.syozkcd1 = members.gakubucd AND syozoku2.syozkcd2 = members.gakka_cd',
					array('syozoku2_szknam_c' => 'szknam_c')
			)
			;
		
		return $this->fetchRow($select);
	}
	
	// オブジェクトが空かどうかを判断できるように取得する
	public function selectProfileFromId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('profile' => 't_profiles'),
				'*'
		)
		->where('members.id = ?', $id)
			
		->joinLeft(
				array('members' => 'm_members'),
				'members.id = profile.m_member_id',
				Class_Model_Tecfolio_MMembers::fieldArray()
		)
		->joinLeft(
				array('syozoku1' => 't_syozoku1'),
				'syozoku1.setti_cd = members.setti_cd AND syozoku1.syozkcd1 = members.gakubucd',
				array('syozoku1_szknam_c' => 'szknam_c')
		)
		->joinLeft(
				array('syozoku2' => 't_syozoku2'),
				'syozoku2.setti_cd = members.setti_cd AND syozoku2.syozkcd1 = members.gakubucd AND syozoku2.syozkcd2 = members.gakka_cd',
				array('syozoku2_szknam_c' => 'szknam_c')
		)
		;
	
		return $this->fetchRow($select);
	}
	
	public function updateFromId($id, $params)
	{
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("m_member_id = ?", $id)
		);
	}
}

