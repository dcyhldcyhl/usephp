<?php
namespace system\core{
	use system\core as core;	
	class Controller extends base {		
		//页面视图
		protected $view;			
		
		/**
		 * 初始化视图类
		 */
		function __construct()
		{
			$this->view = core\View::loadView();	
		}
	
		/**
		 * 获取并分析$_GET数组某参数值
		 * 
		 * 获取$_GET的全局超级变量数组的某参数值,并进行转义化处理，提升代码安全.注:参数支持数组
		 * @access public
		 * @param string $string 所要获取$_GET的参数
		 * @return string	$_GET数组某参数值
		 */
		public function get($string) 
		{
					
			if (!is_array($string)) 
			{
				return isset($_GET[$string]) ? htmlspecialchars(trim($_GET[$string])) : '';			
			}
			
			foreach ($string as $key=>$value) 
			{					
				$string[$key] = $this->get($value);
			}						
			return $string;
		}
		
		/**
		 * 获取并分析$_POST数组某参数值
		 * 
		 * 获取$_POST全局变量数组的某参数值,并进行转义等处理，提升代码安全.注:参数支持数组
		 * @access public
		 * @param string $string	所要获取$_POST的参数
		 * @return string	$_POST数组某参数值
		 */
		public function post($string) {
					
			if (!is_array($string)) 
			{
				return isset($_POST[$string]) ? htmlspecialchars(trim($_POST[$string])) : '';				
				
			}
			
			foreach ($string as $key=>$value) 
			{				
				$string[$key] = $this->post($value);
			}
						
			return $string;
		}
		
		/**
		 * 获取并分析 $_GET或$_POST全局超级变量数组某参数的值
		 * 
		 * 获取并分析$_POST['参数']的值 ，当$_POST['参数']不存在或为空时，再获取$_GET['参数']的值。
		 * @access public
		 * @param string $string 所要获取的参数名称
		 * @return string	$_GET或$_POST数组某参数值
		 */
		public function getParams($string) 
		{
			
			$param_value = $this->post($string);			
			//当$_POST[$string]值没空时
			return empty($param_value) ? $this->get($string) : $param_value;
		}
		
		
		/**
		 * 第二部分：程序调试操作，用于程序调试或运行时出现错误时的信息提示
		 * @author tommy
		 * @version 1.0 2010-10-19
		 */
	
		/**
		 * trigger_error()的简化函数
		 * 
		 * 用于显示错误信息. 若调试模式关闭时(即:WEB_DEBUG为false时)，则将错误信息并写入日志
		 * @access public
		 * @param string $message 所要显示的错误信息
		 * @param string $level     日志类型. 默认为Error. 参数：Warning, Error, Notice
		 * @return void
		 */
		public function halt($message, $level = 'Error') 
		{
			
			//参数分析
			if (empty($message)) {
				return false;
			}								
			
			//调试模式下优雅输出错误信息
			$trace 			= debug_backtrace();
			$source_file 	= $trace[0]['file'] . '(' . $trace[0]['line'] . ')';
				
			$trace_string 	= '';
			foreach ($trace as $key=>$t) {
				$trace_string .= '#'. $key . ' ' . $t['file'] . '('. $t['line'] . ')' . $t['class'] . $t['type'] . $t['function'] . '(' . implode('.',  $t['args']) . ')<br/>';			
			}
				
	
			echo $message;
			
			core\Exception::writeLog('error', $message);
			
			//终止程序
			exit();
		}
		
		/**
		 * 显示提示信息操作
		 * 
		 * 所显示的提示信息并非完全是错误信息。如：用户登陆时用户名或密码错误，可用本方法输出提示信息
		 * 
		 * 注：显示提示信息的页面模板内容可以自定义. 方法：在项目视图目录中的error子目录中新建message.html文件,自定义该文件内容
		 * 显示错误信息处模板标签为<!--{$message}-->
		 * 
		 * 本方法支持URL的自动跳转，当显示时间有效期失效时则跳转到自定义网址，若跳转网址为空则函数不执行跳转功能，当自定义网址参数为-1时默认为:返回上一页。
		 * @access public
		 * @param string $message 		所要显示的提示信息
		 * @param string $goto_url 		所要跳转的自定义网址
		 * @param int    $limit_time 	显示信息的有效期,注:(单位:秒) 默认为5秒
		 * @return void
		 */
		public function showMessage($message, $goto_url = null, $limit_time = 5) 
		{		
			//参数分析
			if (!$message) 
			{
				return false;
			}
			
			//当自定义跳转网址存在时
			if (!is_null($goto_url)) 
			{									
				$limit_time = 1000 * $limit_time;			
				//分析自定义网址是否为返回页
				if ($goto_url == -1) 
				{				
					$goto_url = 'javascript:history.go(-1);';
					$message .= '<br/><a href="javascript:history.go(-1);" target="_self">如果你的浏览器没反应,请点击这里...</a>';
				} 
				else
				{
					//防止网址过长，有换行引起跳转变不正确
					$goto_url = str_replace(array("\n","\r"), '', $goto_url);
					$message .= '<br/><a href="' . $goto_url . '" target="_self">如果你的浏览器没反应,请点击这里...</a>';
				}			
				$message .= '<script type="text/javascript">function WEB_redirect_url(url){location.href=url;}setTimeout("WEB_redirect_url(\'' . $goto_url . '\')", ' . $limit_time . ');</script>';
			}
			
			$message_template_file = VIEW_DIR . 'error/message.php';
			
			echo $message;
			exit();
		}
		
		/**
		 * 优雅输出print_r()函数所要输出的内容
		 * 
		 * 用于程序调试时,完美输出调试数据,功能相当于print_r().当第二参数为true时(默认为:false),功能相当于var_dump()。
		 * 注:本方法一般用于程序调试
		 * @access public
		 * @param array $data 		所要输出的数据
		 * @param boolean $option 	选项:true或 false
		 * @return array			所要输出的数组内容
		 */
		public function dump($data, $option = false) 
		{			
			//当输出print_r()内容时
			if(!$option)
			{
				echo '<pre>';
				print_r($data);
				echo '</pre>';
			} 
			else 
			{
				ob_start();
				var_dump($data);
				$output = ob_get_clean();
				
				$output = str_replace('"', '', $output);
				$output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
				
				echo '<pre>', $output, '</pre>';
			}
	
		}
		
			
		/**
		 * 第三部分：获取当前程序运行的环境信息.如:获取域名，当前网页的根网址，当前网页的网址等信息
		 * @author tommy
		 * @version 1.0 2010-10-21
		 */
		
		/**
		 * 获取当前运行程序的网址域名
		 * 
		 * 如：http://www.webphp.com
		 * @access public
		 * @return string	网址(域名)
		 */
		public function getServerName() 
		{
			
			//获取网址域名部分.
			$server_name = !empty($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : $_SERVER['SERVER_NAME'];
			$server_port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':' . (int)$_SERVER['SERVER_PORT'];
			
			//获取网络协议.
			$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;		
			
			return ($secure ? 'https://' : 'http://') . $server_name . $server_port;
		}
		
		/**
		 * 获取当前项目的根目录的URL
		 * 
		 * 用于网页的CSS, JavaScript，图片等文件的调用.
		 * @access public
		 * @return string 	根目录的URL. 注:URL以反斜杠("/")结尾
		 */
		public function getBaseUrl() 
		{
			
			//处理URL中的//或\\情况,即:出现/或\重复的现象
			$url = str_replace(array('\\', '//'), '/', dirname($_SERVER['SCRIPT_NAME']));
			
			return (substr($url, -1) == '/') ? $url : $url . '/';
		}
		
		/**
		 * 获取当前运行的Action的URL
		 * 
		 * 获取当前Action的URL. 注:该网址由当前的控制器(Controller)及动作(Action)组成,不含有其它参数信息
		 * 如:/index.php/index/list，而非/index.php/index/list/page/5 或 /index.php/index/list/?page=5
		 * @access public
		 * @return string	URL
		 */
		public function getSelfUrl() 
		{			
			return $this->create_url(core\main::getController() . '/' . core\main::getAction());
		}
		
		/**
		 * 获取当前Controller内的某Action的URL
		 * 
		 * 获取当前控制器(Controller)内的动作(Action)的URL. 注:该网址仅由项目入口文件和控制器(Controller)组成。
		 * @access public
		 * @param string $action_name 所要获取URL的action的名称
		 * @return string	URL
		 */
		public function getActionUrl($action_name) 
		{			
			//参数判断
			if (empty($action_name)) 
			{
				return false;
			}
			
			return $this->create_url(core\main::getController() . '/' . $action_name);
		}
		
		/**
		 * 获取当前项目asset目录的URL
		 * 
		 * @access public
		 * @param string $dir_name 子目录名
		 * @return string	URL
		 */
		public function getAssetUrl($dir_name = null) 
		{			
			//获取assets根目录的url
			$asset_url = $this->get_base_url() . 'assets/';
			
			//分析assets目录下的子目录
			if (!is_null($dir_name)) 
			{
				$asset_url .= $dir_name . '/';
			}
			
			return $asset_url;
		}
		
		/**
		 * 获取当前项目themes目录的URL
		 * 
		 * @access public
		 * @param string $theme_name 所要获取URL的主题名称
		 * @return string	URL
		 */
		public function getThemeUrl($theme_name = null)
		{			
			//分析主题名
			$theme_dir_name = is_null($theme_name) ? 'default' : $theme_name;
			
			return $this->getBaseUrl() . 'themes/' . $theme_dir_name . '/';
		}
		
		
		/**
		 * 第四部分：URL处理操作. 如:URL跳转，URL组装等
		 * @author tommy
		 * @version 1.0 2010-10-21
		 */
		
		/**
		 * 网址(URL)跳转操作
		 * 
		 * 页面跳转方法，例:运行页面跳转到自定义的网址(即:URL重定向)
		 * @access public
		 * @param string $url 所要跳转的URL
		 * @return void
		 */
		public function redirect($url)
		{			
			//参数分析.
			if (!$url) 
			{
				return false;
			}
					
			if (!headers_sent()) 
			{
				header("Location:" . $url);			
			}
			else 
			{
				echo '<script type="text/javascript">location.href="' . $url . '";</script>';
			}
			
			exit();
		}
		
		/**
		 * 网址(URL)组装操作
		 * 
		 * 组装绝对路径的URL
		 * @access public
		 * @param string 	$route 			controller与action
		 * @param array 	$params 		URL路由其它字段
		 * @param boolean 	$routing_mode	网址是否启用路由模式
		 * @return string	URL
		 */
		public function createUrl($route, $params = null, $routing_mode = true) 
		{
		    
			//参数分析.
			if (!$route) 
			{
				return false;
			}
			
			$url  = $this->getBaseUrl() . ((WEB_REWRITE === false) ? 'index.php/' : '') . $route;
			
			//参数$params变量的键(key),值(value)的URL组装
			if (!is_null($params) && is_array($params)) 
			{							
				$params_url = array();			
				if ($routing_mode == true) 
				{				
					foreach ($params as $key=>$value) 
					{
						$params_url[] = trim($key) . '/' . trim($value);
					}
					$url .= '/'.implode('/', $params_url);					
				} 
				else 
				{				
					foreach ($params as $key=>$value) 
					{
						$params_url[] = trim($key) . '=' . trim($value);
					}
					$url .= '/?'.implode('&', $params_url);
				}
			}
					
			return str_replace('//', '/', $url);
		}
		
		
		/**
		 * 第五部分：类的实例化(单例模式)操作, 用于扩展类,model,扩展模块类的实例化
		 * @author tommy
		 * @version 1.0 2010-10-21
		 */
		
		/**
		 * 类的单例实例化操作
		 * 
		 * 用于类的单例模式的实例化,当某类已经实例化，第二次实例化时则直接反回初次实例化的object,避免再次实例化造成的系统资源浪费。
		 * @access public
		 * @param string $class_name 所要实例化的类名
		 * @return object	实例化后的对象
		 */
		public function instance($class_name) 
		{
			
			//参数判断
			if (!$class_name) 
			{
				return false;
			}
			
			return core\main::singleton($class_name);
		}
		
						
		
		/**
		 * 静态加载文件 
		 * 
		 * 相当于inclue_once()
		 * @access public
		 * @params string $file_name 所要加载的文件
		 * @return void
		 */
		public function import($file_name) 
		{
			
			//参数判断
			if (!$file_name) 
			{
				return false;
			}
			
			//判断文件是不是项目扩展目录里的
			$file_url = (strpos($file_name, '/') !== false) ? realpath($file_name) : realpath(EXTENSION_DIR . $file_name . '.php');
			
			return core\main::loadFile($file_url);
		}	
		
	
		/**
		 * stripslashes()的同功能操作
		 * 
		 * @access protected
		 * @param string $string 	所要处理的变量
		 * @return mixed			变量
		 */
		protected function strips($string) 
		{
			
			//参数分析.
			if (!$string) {
				return false;
			}
					
			if (!is_array($string)) 
			{
				return stripslashes($string);			
			}
			
			foreach ($string as $key=>$value) 
			{					
				$string[$key] = $this->strips($value);
			}
						
			return $string;
		}
		
		/**
		 * addslashes()的同功能操作
		 * 
		 * @access protected
		 * @params string $string 	所要处理的变量
		 * @return mixed			变量
		 */
		protected function adds($string) 
		{
	
			//参数分析.
			if (!$string) {
				return false;
			}
			
			if (!is_array($string)) 
			{
				return addslashes($string);			
			}
			
			foreach ($string as $key=>$value) 
			{				
				$string[$key] = $this->adds($value);
			}
						
			return $string;
		}		

	}
}