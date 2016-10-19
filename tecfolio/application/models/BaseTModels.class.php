<?php

/**
 * ベースモデル
 */
require_once('BaseModels.class.php');

/**
 * ユーザーモデル共通基底クラス
 *
 * @author		satake
 * @version		0.0.1
*/
class BaseTModels extends BaseModels
{

	/**
	 *	挿入
	 *
	 *	@param	array	$params		設定カラム連想配列(カラム名 => 値)
	 *	@return	integer	追加行数
	 */
	public function insert($params)
	{
		if (!is_array($params))
			$params = array();

		$params["createdate"]	= $params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["creator"]		= $params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;

		return parent::insert($params);
	}

	public function insert_lastupdate($params)
	{
		if (!is_array($params))
			$params = array();

		$params["lastupdate"]		= Zend_Registry::get('nowdatetime');
		$params["lastupdater"]		= Zend_Auth::getInstance()->getIdentity()->id;

		return parent::insert($params);
	}

	/**
	 *	ファイルIDで検索
	 *
	 *	@param	integer	$t_file_id		検索ファイルid
	 *	@return	stdObj
	 */
	public function selectFromFileId($t_file_id)
	{
		$select = $this->select();
		$select->where('t_file_id = ?', $t_file_id);

		return $this->fetchRow($select);
	}

}



