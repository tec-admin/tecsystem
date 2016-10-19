<?php

require_once('BaseTModels.class.php');

class Class_Model_TJyugyoKanri extends BaseTModels
{
	/**
	 * @var string 対応テーブル名
	 */
	const TABLE_NAME	= 't_jyugyo_kanri';
	protected $_name	= Class_Model_TJyugyoKanri::TABLE_NAME;
	protected $_id		= 'jyu_knr_no';
	
	/**
	 *	共通ユニークIDで検索(オーバーライド)
	 *
	 *	@param	character	$jyu_nendo		授業年度
	 *	@param	character	$jyu_knr_no		授業管理番号
	 *	@return	objecs		stdObj
	 */
	public function selectFromId($jyu_knr_no, $jyu_nendo)
	{
		$select = $this->select();
		$select->where('jyu_knr_no = ?', $jyu_knr_no);
		$select->where('jyu_nendo = ?', $jyu_nendo);
		
		return $this->fetchRow($select);
	}
}