<?php

require_once('BaseMModels.class.php');

/**
 * 大分類（マスター）モデルクラス
 *
 * 対応テーブル : m_dockinds
 *
 * @author		satake
 * @version		0.0.1
 */
class Class_Model_MDockinds extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_dockinds';
	protected $_name   = Class_Model_MDockinds::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MDockinds::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_document_category' => 'document_category',

			//$prefix . '_createdate' => 'createdate',
			//$prefix . '_creator' => 'creator',
			//$prefix . '_lastupdate' => 'lastupdate',
			//$prefix . '_lastupdater' => 'lastupdater',
			$prefix . '_display_flg' => 'display_flg',
			$prefix . '_order_num' => 'order_num',
		);
	}

	public static function fieldArrayTwc($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MDockinds::TABLE_NAME;

		return array(
				$prefix . '_id' => 'id',

				$prefix . '_document_category' => 'document_category',

// 				$prefix . '_createdate' => 'createdate',
// 				$prefix . '_creator' => 'creator',
// 				$prefix . '_lastupdate' => 'lastupdate',
// 				$prefix . '_lastupdater' => 'lastupdater',
				$prefix . '_shiftclass' => 'shiftclass',
				$prefix . '_clipped_form' => 'clipped_form',
				$prefix . '_display_flg' => 'display_flg',
				$prefix . '_order_num' => 'order_num',
		);
	}
	// 引数dockindidでの予約が存在するかを調べる
	// @param	dockindid	文書ID
	// @return				dockindidに一致する予約件数
	public function getReservedDockindId($dockindid)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('dockinds' => $this->_name), "*")
				->join(
						array('shifts' => 'm_shifts'),
						'shifts.m_dockind_id = dockinds.id',
						Class_Model_MShifts::fieldArray()
				)
				->join(
						array('reserves' => 't_reserves'),
						'reserves.m_shift_id = shifts.id',
						Class_Model_TReserves::fieldArray()
				);
		$select->where('dockinds.id = ?', $dockindid);
		$select->order('dockinds.id asc');
		
		return count($this->fetchAll($select));
	}
	
	// 運営画面用にdisplay_flgを無視して全取得
	public function selectAllFromShiftclassId($shiftclass)
	{
		$select = $this->select();
		$select->where('shiftclass = ?', $shiftclass);
		$select->order('order_num asc');
		
		return $this->fetchAll($select);
	}
}

