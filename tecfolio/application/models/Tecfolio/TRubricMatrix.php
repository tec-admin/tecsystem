<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TRubricMatrix extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_rubric_matrix';
	protected $_name   = Class_Model_Tecfolio_TRubricMatrix::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TRubricMatrix::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			
			$prefix . '_m_rubric_id' => 'm_rubric_id',
			$prefix . '_vertical' => 'vertical',
			$prefix . '_horizontal' => 'horizontal',
			$prefix . '_rank' => 'rank',
			$prefix . '_description' => 'description',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// ルーブリックIDを元に選択
	public function selectFromRubricId($id)
	{
		$select = $this->select()
			->where('m_rubric_id = ?', $id)
			->order(array('vertical asc', 'horizontal asc'))
			;
	
		return $this->fetchAll($select);
	}
	
	// ポートフォリオIDを元に選択
	public function selectFromPortfolioId($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$sub = $db->select()
		->from(
				array('portfolio'=>'t_portfolio'), 'm_rubric_id'
		)
		->where('id = ?', $id)
		;
		
		$select = $this->select()
		->where('m_rubric_id IN (?)', $sub)
		->order(array('vertical asc', 'horizontal asc'))
		;
	
		return $this->fetchAll($select);
	}
}

