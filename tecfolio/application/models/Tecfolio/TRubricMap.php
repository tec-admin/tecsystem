<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TRubricMap extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_rubric_map';
	protected $_name   = Class_Model_Tecfolio_TRubricMap::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TRubricMap::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			
			$prefix . '_parent_id' => 'parent_id',
			$prefix . '_m_rubric_id' => 'm_rubric_id',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	public static function fieldArrayForInsert($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TRubricMap::TABLE_NAME;
	
		return array(
				$prefix . '_parent_id' => 'parent_id',
				$prefix . '_m_rubric_id' => 'm_rubric_id',
				
				$prefix . '_original_flag' => 'original_flag',
	
				$prefix . '_createdate' => 'createdate',
				$prefix . '_creator' => 'creator',
				$prefix . '_lastupdate' => 'lastupdate',
				$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// 親IDとルーブリックマスタIDから削除
	public function deleteFromParentIdAndRubricId($parent_id, $m_rubric_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$where = array(
				$db->quoteInto('parent_id = ?', $parent_id),
				$db->quoteInto('m_rubric_id = ?', $m_rubric_id)
		);
	
		return $db->delete($this->_name, $where);
	}
	
	// 親IDから選択
	// ※親IDは現状(-2015/08/31)ではMyテーマIDのみ(増える可能性有)
	public function selectFromParentId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('rubric_map'=>'t_rubric_map'), 'm_rubric_id'
		)
		->where('parent_id = ?', $id)
		->joinLeft(
				array('mytheme' => 'm_mythemes'),
				'rubric_map.parent_id = mytheme.id',
				''
		)
		->joinLeft(
				array('rubric' => 'm_rubric'),
				'rubric_map.m_rubric_id = rubric.id',
				array('m_rubric_name' => 'name')
		)
		;
	
		return $this->fetchAll($select);
	}
	
	// MyテーマIDから選択
	public function selectFromMythemeId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('rubric_map'=>'t_rubric_map'),
				array('m_rubric_id', 'lastupdate')
		)
		->where('parent_id = ?', $id)
		->joinLeft(
				array('mytheme' => 'm_mythemes'),
				'rubric_map.parent_id = mytheme.id',
				''
		)
		->joinLeft(
				array('rubric' => 'm_rubric'),
				'rubric_map.m_rubric_id = rubric.id',
				Class_Model_Tecfolio_MRubric::fieldArray()
		)
		->joinLeft(
				array('license' => 't_rubric_license'),
				'rubric.t_rubric_license_id = license.id',
				array('license_name' => 'name', 'export_flag' => 'export_flag')
		)
		->order(array('rubric_map.lastupdate asc'))
		;
		
		return $this->fetchAll($select);
	}
	
	// 親IDとルーブリックIDの配列から選択
	public function selectFromParentIdAndMultipleRubricId($parent_id, $rubric_id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('rubric_map'=>'t_rubric_map'), 'm_rubric_id'
		)
		->joinLeft(
				array('mytheme' => 'm_mythemes'),
				'rubric_map.parent_id = mytheme.id',
				array('m_mythemes_name' => 'name')
		)
		->joinLeft(
				array('rubric' => 'm_rubric'),
				'rubric_map.m_rubric_id = rubric.id',
				Class_Model_Tecfolio_MRubric::fieldArray()
		)
		->where('parent_id = ?', $parent_id)
		;
		
		BaseModels::connectOrWhere($select, 'rubric_map.m_rubric_id', $rubric_id);
		
		return $this->fetchAll($select);
	}
	
	// 授業科目設定時、科目マスタ表のIDをベースにサンプルルーブリックを配置する
	public function insertFromMSubjects($kyoincd, $jyu_knr_no, $jwaricd)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$select = $db->select()->distinct()
		->from(
				array('subjects' => 'm_subjects'),
				array('parent_id' => "('SUBJ_' || jyu_nendo || jyu_knr_no || kyoincd || jwaricd)", 'm_rubric_id' => new Zend_Db_Expr('1'), 'original_flag' => new Zend_Db_Expr('0'))
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
		
		$this->insertSelect('t_rubric_map', $select, $this::fieldArrayForInsert());
	}
}

