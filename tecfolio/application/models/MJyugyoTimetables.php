<?php

require_once('BaseMModels.class.php');

class Class_Model_MJyugyoTimetables extends BaseMModels
{
	/**
	 * @var string 対応テーブル名
	 */
	const TABLE_NAME = 'm_jyugyo_timetables';
	protected $_name   = Class_Model_MJyugyoTimetables::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MJyugyoTimetables::TABLE_NAME;

		return array(
				$prefix . '_id' => 'id',

				$prefix . '_starttime' => 'starttime',
				$prefix . '_endtime' => 'endtime',

				//$prefix . '_createdate' => 'createdate',
				//$prefix . '_creator' => 'creator',
				//$prefix . '_lastupdate' => 'lastupdate',
				//$prefix . '_lastupdater' => 'lastupdater',
				$prefix . '_display_flg' => 'display_flg',
				$prefix . '_order_num' => 'order_num',
		);
	}
	
	public function getMinAndMax()
	{
		$select = $this->select();
		$select->where('id = (select MIN(id) from m_jyugyo_timetables)');
		$select->orWhere('id = (select MAX(id) from m_jyugyo_timetables)');
//		$select->where('id = (select MIN(id) from m_timetables)');
//		$select->orWhere('id = (select MAX(id) from m_timetables)');
		return $this->fetchAll($select);
	}
}