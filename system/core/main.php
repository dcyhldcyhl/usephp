<?php
namespace system\core{
	
	use system\core as core;
	use application\controllers as controllers;
	/**
	 * 框架核心全局控制类
	 * 
	 * 用于初始化程序运行及完成基本设置
	 * @version 1.0
	 */
	abstract class Main {
	
		/**
		 * 控制器(controller)
		 * 
		 * @var string
		 */
		public static $controller;
		
		/**
		 * 动作(action)
		 * 
		 * @var string
		 */
		public static $action;
		
		/**
		 * 扩展名
		 * 
		 * @var array
		 */
		
		public static $format;
		
		/**
		 * 对象注册表
		 * 
		 * @var array
		 */
		
		public static $_objects = array();
		
		/**
		 * 载入的文件名(用于PHP函数include所加载过的)
		 * 
		 * @var array
		 */
		public static $_inc_files = array();
		
		/**
		 * 分析URL信息 unend
		 * 
		 * 通过对URL(网址)的分析,获取当前运行的controller和action,赋值给变量self::controller, 和self::action,
		 * 方便程序调用,同时将URL中的所含有的变量信息提取出来 ,写入$_GET全局超级变量数组中.
		 * 
		 * 注:这里的URL的有效部分是网址'?'之前的部分.'?'之后的部分不再被分析,因为'?'之后的URL部分完全属于$_GET正常调用的范畴.
		 * 这里的网址分析不支持功能强大的路由功能,只是将网址中的'/'分隔开,经过简单地程序处理提取有用数据.
		 * @access private
		 * @return boolean
		 */
		private static function parseRequest() 
		{
			
			//当项目开启Rewrite设置时
			if (WEB_REWRITE === false) 
			{
				$path_url_string = strlen($_SERVER['SCRIPT_NAME']) > strlen($_SERVER['REQUEST_URI']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['REQUEST_URI'];
				$path_url_string = str_replace($_SERVER['SCRIPT_NAME'], '', $path_url_string);				
			} 
			else
			{			
				$path_url_string = str_replace('/index.php',  '', $_SERVER['REQUEST_URI']);
			}
			
			//如网址(URL)含有'?'(问号),则过滤掉问号(?)及其后面的所有字符串
			$pos = strpos($path_url_string, '?');
			if ($pos !== false) 
			{			
				$path_url_string = substr($path_url_string, 0, $pos);
			}
			//将处理过后的有效URL进行分析,提取有用数据.
			$url_info_array = explode('/', $path_url_string);
			
			//获取 controller名称
			$controller_name  = ($url_info_array[1] == true) ? $url_info_array[1] : DEFAULT_CONTROLLER;
			self::$controller =  strtolower($controller_name);
					
			//获取 action名称
			$action_name  = ($url_info_array[2] == true) ? $url_info_array[2] : DEFAULT_ACTION;
			if(strpbrk($action_name, '.') === false)
			{
				self::$action =  strtolower($action_name);
			}
			else
			{
				$action_arr = explode('.', $action_name);
				self::$action =  strtolower($action_arr[0]);
				self::$format =  strtolower($action_arr[1]);
			}
			//变量重组,将网址(URL)中的参数变量及其值赋值到$_GET全局超级变量数组中
			$total_num = sizeof($url_info_array);
			if ($total_num > 4) 
			{			
				for ($i = 3; $i < $total_num; $i +=2) 
				{					
					if (!$url_info_array[$i]) 
					{
						continue;
					}				
					$_GET[$url_info_array[$i]] = $url_info_array[$i + 1];
				}
			}
					
			return true;
		}
		
		/**
		 * 项目运行函数
		 * 
		 * 供项目入口文件(index.php)所调用,用于启动框架程序运行
		 * @access public
		 * @return object
		 */
		public static function run() 
		{
			ob_start();	
			//定义变量_app
			static $_app = array();
			
			//分析URL
			self::parseRequest();
			
			$app_id = self::$controller . '_' . self::$action;
			
			if (!isset($_app[$app_id]))
			{
							
				//通过实例化及调用所实例化对象的方法,来完成controller中action页面的加载
				$controller = self::$controller;
				$action     = self::$action;
				//加载基本文件:Base,Controller基类
				if (is_file(CONTROLLER_DIR . $controller . EXT))
				{											
					self::loadFile(CONTROLLER_DIR . $controller . EXT);				
				} 
				else
				{							
						Exception::show404('class not found');
				}
				
				//在controller执行前执行钩子文件			
				$hooks = self::getConfigKey('hooks');
				if($hooks != null)
				{
					foreach ($hooks as $obj)
					{
						$hook_name = 'application\\hooks\\' . $obj;
						self::singleton($hook_name)->load();
					}
				}
				
				//创建一个页面控制对象
				$controller = 'application\\controllers\\'.$controller;
				$app_object = new $controller();
				
				if (method_exists($controller, $action))
				{				
					$_app[$app_id] = $app_object->$action();				
				} 
				else 
				{				
					//所调用方法在所实例化的对象中不存在时.
					Exception::show404('action not found');
					//core\controller::halt($controller.' File '.$action.' not Found');
					
				}			
			}
					
			return $_app[$app_id];
		}
		
		/**
		 * 项目文件的自动加载
		 * 
		 * @access public
		 * @param string $class_name 所需要加载的类的名称,注:不含后缀名
		 * @return void
		 */
		public static function autoLoad($class_name) 
		{
			$class_name = strtolower($class_name);
			
			$filepath = APP_ROOT.$class_name.EXT;
			
			require($filepath);
		
			return;		
		}
		
		/**
		 * 获取当前运行的controller名称
		 * 
		 * @example $controller_name = main::getController();
		 * @access public
		 * @return string controller名称(字母全部小写)
		 */
		public static function getController() 
		{		
			return strtolower(self::$controller);
		}
		
		/**
		 * 获取当前运行的action名称
		 * 
		 * @example $action_name = main::getAction();
		 * @access public
		 * @return string action名称(字母全部小写)
		 */
		public static function getAction() 
		{
			
			return self::$action;
		}
		
		/**
		 * 返回唯一的实例(单例模式)
		 * 
		 * @access public
		 * @param string $class_name  要获取的对象的类名字
		 * @return object 返回对象实例
		 */
		public static function singleton($class_name) 
		{
			
			//参数分析
			if (!$class_name) 
			{
				return false;
			}
			
			$key = strtolower($class_name);
			
			if (isset(self::$_objects[$key])) 
			{				
				return self::$_objects[$key];
			}
			
			return self::$_objects[$key] = new $class_name();
		}
		
		/**
		 * 静态加载文件(相当于PHP函数require_once)
		 * 
		 * include 以$file_name为名的php文件,如果加载了,这里将不再加载.
		 * @param string $file_name 文件路径,注:含后缀名	
		 * @return boolean 
		 */
		public static function loadFile($file_name) 
		{
	
			//参数分析
			if (!$file_name) 
			{
				return false;
			}
			 
			//判断文件有没有加载过,加载过的直接返回true
			if (!isset(self::$_inc_files[$file_name]) || self::$_inc_files[$file_name] == false) 
			{
				
				if (!is_file($file_name)) 
				{
					core\controller::halt('The file:' . $file_name . ' not found!');
				}
				
				require  $file_name;
				self::$_inc_files[$file_name] = true;
			}
			
			return self::$_inc_files[$file_name];
		}
		/**
		 * 获取config文件中的键值
		 * @param string $key 配置的键
		 * @return multitype:
		 */
		public static function getConfigKey($key,$filename="config")
		{
			static $sConfig;
	
			//判定是否已经加载过配置文件
			if (!isset($sConfig))
			{
				$filename = CONFIG_DIR.$filename.EXT;
				if (!is_file($filename)) 
				{
					core\controller::halt('The file: '.$filename.' not found!');
				}
				require($filename);
				$sConfig = $config;
			}
				
			//判定配置文件内是否存在该键值
			if(isset($sConfig)&&!empty($sConfig))
			{
				if (array_key_exists($key, $sConfig))
				{
					//返回该键上的配置值
					return $sConfig[$key];
				}
			}
	
			return null;
	
		}
	}
	/**
	 * 调用SPL扩展,注册__autoload()函数.
	 */
	spl_autoload_register(array('system\core\main', 'autoLoad'));
}

