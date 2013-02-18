<?php
namespace system\core{

	use system\core as core;

	/**
	 * 错误页面
	 * @package system\core
	 * @category core
	 * @author huanglin
	 * @see
	 */
	class Exception
	{
		var $ob_level;

		function __construct()
		{
			$this->ob_level = ob_get_level();
		}

		/**
		 * 404处理页面
		 * @return void
		 */
		static function show404($message = 'Not Found')
		{
			self::ErrorPage($message,404);
			die();
		}

		/**
		 * 500处理页面
		 * @param string $severity
		 * @param string $message
		 * @param string $filepath
		 * @param string $line
		 */
		static function showError($severity, $message, $filepath, $line)
		{
			$data['severity'] = $severity;
			$data['message'] = $message;
			$data['filepath'] = $filepath;
			$data['line'] = $line;
			
			$errorStr = $message." ".$filepath." ".$line;
			switch ($severity)
			{
				case E_USER_ERROR:
					self::writeLog('error', $errorStr);
					break;
				case E_WARNING:
					self::writeLog('warning', $errorStr);
					break;
				case E_NOTICE:
					self::writeLog('notice', $errorStr);
					break;
				default:
					self::writeLog('notice', $errorStr);
					break;	
			}
			//die();
		}
		
		static function writeLog($level,$message)
		{

			$logPath=core\main::getConfigKey('logs');
            
			if($logPath != null)
			{
	            //确保日志的根目录存在并且可写
	            if ( is_dir($logPath) && is_writable($logPath) )
	            {           
		            $logPath.="/".date("Ymd",time());
		            
					if( !is_dir($logPath))
		            {
		                mkdir($logPath);
		            }
		            elseif( !is_writable($logPath))
		            {
		                return false;
		            }
		            
		            $level = strtolower($level);
		            
		            $filePath = $logPath."/".$level.".txt"; //得到日志文件的完整路径
		            $message = "[" . date("Y-m-d H:i:s",time()) . "] - [" . $_SERVER['REQUEST_URI'] . "] : " . $message . "\r\n"; //得到日志内容
		            return error_log( $message, 3, $filePath );
	            }
			}
		}
		
		static function ErrorPage($message, $statusCode = 404)
		{
			ob_end_clean();
			switch ($statusCode)
			{
				case 403:
					$statusMsg = 'Forbidden';
					break;
				case 404:
					$statusMsg = 'Not Found';
					break;
				case 500:
					$statusMsg = 'Internal Server Error';
			}

			header('HTTP/1.1 '.$statusCode.' '.$statusMsg);
			$error_page = core\Main::getConfigKey('error_page');

			if ($error_page != NULL)
			{
				if (isset($error_page[$statusCode.'_page']))
				{
					
					$response = new core\View();
					$response->display(array('message'=>$message),$error_page[$statusCode.'_page']);
					die();
				}
			}
			self::writeLog('error', $message);
			echo $message;
			die();
		}
	}
}
/* End of file exception.php */