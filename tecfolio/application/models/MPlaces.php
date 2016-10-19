<?php

require_once('BaseMModels.class.php');

/**
 * 大分類（マスター）モデルクラス
 *
 * 対応テーブル : m_places
 *
 * @author		satake
 * @version		0.0.1
 */
class Class_Model_MPlaces extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_places';
	protected $_name   = Class_Model_MPlaces::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MPlaces::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_consul_place' => 'consul_place',
			$prefix . '_m_campus_id' => 'm_campus_id',

			//$prefix . '_createdate' => 'createdate',
			//$prefix . '_creator' => 'creator',
			//$prefix . '_lastupdate' => 'lastupdate',
			//$prefix . '_lastupdater' => 'lastupdater',
			$prefix . '_display_flg' => 'display_flg',
			$prefix . '_order_num' => 'order_num',
		);
	}

	// シフトの1グループ（m_term_id, m_dockind_id, m_place_id でユニークなもの）取得
	public function selectFromCampusId($m_campus_id)
	{
		$select = $this->select();
		$select->where('m_campus_id = ?', $m_campus_id);
		//displayflgが0でないもの
		$select->where("display_flg != 0");
		return $this->fetchAll($select);
	}
	
	// 運営画面用にdisplay_flgを無視して全取得
	public function selectAllFromCampusId($m_campus_id)
	{
		$select = $this->select();
		$select->where('m_campus_id = ?', $m_campus_id);
		$select->order('order_num asc');
		return $this->fetchAll($select);
	}
	
	// 引数placeidでの予約が存在するかを調べる
	// @param	placeid		場所ID
	// @return				placeidに一致する予約件数
	public function getReservedPlaceId($placeid)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('places' => $this->_name), "*")
				->join(
						array('shifts' => 'm_shifts'),
						'shifts.m_place_id = places.id',
						Class_Model_MShifts::fieldArray()
				)
				->join(
						array('reserves' => 't_reserves'),
						'reserves.m_shift_id = shifts.id',
						Class_Model_TReserves::fieldArray()
				);
		$select->where('places.id = ?', $placeid);
		$select->order('places.id asc');
		
		return count($this->fetchAll($select));
	}

}

