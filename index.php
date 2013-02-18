<?php

error_reporting(E_ALL^E_NOTICE);

/**
 * 定义项目所在路径即:APP_ROOT
 */
define('APP_ROOT',  __DIR__. DIRECTORY_SEPARATOR);


//设置程序开始执行时间.根据实际需要,自行开启,如开启去掉下面的

//define('WEB_START_TIME',microtime(true));

//项目controller目录的路径
define('CONTROLLER_DIR', APP_ROOT . 'application/controllers' . DIRECTORY_SEPARATOR);

//项目model目录的路径
define('MODEL_DIR', APP_ROOT . 'application/models' . DIRECTORY_SEPARATOR);	

//项目view目录的路径
define('VIEW_DIR', APP_ROOT . 'application/views' . DIRECTORY_SEPARATOR);

//项目config目录的路径s
define('CONFIG_DIR', APP_ROOT . 'application/config' . DIRECTORY_SEPARATOR);


//项目widget目录的路径
define('WIDGET_DIR', APP_ROOT . 'application/widgets' . DIRECTORY_SEPARATOR);


//项目hooks目录的路径
define('HOOK_DIR', APP_ROOT . 'application/hooks' . DIRECTORY_SEPARATOR);


//项目libs目录的路径
define('LIBS_DIR', APP_ROOT . 'application/libs' . DIRECTORY_SEPARATOR);

//设置是否开启调试模式.开启后,程序运行出现错误时,显示错误信息,便于程序调试.
define('WEB_DEBUG', false);	

//设置URL的Rewrite功能是否开启,如开启后,需WEB服务器软件如:apache或nginx等,要开启Rewrite功能.
define('WEB_REWRITE', true);

//设置系统默认的controller名称,默认为:index
define('DEFAULT_CONTROLLER', 'index');

//设置 系统默认的action名称,默认为index
define('DEFAULT_ACTION', 'index');

//默认程序资源后缀名
define('EXT', '.php');

//注册异常处理函数
function exception_handler($severity, $message, $filepath, $line)
{
	system\core\Exception::showError($severity, $message, $filepath, $line);
}
set_error_handler('exception_handler');

//设定时区
date_default_timezone_set('Asia/ShangHai');

//加载框架主文件
require_once APP_ROOT . 'system/core/main'.EXT;

system\core\main::run();
