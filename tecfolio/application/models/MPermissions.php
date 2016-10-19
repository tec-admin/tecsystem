<?php

require_once('BaseMModels.class.php');

class Class_Model_MPermissions extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_permissions';
	protected $_name   = Class_Model_MPermissions::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MPermissions::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_m_member_roles' => 'm_member_roles',
			$prefix . '_role_jp' => 'role_jp',
			$prefix . '_role_jp_clipped_form' => 'role_jp_clipped_form',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
			$prefix . '_display_flg' => 'display_flg',
			$prefix . '_order_num' => 'order_num',
			
			$prefix . '_search_flg' => 'search_flg',
		);
	}
	// 検索用一覧を返す
	public function selectAllForSearch()
	{
		$select = $this->select();
		$select->where('search_flg = 1');
		$select->order('order_num asc');
		return $this->fetchAll($select);
	}
}