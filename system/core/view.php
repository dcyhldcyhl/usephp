<?php
namespace system\core{
	use system\core as core;
	
	class View extends base {
		
		
		/**
		 * 视图主题风格名称(theme)
		 * 
		 * @var string
		 */
		protected $theme;
		
		/**
		 * 视图变量数组
		 * 
		 * @var array
		 */
		protected $_options = array();
		
		
		/**
		 * 实例化视图对象
		 * @return Object View
		 */
		static function loadView()
		{
			static $sView;

			if (!isset($sView))
			{
				$sView = new core\View();
			}
			
			return $sView;
		}
		
		/**
		 * 设置视图的主题
		 * 
		 * 用于变化网页风格的视图选择. 注:该主题的视图文件存放于项目themes目录内,而非项目controller目录内
		 * @access public
		 * @param string $theme_name 所要设置的网页模板主题名称 
		 * @return string	视图的主题名
		 */
		public function setTheme($theme_name = 'default') 
		{			
			return $this->theme = $theme_name;
		}
		
		/**
		 * 分析视图文件
		 * 
		 * 获取视图的路径,便于程序进行require操作. 注:本方法不支持视图布局结构(layout)
		 * @access publice
		 * @param string $file_name 视图文件名，注:名称中不带.php后缀。
		 * @return void
		 * 
		 * @example
		 * 法一：
		 * include $this->template('list'); 
		 * 
		 * 法二：
		 * include $this->template();	//即:当前controller视图目录下,当前action所对应的视图名称
		 * 如当前action为demo，则相当于$this->template('demo');
		 * 
		 * 法三：
		 * include $this->template('xx_controller/xx_action');
		 */
		public function viewFile($file_name = null) 
		{
			//参数分析
			if (is_null($file_name)) 
			{						
				$file_name = core\main::getController() . '/' . core\main::getAction();			
			} 
			else 
			{			
				$file_name = (strpos($file_name, '/') !== false) ? $file_name : core\main::getController() . '/' . $file_name;
			}
			$file_name .= EXT;
			//分析视图文件所在的目录,是否视图使用了主题 也可以理解为语言包
			$view_file  = (!empty($this->theme)) ? APP_ROOT . 'themes/' . $this->theme . '/' . $file_name : VIEW_DIR . $file_name;
			//分析视图文件是否存在			
			if (!is_file($view_file)) 
			{			
				core\Exception::ErrorPage('The view file:' . $view_file . ' is not exists!',404);
			}			
			return $view_file;
		}
		
		/**
		 * 视图变量赋值操作
		 * 
		 * @access public
		 * @param mixted $keys 视图变量名
		 * @param string $value 视图变量值
		 * @return mixted
		 */
		public function assign($keys, $value = null) 
		{
			
			//参数分析
			if (!$keys) 
			{
				return false;
			}
			
			//当$keys为数组时
			if (!is_array($keys)) 
			{
				$this->_options[$keys] = $value;			
			}
			else 
			{
				foreach ($keys as $handle=>$lines) 
				{
					$this->_options[$handle] = $lines;
				}
			}
			
			return true;
		}
		
		/**
		 * 加载当前页面的视图内容
		 * 
		 * 包括视图页面中所含有的挂件(widget), 视图布局结构(layout), 及render()所加载的视图片段等
		 * @access public
		 * @param string $file_name 视图名称
		 * @param array  $_data     视图的变量
		 * @return void
		 */
		public function display($_data = array(),$file_name = null) 
		{
			//分析视图文件路径
			$view_file = $this->viewFile($file_name);
			
			//模板变量赋值
			if (!empty($this->_options)) 
			{
				extract($this->_options, EXTR_PREFIX_SAME, 'data');
				//清空不必要的内存占用
				$this->_options = array();
			}
			
			if (!empty($_data) && is_array($_data)) 
			{			
				extract($_data, EXTR_PREFIX_SAME, 'data');
				//清空不必要的内存占用
				unset($_data);
			}
			
			//获取当前视图($file_name)的内容
			require $view_file;		
		}
		
		/**
		 * Enter description here ...
		 * @param unknown_type $file_name
		 */
		public function template($file_name)
		{
			//分析视图文件路径
			$view_file = $this->viewFile('templates/'.$file_name);		
			
			require $view_file;	
		}
			
		/**
		 * 显示视图内容
		 */
		public function render() 
		{
			
			$buffer = ob_get_contents();
			ob_clean();
			echo($buffer);
			ob_flush();
			flush();
			
		}	
		/**
		 * 加载视图文件的挂件(widget)
		 * 
		 * 加载挂件内容，一般用在视图内容中(view)
		 * @access public
		 * @param string  $widget_name 所要加载的widget名称,注没有后缀名	
		 * @return boolean
		 */
		public function widget($widget_name,$argv=array()) 
		{
			
			//参数判断
			if (!$widget_name) 
			{
				return false;
			}
			
			//分析widget名称
			$widget_name = 'application\\widgets\\'.ucfirst(strtolower($widget_name));							
		    core\main::singleton($widget_name)->index($argv);
		    return true;
		}	
	}
}