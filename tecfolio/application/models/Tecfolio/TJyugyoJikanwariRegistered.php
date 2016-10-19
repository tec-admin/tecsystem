<?php

require_once(dirname(__FILE__) . '/../BaseMModels.class.php');

class Class_Model_Tecfolio_TJyugyoJikanwariRegistered extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME		= 't_jyugyo_jikanwari_registered';
	protected $_name		= Class_Model_Tecfolio_TJyugyoJikanwariRegistered::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TJyugyoJikanwariRegistered::TABLE_NAME;
	
		return array(
				$prefix . '_jyu_nendo' 		=> 'jyu_nendo',
				$prefix . '_jyu_knr_no' 	=> 'jyu_knr_no',
				$prefix . '_jwari_seq' 		=> 'jwari_seq',
				$prefix . '_riyou_kn' 		=> 'riyou_kn',
				$prefix . '_jyugyo_seq' 	=> 'jyugyo_seq',
				$prefix . '_sykjyudy' 		=> 'sykjyudy',
				$prefix . '_sykjigen' 		=> 'sykjigen',
				$prefix . '_sykjzgkn' 		=> 'sykjzgkn',
				$prefix . '_jyugyody' 		=> 'jyugyody',
				$prefix . '_jigen'			=> 'jigen',
				$prefix . '_jzengokn' 		=> 'jzengokn',
				$prefix . '_jikoku_f' 		=> 'jikoku_f',
				$prefix . '_jikoku_t' 		=> 'jikoku_t',
					
				$prefix . '_display_flg' 	=> 'display_flg',
				$prefix . '_lastupdate' 	=> 'lastupdate',
				$prefix . '_lastupdater' 	=> 'lastupdater',
				
				$prefix . '_title'		 	=> 'title',
				$prefix . '_memo'		 	=> 'memo',
		);
	}
	
	
	public static function fieldArrayForInsert($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TJyugyoJikanwariRegistered::TABLE_NAME;

		return array(
			$prefix . '_jyu_nendo' 		=> 'jyu_nendo',
			$prefix . '_jyu_knr_no' 	=> 'jyu_knr_no',
			$prefix . '_jwari_seq' 		=> 'jwari_seq',
			$prefix . '_riyou_kn' 		=> 'riyou_kn',
			$prefix . '_jyugyo_seq' 	=> 'jyugyo_seq',
			$prefix . '_sykjyudy' 		=> 'sykjyudy',
			$prefix . '_sykjigen' 		=> 'sykjigen',
			$prefix . '_sykjzgkn' 		=> 'sykjzgkn',
			$prefix . '_jyugyody' 		=> 'jyugyody',
			$prefix . '_jigen'			=> 'jigen',
			$prefix . '_jzengokn' 		=> 'jzengokn',
			$prefix . '_jikoku_f' 		=> 'jikoku_f',
			$prefix . '_jikoku_t' 		=> 'jikoku_t',
			
			$prefix . '_display_flg' 	=> 'display_flg',
			$prefix . '_lastupdate' 	=> 'lastupdate',
			$prefix . '_lastupdater' 	=> 'lastupdater',
		);
	}
	
	// 授業科目設定時、同時に授業時間割表の該当項目をコピーする
	public function insertFromTJyugyoJikanwari($jyu_knr_no)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$subselect = $db->select()
		->from(
				array('registered' => 't_jyugyo_jikanwari_registered'),
				array('jyu_nendo', 'jyu_knr_no')
		);
	
		$select = $db->select()
		->from(
				array('jikanwari' => 't_jyugyo_jikanwari'),
				array('jyu_nendo', 'jyu_knr_no', 'jwari_seq', 'riyou_kn',
						'jyugyo_seq', 'sykjyudy', 'sykjigen', 'sykjzgkn', 'jyugyody',
						'jigen', 'jzengokn', 'jikoku_f', 'jikoku_t', 'display_flg')
		)
		;
	
		$str = "";
		foreach($jyu_knr_no as $k => $val)
		{
			$str .= "'" . $val . "',";
		}
	
		$select->where('jikanwari.jyu_knr_no IN ('. substr($str, 0, -1) . ')');
		$select->where('(jikanwari.jyu_nendo, jikanwari.jyu_knr_no) NOT IN (?)', $subselect);
	
		$select->columns(array('lastupdate' => new Zend_Db_Expr("to_timestamp('" . Zend_Registry::get('nowdatetime') . "', 'YYYY/MM/DD HH24:MI:SS')")));
		$select->columns(array('lastupdater' => new Zend_Db_Expr("'" . Zend_Auth::getInstance()->getIdentity()->id . "'")));
	
		$this->insertSelect('t_jyugyo_jikanwari_registered', $select, $this::fieldArrayForInsert());
	}
	
	// 選択された授業科目の時間割データを取得する
	public function selectFromNendoAndKnrno($jyu_nendo, $jyu_knr_no)
	{
		$select = $this->select()
		->from(
				array('jyugyo' => $this->_name),
				'*'
		)
			
		->where('jyu_nendo = ?', $jyu_nendo)
		->where('jyu_knr_no = ?', $jyu_knr_no)
		;
		
		return $this->fetchAll($select);
	}
}