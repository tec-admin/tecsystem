<?php
 /**
 * モデル共通基底クラス
 *
 * @author		satake
 * @version		0.0.1
*/
class BaseModels extends Zend_Db_Table_Abstract
{
	/**
	 *	全てを選択
	 *
	 *	@param 	string	$order  順序指定文字列
	 *	@return	objects	stdObj配列
	 */
	public function selectAll($order="")
	{
		if (empty($order))
			$select = $this->select();
		else
			$select = $this->select()->order($order);

		return $this->fetchAll($select);
	}
	
	/**
	 *	全てを選択、order列を持つ表用
	 *
	 *	@param 	string	$order  順序指定文字列
	 *	@return	objects	stdObj配列
	 */
	public function selectAllDisplay()
	{
		$select = $this->select()
		->where("display_flg != 0")
		->order("order_num asc");
	
		return $this->fetchAll($select);
	}

	/**
	 *	指定数選択
	 *
	 *	@param 	string	$order  順序指定文字列
	 *	@return	objects	stdObj配列
	 */
	public function selectLimit($limit=0, $order="")
	{
		if (empty($order))
			$select = $this->select();
		else
			$select = $this->select()->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $this->fetchAll($select);
	}

	/**
	 *	指定数ページ指定選択
	 *
	 *	@param 	string	$order  順序指定文字列
	 *	@return	objects	stdObj配列
	 */
	public function selectPage($page = 1, $limit = 0, $order = "")
	{
		if (empty($order))
			$select = $this->select();
		else
			$select = $this->select()->order($order);

		if ($limit > 0)
			$select->limitPage($page, $limit);

		return $this->fetchAll($select);
	}

	/**
	 *	共通ユニークIDで検索
	 *
	 *	@param	integer	$id		 検索ID
	 *	@return	objecs	stdObj
	 */
	public function selectFromId($id)
	{
		$select = $this->select();
		$select->where('id = ?', $id);

		return $this->fetchRow($select);
	}
	
	/**
	 *	最終更新者IDで検索
	 *
	 *	@param	integer	$id		 検索ID
	 *	@return	objecs	stdObj
	 */
	public function selectFromLastupdaterId($id)
	{
		$select = $this->select();
		$select->where('lastupdater = ?', $id);
	
		return $this->fetchAll($select);
	}

	/**
	 *	共通ユニークIDで削除
	 *
	 *	@param	integer	$id		削除ID
	 *	@return	integer	削除ID
	 */
	public function deleteFromId($id)
	{
		$where = $this->getAdapter()->quoteInto('id = ?', $id);

		return $this->delete($where);
	}

	/**
	 *	全て削除
	 *
	 *	@param	integer	$id		削除ID
	 *	@return	integer	削除ID
	 */
	public function deleteAll()
	{
		return $this->delete('1=1');
	}

	
	// オブジェクトですべて取得
	public function GetSelectFromAll($limit=0, $order=array())
	{
		$db = Zend_Db_Table::getDefaultAdapter();

		$select = $this->select()->setIntegrityCheck(false)
				->from(array('me' => $this->_name), '*');

		if (count($order) > 0)
			$select->order($order);

		if ($limit > 0)
			$select->limit($limit, 0);

		return $select;
	}
	
	/**
	 *	更新
	 *
	 *	@param	integer	$id			更新レコードid
	 *	@param	array	$params		設定カラム連想配列(カラム名 => 値)
	 *	@return	integer	更新行数
	 */
	public function updateFromId($id, $params)
	{
		if (!is_array($params))
			$params = array();
	
		$params["lastupdate"]	= Zend_Registry::get('nowdatetime');
		if (!empty(Zend_Auth::getInstance()->getIdentity()->id))
			$params["lastupdater"]	= Zend_Auth::getInstance()->getIdentity()->id;
	
		$count = $this->update(
				$params,
				$this->getAdapter()->quoteInto("id = ?", $id)
		);
	}
	
	/**
	 *	SELEC文から挿入
	 *
	 *	@param $tableName	テーブル名
	 *	@param $select		SELECT文
	 *	@param $fields		挿入する列
	 */
	public function insertSelect($tableName, Zend_Db_Select $select, array $fields = array())
	{
		$fieldString = '';
		if (count($fields))
		{
			foreach($fields as $fieldKey => $field)
			{
				$fields[$fieldKey] =  $this->getAdapter()->quoteIdentifier($field);
			}
	
			$fieldString = ' (' . implode(',', $fields) . ') ';
		}
	
		$query  = "INSERT INTO ". $this->getAdapter()->quoteIdentifier($tableName) . $fieldString . $select;
	
		$this->_db->query($query);
	}
	
	/**
	 *	多次元連想配列から挿入
	 *
	 *	@param $tableName	テーブル名
	 *	@param $data		挿入するデータの配列
	 *	@param $fields		挿入する列
	 */
	public function insertMultiple($tableName, array $data = array(), array $fields = array())
	{
		$fieldString = '';
		if (count($fields))
		{
			foreach($fields as $fieldKey => $field)
			{
				$fields[$fieldKey] =  $this->getAdapter()->quoteIdentifier($field);
			}
	
			$fieldString = ' (' . implode(',', $fields) . ')';
		}
	
		$queryVals = array();
		foreach ($data as $row) {
			foreach($row as &$col) {
				$col = $this->getAdapter()->quote($col);
			}
			$queryVals[] = '(' . implode(',', $row) . ')';
		}
	
		$query = 'INSERT INTO ' . $this->getAdapter()->quoteIdentifier($tableName) . fieldString . ' VALUES ' . implode(',', $queryVals);
	
		$this->_db->query($query);
	}
	
	// 配列の値によりORで選択
	// @param	Zend_Db_Select $select
	// @param	$rowname	対象列
	// @param	$values		配列
	// @return	なし(引数$selectを直接変更する)
	public function connectOrWhere(Zend_Db_Select $select, $rowname, array $values)
	{
		//配列の最初にポインタを移動
		reset($values);
		//このときのキーを取得
		$array_first_info = each($values);
		$array_first_key = $array_first_info["key"];
		
		//配列の最後にポインタを移動
		end($values);
		//このときのキーを取得
		$array_last_info = each($values);
		$array_last_key = $array_last_info["key"];
		
		foreach($values as $key => $value)
		{
			if($key === $array_first_key && $key === $array_last_key)
				$select->where($rowname . ' = ?', $value);
			elseif($key === $array_first_key)
				$select->where('(' . $rowname . ' = ?', $value);
			elseif($key === $array_last_key)
				$select->orWhere($rowname . ' = ?)', $value);
			else
				$select->orWhere($rowname . ' = ?', $value);
		}
	}
	
	// 配列の値によりORで選択(LIKE検索)
	// @param	Zend_Db_Select $select
	// @param	$rowname	対象列
	// @param	$values		配列
	// @return	なし(引数$selectを直接変更する)
	public function connectOrWhereWithLIKE(Zend_Db_Select $select, $rowname, array $values)
	{
		//配列の最初にポインタを移動
		reset($values);
		//このときのキーを取得
		$array_first_info = each($values);
		$array_first_key = $array_first_info["key"];
	
		//配列の最後にポインタを移動
		end($values);
		//このときのキーを取得
		$array_last_info = each($values);
		$array_last_key = $array_last_info["key"];
	
		foreach($values as $key => $value)
		{
			if($key === $array_first_key && $key === $array_last_key)
				$select->where($rowname . ' LIKE ?', '%' . $value . '%');
			elseif($key === $array_first_key)
				$select->where('(' . $rowname . ' LIKE ?', '%' . $value . '%');
			elseif($key === $array_last_key)
				$select->orWhere($rowname . ' LIKE ?)', '%' . $value . '%');
			else
				$select->orWhere($rowname . ' LIKE ?', '%' . $value . '%');
		}
	}

}



