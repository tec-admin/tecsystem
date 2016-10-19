<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TPortfolioContents extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_portfolio_contents';
	protected $_name	= Class_Model_Tecfolio_TPortfolioContents::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TPortfolioContents::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
				
			$prefix . '_t_portfolio_id' => 't_portfolio_id',
			$prefix . '_t_content_id' => 't_content_id',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
	
	// ポートフォリオIDで削除
	public function deleteFromPortfolioId($id)
	{
		$where = $this->getAdapter()->quoteInto('t_portfolio_id = ?', $id);
	
		return $this->delete($where);
	}
	
	// コンテントIDで削除
	public function deleteFromContentId($id)
	{
		$where = $this->getAdapter()->quoteInto('t_content_id = ?', $id);
	
		return $this->delete($where);
	}
}

