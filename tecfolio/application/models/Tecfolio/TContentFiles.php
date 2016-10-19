<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TContentFiles extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_content_files';
	protected $_name   = Class_Model_Tecfolio_TContentFiles::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TContentFiles::TABLE_NAME;

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
	
		$db = $this->getadapter();	// DBアダプタ取得
		$stt = $db->prepare('INSERT INTO t_content_files(data, name, type, filesize, createdate, creator, lastupdate, lastupdater) VALUES(:data, :name, :type, :filesize, :createdate, :creator, :lastupdate, :lastupdater)');
	
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
	
		return $db->lastSequenceId('t_content_files_id_seq');
	}
	
	// t_content_filesからコピー
	public function insertCopyFromContentId($id)
	{
		$creator = Zend_Auth::getInstance()->getIdentity()->id;
		
		$db = $this->getadapter();
		$stt = $db->prepare('INSERT INTO t_content_files(data, name, type, filesize, createdate, creator, lastupdate, lastupdater)
								SELECT f.data, f.name, f.type, f.filesize, :createdate, :creator, :lastupdate, :lastupdater FROM t_content_files f
									LEFT JOIN t_contents c on c.t_content_file_id = f.id
										WHERE c.id = :id');
	
		$stt->bindValue(':id', $id, Zend_Db::PARAM_INT);
		$stt->bindValue(':createdate', Zend_Registry::get('nowdatetime'), Zend_Db::PARAM_STR);
		$stt->bindValue(':creator', $creator, Zend_Db::PARAM_INT);
		$stt->bindValue(':lastupdate', Zend_Registry::get('nowdatetime'), Zend_Db::PARAM_STR);
		$stt->bindValue(':lastupdater', $creator, Zend_Db::PARAM_INT);
	
		$stt->execute();
	
		return $db->lastSequenceId('t_content_files_id_seq');
	}
	
	// t_filesからコピー
	public function insertCopyFromFileId($id)
	{
		$creator = Zend_Auth::getInstance()->getIdentity()->id;
	
		$db = $this->getadapter();
		$stt = $db->prepare('INSERT INTO t_content_files(data, name, type, filesize, createdate, creator, lastupdate, lastupdater)
								SELECT f.data, f.name, f.type, f.filesize, :createdate, :creator, :lastupdate, :lastupdater FROM t_files f
									WHERE f.id = :id');
	
		$stt->bindValue(':id', $id, Zend_Db::PARAM_INT);
		$stt->bindValue(':createdate', Zend_Registry::get('nowdatetime'), Zend_Db::PARAM_STR);
		$stt->bindValue(':creator', $creator, Zend_Db::PARAM_INT);
		$stt->bindValue(':lastupdate', Zend_Registry::get('nowdatetime'), Zend_Db::PARAM_STR);
		$stt->bindValue(':lastupdater', $creator, Zend_Db::PARAM_INT);
	
		$stt->execute();
	
		return $db->lastSequenceId('t_content_files_id_seq');
	}
	
	public function selectFromMythemeId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('content_files'=>'t_content_files'), 
				array('data', 'name')
		)
		->joinLeft(
				array('contents' => 't_contents'),
				'contents.t_content_file_id = content_files.id',
				''
		)
		->joinLeft(
				array('mythemes' => 'm_mythemes'),
				'mythemes.id = contents.m_mytheme_id',
				''
		)
		->where('mythemes.id = ?', $id)
		->where('contents.delete_flag != \'1\'')
		;
	
		return $this->fetchAll($select);
	}
	
	// 引数t_contentsのID(配列)で選択
	public function selectFromMultipleId($t_content_id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('content_files'=>'t_content_files'), 
				array('data', 'name', 'lastupdate')
		)
		->joinLeft(
				array('contents' => 't_contents'),
				'contents.t_content_file_id = content_files.id',
				''
		)
		;
		BaseModels::connectOrWhere($select, 'contents.id', $t_content_id);
	
		return $this->fetchAll($select);
	}
}

