<?php

require_once('BaseTModels.class.php');

// t_files テーブルクラス
class Class_Model_TFiles extends BaseTModels
{
	const TABLE_NAME = 't_files';
	protected $_name   = Class_Model_TFiles::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TFiles::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			$prefix . '_data' => 'data',
			$prefix . '_name' => 'name',
			$prefix . '_type' => 'type',
			$prefix . '_filesize' => 'filesize',
			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}

	public function insertFile($filepath, $name, $type, $size)
	{
		$creator = Zend_Auth::getInstance()->getIdentity()->id;

		//$db = Zend_Db_Table::getDefaultAdapter();	// DBアダプタ取得
		$db = $this->getadapter();	// DBアダプタ取得
		$stt = $db->prepare('INSERT INTO t_files(data, name, type, filesize, createdate, creator, lastupdate, lastupdater) VALUES(:data, :name, :type, :filesize, :createdate, :creator, :lastupdate, :lastupdater)');

		$file = fopen($filepath, 'rb');

		$stt->bindValue(':data', $file, Zend_Db::PARAM_LOB);
		$stt->bindValue(':name', $name, Zend_Db::PARAM_STR);
		$stt->bindValue(':type', $type, Zend_Db::PARAM_STR);
		$stt->bindValue(':filesize', $size, Zend_Db::PARAM_INT);

		$stt->bindValue(':createdate', Zend_Registry::get('nowdatetime'), Zend_Db::PARAM_STR);
		$stt->bindValue(':creator', $creator, Zend_Db::PARAM_INT);
		$stt->bindValue(':lastupdate', Zend_Registry::get('nowdatetime'), Zend_Db::PARAM_STR);
		$stt->bindValue(':lastupdater', $creator, Zend_Db::PARAM_INT);

		$stt->execute();

		return $db->lastSequenceId('t_files_id_seq');
	}

	/**
	 *	最終更新者IDで検索
	 *
	 *	@param	integer	$id		 検索ID
	 *	@return	objecs	stdObj
	 */
	public function selectFromLastupdaterId($id)
	{
		$select = $this->select()
		->from(
				array('files'=>'t_files'),
				array('id', 'name', 'type', 'filesize', 'createdate')
		);
		$select->where('lastupdater = ?', $id);
	
		return $this->fetchAll($select);
	}
	
	// 引数t_contentsのID(配列)で選択
	public function selectFromMultipleId($t_content_id)
	{
		$select = $this->select();
		BaseModels::connectOrWhere($select, 'id', $t_content_id);
	
		return $this->fetchAll($select);
	}
	
	// ポートフォリオで選択済みでないファイルを返す
	public function selectAvailableFromMythemeId($id, $portfolio_id, $m_member_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()
		->from(
				array('pfcontents' => 't_portfolio_contents'), 't_content_id'
		)
		->where('t_portfolio_id = ?', $portfolio_id)
		;
		
		$select = $this->select()
		->from(
				array('files' => 't_files'),
				array('id', 'name', 'type', 'filesize', 'createdate')
		)
		->where('lastupdater = ?', $m_member_id)
		->where('CAST(id AS VARCHAR) NOT IN (?)', $subselect)
		;
		
		return $this->fetchAll($select);
	}
}