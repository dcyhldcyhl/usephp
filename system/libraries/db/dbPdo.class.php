<?php
namespace system\lib\db{

	use system\core as core;
	use PDO;
	use PDOException;
	/**
	 * mysqli数据库连接类
	 * @author huanglin
	 *
	 */
	class dbPdo extends core\base 
	{
		
		
		/**
		 * 数据库主库连接
		 * 
		 * @var object
		 */
		private static $db_master_link;
		
		/**
		 * 数据库从库连接
		 * 
		 * @var object
		 */
		private static $db_slave_link;
		
		/**
		 * 事务处理开启状态
		 * 
		 * @var boolean
		 */
		private static $Transactions;
		
			/**
		 * 数据表名
		 * 
		 * @var string
		 */
		private static $table_name;
		
			
		
		/**
		 * 返回主库的连接串
		 * @return object
		 */
		private static function get_master_link()
		{
			if(!self::$db_master_link)
			{
				$db_config = core\main::getConfigKey('pdodb');
				
				$params = $db_config['master'];				
				
				self::$db_master_link = new PDO($params['dsn'], $params['username'], $params['password']);
				
				if(!self::$db_master_link)
				{
					core\Exception::ErrorPage('Mysql Master Server connect fail');
				}
				
				//设置数据库编码
				self::$db_master_link->query("SET NAMES {$params['charset']}");
			}
			
			return self::$db_master_link;
		}
		
		/**
		 * 返回从库的连接串
		 * @return object
		 */
		private static function get_slave_link()
		{
			if(!self::$db_slave_link)
			{
				$db_config = core\main::getConfigKey('pdodb');
				
				if(isset($db_config['slave']))
				{
					$params = $db_config['slave'][rand(0, count($db_config['slave'])-1)];//从几个从库里取一个
				}
				else
				{
					$params = $db_config['master'];
				}
				try
				{
					self::$db_slave_link = new PDO($params['dsn'], $params['username'], $params['password']);
				}
				catch (PDOException $e)
				{
					core\Exception::ErrorPage('Error:'.$e->getMessage()."<br>ErrorCode:".$e->getCode()."<br>Line:".$e->getLine()."<br>File:".$e->getFile(),500);
				}
				
				//设置数据库编码
				if(isset($params['charset'])&&!empty($params['charset']))
				{
					self::$db_slave_link->query("SET NAMES {$params['charset']}");
				}
			}
			
			return self::$db_slave_link;
		}
		
		/**
		 * 字符串转义函数
		 * 
		 * SQL语句指令安全过滤,用于字符转义
		 * @access public
		 * @param mixed $value 所要转义的字符或字符串,注：参数支持数组
		 * @return string|string
		 */
		public static function quoteInto($value) 
		{
			//参数是否为数组
			if (is_array($value)) 
			{			
				foreach ($value as $key=>$string) 
				{
					$value[$key] = self::quoteInto($string);
				}
			} 
			else 
			{
				//当参数为字符串或字符时
				if (is_string($value)) 
				{
					$value = '\'' . mysql_real_escape_string($value) . '\'';
				}
			}
			
			return $value;
		}
		
		/**
		 * 执行SQL语句
		 * 
		 * SQL语句执行函数.
		 * @access public
		 * @param string $sql SQL语句内容
		 * @return mixed
		 */
		public static function query($sql,$master=false) 
		{
			
			//参数分析
			if (!$sql) 
			{
				return false;
			}
			
			$fieldArr = explode(" ", ltrim($sql));
			
			$optType = trim(strtolower(array_shift($fieldArr)));
			
			//获取执行结果
			
			if($optType == "select" && $master == false)
			{
				$db = self::get_slave_link();
			}
			else 
			{
				$db = self::get_master_link();
			}
			$result = $db->query($sql);
			//日志操作,当调试模式开启时,将所执行过的SQL写入SQL跟踪日志文件,便于DBA进行MYSQL优化.若调试模式关闭,当SQL语句执行错误时写入日志文件
			
			if ($result == false) 
			{
				if (core\main::getConfigKey('debug') === true)
				{
					//获取当前运行的错误信息
					$errorInfo =$db->errorInfo();
					$message = 'SQL  failed :' . $sql.'<br/>Error Code:'.$db->errorCode().'<br/>Error Message:'.$errorInfo[2];
					core\Exception::ErrorPage($message,500);
				}
			}
					
			return $result;
		}
		
		
		
		/**
		 * 通过一个SQL语句获取全部信息(字段型)
		 * 
		 * @access public
		 * @param string $sql SQL语句
		 * @return array
		 */
		public static function getAll($sql,$master=false) 
		{
			
			//参数分析
			if (!$sql) 
			{
				return false;
			}
			
			//执行SQL语句.
			$result = self::query($sql,$master);
					
			if (!$result) 
			{			
				return false;
			}
										
			$myrow = $result->fetchAll(PDO::FETCH_ASSOC);		
			$result = null;
			return $myrow;
		}
		
		
		/**
		 * 数据表更改操作
		 * 
		 * 更改当前model所对应的数据表的数据内容
		 * @access public
		 * @param array 	$data 所要更改的数据内容
		 * @param mixed		$where 更改数据所要满足的条件
		 * @return boolean
		 */
		public static function update($table_name,$data, $where) 
		{
			
			//参数分析
			if (!is_array($data) || !$data || !$where) 
			{
				return false;
			}
					
			$content_array = array();
			
			foreach ($data as $key=>$value) 
			{
					$content_array[] = '`' . $key . '` = \'' . mysql_real_escape_string(trim($value)) . '\'';
			}
			$content_str = implode(',', $content_array);
			unset($content_array);
			
			//组装SQL语句
			$sql_str = 'UPDATE `'.$table_name.'` SET '.$content_str  .$where;
			
			return self::query($sql_str);
		}
		
	/**
		 * 数据表写入操作
		 * 
		 * 向当前model对应的数据表插入数据
		 * @access public
		 * @param array $data 所要写入的数据内容。注：数据必须为数组
		 * @return boolean
		 * 
		 * @example
		 * 
		 * $data = array('name'=>'tommy', 'age'=>23, 'addr'=>'山东'); //注：数组的键值是数据表的字段名
		 * 
		 * $model->insert($data);
		 */
		public static function insert($table_name,$data) 
		{
			
			//参数分析
			if (!is_array($data) || !$data) 
			{
				return false;
			}
			
	
			//处理数据表字段与数据的对应关系
			$field_array 	= array();
			$content_array 	= array();
			
			foreach ($data as $key=>$value) 
			{
				
					$field_array[] 	= '`' . trim($key) . '`';
					$content_array[]= '\'' . mysql_real_escape_string(trim($value)) . '\'';
			}
			
			$field_str 		= implode(',', $field_array);
			$content_str	= implode(',', $content_array);
					
			$sql_str = 'INSERT INTO `' .  $table_name . '` (' . $field_str . ' ) VALUES (' . $content_str . ')';
			return self::query($sql_str);
		}
		
		/**
		 * 通过一个SQL语句获取一行信息
		 * 
		 * @access public
		 * @param string $sql SQL语句
		 * @return array
		 */
		public static function getRow($sql,$master=false) 
		{
			
			//参数分析
			if (!$sql) 
			{
				return false;
			}
			
			//执行SQL语句.
			$result = self::query($sql,$master);
					
			if (!$result) 
			{			
				return false;
			}
			
			$row = $result->fetch(PDO::FETCH_ASSOC);
				
			$result = null;
			
			return $row;
		}
		/**
		 * 获取insert_id
		 * 
		 * @access public
		 * @return int
		 */
		public static function getInsertId()
		{		
			return self::get_master_link()->lastInsertId();
		}
		
		/**
	     * 开启事务处理
	     *
	     * @access public
	     * @return boolean
	     */
		public static function startTrans() 
		{
					
			if (self::$Transactions == false) 
			{	
				self::get_mster_link()->beginTransaction(false);
				self::$Transactions = true;
			}
										
			return true;
		}
		
		/**
		 * 提交事务处理
		 * 
		 * @access public
		 * @return boolean
		 */
		public static function commit() 
		{
					
			if(self::$Transactions == true)
			{			
				$result = self::get_mster_link()->commit();
				
				if ($result) 
				{								
					self::$Transactions = false;				
				} 
				else 
				{
					//获取当前运行的错误信息
					$errorInfo =$db->errorInfo();
					$message = 'Database SQL execute failed!<br/>Error Code:'.$db->errorCode().'<br/>Error Message:'.$errorInfo[2];
					core\Exception::ErrorPage($message,500);
				}			
			}
					
			return true;
		}
		
		/**
		 * 事务回滚
		 * 
		 * @access public
		 * @return boolean
		 */
		public static function rollback() 
		{
					
			if (self::$Transactions == true) 
			{			
				$result = self::$db_link->rollback();
							
				if ($result) 
				{				
					self::$Transactions = false;
				} 
				else 
				{	
					//获取当前运行的错误信息
					$errorInfo =$db->errorInfo();
					$message = 'Database SQL execute failed!<br/>Error Code:'.$db->errorCode().'<br/>Error Message:'.$errorInfo[2];
					core\Exception::ErrorPage($message,500);
				}
			}	
				
			return true;
		}
			
	}
}