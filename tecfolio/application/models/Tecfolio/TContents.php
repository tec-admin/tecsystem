<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TContents extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_contents';
	protected $_name   = Class_Model_Tecfolio_TContents::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TContents::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_m_mytheme_id' => 'm_mytheme_id',
			$prefix . '_t_content_file_id' => 't_content_file_id',
			$prefix . '_uploaded_by' => 'uploaded_by',
			$prefix . '_plan_name' => 'plan_name',
			$prefix . '_delete_flag' => 'delete_flag',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}

	// MyテーマIDから選択
	public function selectFromMythemeId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
			->from(
				array('contents'=>'t_contents'),
				'*'
			)
			->where('m_mytheme_id = ?', $id)
			->where('delete_flag != ?', '1')
			->joinLeft(
				array('content_files' => 't_content_files'),
				'contents.t_content_file_id = content_files.id',
				array('content_files_id' => 'id', 'content_files_name' => 'name', 'content_files_type' => 'type', 'content_files_filesize' => 'filesize', 'content_files_createdate' => 'createdate')
			)
			->order('lastupdate asc')
			;

		return $this->fetchAll($select);
	}

	// MyテーマIDからアップロードファイルと文献情報を取得
	public function selectQuotedFromMythemeId($id, $order, $asc)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('contents'=>'t_contents'),
				'*'
		)
		->where('m_mytheme_id = ?', $id)
		->where('delete_flag != ?', '1')
		->joinLeft(
				array('content_files' => 't_content_files'),
				'contents.t_content_file_id = content_files.id',
				array('available_name' =>
						new Zend_Db_Expr("CASE WHEN \"content_files\".name IS NOT NULL THEN \"content_files\".name
												WHEN ref_title IS NOT NULL THEN ref_title ELSE '' END"),
					'content_files_id' => 'id', 'content_files_name' => 'name', 'content_files_type' => 'type', 'content_files_filesize' => 'filesize', 'content_files_createdate' => 'createdate')
		)
		->joinLeft(
				array('portfolio_contents' => new Zend_Db_Expr("(SELECT t_content_id, count(id) AS cnt FROM t_portfolio_contents GROUP BY t_content_id)")),
				'contents.id = portfolio_contents.t_content_id',
				array('portfolio_contents_count' => 'cnt')
		)
		;

		if($asc == 1)
			$direction = ' asc';
		else
			$direction = ' desc';

		if(!empty($order))
			$select->order($order . $direction);
		else
			$select->order('createdate asc');

		return $this->fetchAll($select);
	}
	
	// MyテーマIDからアップロードファイルと文献情報を取得
	public function selectQuotedSubjectFromMythemeId($id, $order, $asc, $m_member_id, $staff_no, $act_name)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('contents'=>'t_contents'),
				'*'
		)
		->joinLeft(
				array('content_files' => 't_content_files'),
				'contents.t_content_file_id = content_files.id',
				array('available_name' =>
						new Zend_Db_Expr("CASE WHEN \"content_files\".name IS NOT NULL THEN \"content_files\".name
												WHEN ref_title IS NOT NULL THEN ref_title ELSE '' END"),
						'content_files_id' => 'id', 'content_files_name' => 'name', 'content_files_type' => 'type', 'content_files_filesize' => 'filesize', 'content_files_createdate' => 'createdate')
		)
		->joinLeft(
				array('portfolio_contents' => new Zend_Db_Expr("(SELECT t_content_id, count(id) AS cnt FROM t_portfolio_contents GROUP BY t_content_id)")),
				'contents.id = portfolio_contents.t_content_id',
				array('portfolio_contents_count' => 'cnt')
		)
		->joinLeft(
				array('subjects' => 'm_subjects_registered'),
				'subjects.id = contents.m_mytheme_id',
				''
		)
		->joinLeft(
				array('kyoin_map' => 't_kyoin8_5_m'),
				'subjects.kyoincd = kyoin_map.ky_kyoincd',
				''
		)
		->where('contents.m_mytheme_id = ?', $id)
		->where('contents.delete_flag != ?', '1')
		;
		
		if(!empty($act_name))
		{
			$select->where('(contents.publicity = ?', '1')
			->orWhere('kyoin_map.ky_jkyoincd8 = ?', $staff_no)
			->orWhere('contents.creator = ?)', $m_member_id)
			;
		}
	
		if($asc == 1)
			$direction = ' asc';
		else
			$direction = ' desc';
	
		if(!empty($order))
			$select->order($order . $direction);
		else
			$select->order('createdate asc');
	
		return $this->fetchAll($select);
	}

	// ポートフォリオで選択済みでないファイルを返す
	public function selectAvailableFromMythemeId($id, $portfolio_id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()
		->from(
				array('pfcontents' => 't_portfolio_contents'), 't_content_id'
		)
		->where('t_portfolio_id = ?', $portfolio_id)
		;

		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('contents'=>'t_contents'), '*'
		)
		->joinLeft(
				array('content_files' => 't_content_files'),
				'contents.t_content_file_id = content_files.id',
				array('content_files_id' => 'id', 'content_files_name' => 'name', 'content_files_type' => 'type', 'content_files_filesize' => 'filesize', 'content_files_createdate' => 'createdate')
		)
		->where('m_mytheme_id = ?', $id)
		->where('delete_flag != ?', '1')
		->where('contents.id NOT IN (?)', $subselect)
		->order('createdate asc');
		;

		return $this->fetchAll($select);
	}
	
	// (授業科目用)ポートフォリオで選択済みでないファイルを返す
	public function selectAvailableFromMythemeIdForSubject($id, $portfolio_id, $m_member_id, $staff_no)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$subselect = $db->select()
		->from(
				array('pfcontents' => 't_portfolio_contents'), 't_content_id'
		)
		->where('t_portfolio_id = ?', $portfolio_id)
		;
	
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('contents'=>'t_contents'), '*'
		)
		->joinLeft(
				array('content_files' => 't_content_files'),
				'contents.t_content_file_id = content_files.id',
				array('content_files_id' => 'id', 'content_files_name' => 'name', 'content_files_type' => 'type', 'content_files_filesize' => 'filesize', 'content_files_createdate' => 'createdate')
		)
		->joinLeft(
				array('subjects' => 'm_subjects_registered'),
				'subjects.id = contents.m_mytheme_id',
				''
		)
		->joinLeft(
				array('kyoin_map' => 't_kyoin8_5_m'),
				'subjects.kyoincd = kyoin_map.ky_kyoincd',
				''
		)
		->where('m_mytheme_id = ?', $id)
		->where('delete_flag != ?', '1')
		->where('(contents.publicity = ?', '1')
		->orWhere('kyoin_map.ky_jkyoincd8 = ?', $staff_no)
		->orWhere('contents.creator = ?)', $m_member_id)
		->where('contents.id NOT IN (?)', $subselect)
		->order('createdate asc')
		;
	
		return $this->fetchAll($select);
	}
	
	// ゴミ箱のファイルを取得
	public function selectRemovedFromMythemeId($id)
	{
		$select = $this->select()->setIntegrityCheck(false)
		->from(
				array('contents'=>'t_contents'),
				'*'
		)
		->where('m_mytheme_id = ?', $id)
		->where('delete_flag != ?', '0')
		->joinLeft(
				array('content_files' => 't_content_files'),
				'contents.t_content_file_id = content_files.id',
				array('content_files_id' => 'id', 'content_files_name' => 'name', 'content_files_type' => 'type', 'content_files_filesize' => 'filesize', 'content_files_createdate' => 'createdate')
		)
		->order('createdate asc')
		;

		return $this->fetchAll($select);
	}
	
	/**
	 *	アップデート関数の拡張
	 *	ポートフォリオで選択されているコンテンツは更新しない
	 *
	 *	@param	integer	$id			更新レコードid
	 *	@param	array	$params		設定カラム連想配列(カラム名 => 値)
	 *	@return	integer	更新行数
	 */
	public function updateNotPortfolioFromId($id, $params)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$select = $db->select()
		->from(
				array('contents'=>'t_contents'),
				'id'
		)
		->joinLeft(
				array('portfolio_contents' => new Zend_Db_Expr("(SELECT t_content_id, count(id) AS cnt FROM t_portfolio_contents GROUP BY t_content_id)")),
				'contents.id = portfolio_contents.t_content_id',
				''
		)
		->where('contents.id = ?', $id)
		->where('delete_flag != ?', '1')
		->where('portfolio_contents.cnt IS NULL')
		;
		
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("id = (?)", $select)
		);
		
		return $count;
	}
}

