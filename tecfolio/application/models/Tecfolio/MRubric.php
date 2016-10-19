<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_MRubric extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_rubric';
	protected $_name   = Class_Model_Tecfolio_MRubric::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_MRubric::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			
			$prefix . '_original_id' => 'original_id',
			$prefix . '_name' => 'name',
			$prefix . '_theme' => 'theme',
			$prefix . '_memo' => 'memo',
			$prefix . '_original_name_jp' => 'original_name_jp',
			$prefix . '_editor_name_jp' => 'editor_name_jp',
			$prefix . '_t_rubric_license_id' => 't_rubric_license_id',
			$prefix . '_published_flag' => 'published_flag',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// IDより選択(ライセンス含む)
	public function selectFromIdIncludingLicense($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('rubric' => 'm_rubric'),
				'*'
		)
		->where('rubric.id = ?', $id)
		->joinLeft(
				array('rubric_license' => 't_rubric_license'),
				'rubric.t_rubric_license_id = rubric_license.id',
				Class_Model_Tecfolio_TRubricLicense::fieldArray()
		)
		;
		return $this->fetchRow($select);
	}
	
	// IDより選択
	public function selectMatrixFromId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('rubric' => 'm_rubric'), 
				Class_Model_Tecfolio_MRubric::fieldArray()
		)
		->where('rubric.id = ?', $id)
		->joinLeft(
				array('rubric_matrix' => 't_rubric_matrix'),
				'rubric.id = rubric_matrix.m_rubric_id',
				array('vertical', 'horizontal', 'rank', 'description')
		)
		->order(array('vertical asc', 'horizontal asc'))
		;
		
		return $this->fetchRow($select);
	}
}

