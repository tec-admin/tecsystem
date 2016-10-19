<?php

require_once('BaseMModels.class.php');

class Class_Model_MLShiftclasses extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_l_shiftclasses';
	protected $_name   = Class_Model_MLShiftclasses::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MLShiftclasses::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_l_class_name' => 'l_class_name',
			$prefix . '_m_shiftclass_id' => 'm_shiftclass_id',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
			$prefix . '_display_flg' => 'display_flg',
			$prefix . '_order_num' => 'order_num',
		);
	}
	
	public function selectFromShiftclassId($id)
	{
		$select = $this->select();
		$select->where('m_shiftclass_id = ?', $id);
	
		return $this->fetchRow($select);
	}
	
	public function getShiftclassFromDockindId($m_dockind_id, $order='id')
	{
		$db = Zend_Db_Table::getDefaultAdapter();
	
		$shiftclass = $db->select()->from(
				array('dockinds' => 'm_dockinds'),
				array('dockinds.shiftclass'))
				->where('dockinds.id = ?', $m_dockind_id)
				;
	
		$select = $db->select()->from(
				array('l_shiftclasses' => 'm_l_shiftclasses'),
				array('l_shiftclasses.m_shiftclass_id'))
				->where(new Zend_Db_Expr("CAST(SUBSTR(l_shiftclasses.m_shiftclass_id,1,1) AS int8)") . ' = ?', $shiftclass)
				->orWhere(new Zend_Db_Expr("CASE SUBSTR(l_shiftclasses.m_shiftclass_id,3,1) WHEN '' THEN 0 ELSE CAST(SUBSTR(l_shiftclasses.m_shiftclass_id,3,1) AS int8) END") . ' = ?', $shiftclass)
				->order($order)
				;
		
		return $db->fetchRow($select);
	}
}

