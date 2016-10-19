<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TRubricInput extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_rubric_input';
	protected $_name   = Class_Model_Tecfolio_TRubricInput::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TRubricInput::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			
			$prefix . '_t_portfolio_id' => 't_portfolio_id',
			$prefix . '_vertical' => 'vertical',
			$prefix . '_rank' => 'rank',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	public function selectFromPortfolioId($id)
	{
		$select = $this->select()
			->where('t_portfolio_id = ?', $id)
			;
	
		return $this->fetchAll($select);
	}
	
	public function deleteFromPortfolioId($id)
	{
		$where = $this->getAdapter()->quoteInto('t_portfolio_id = ?', $id);
	
		return $this->delete($where);
	}
}

