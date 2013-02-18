<?php

$config['debug'] = true;//调试开启


//$config['hooks'] = array('myhook');//钩子文件的类名

$config['logs'] = 'E:\logs\usephp';//日志文件路径

$config['error_page'] = array(

		'403_page'=>'error_page/403_page',
		'404_page'=>'error_page/404_page',
		'500_page'=>'error_page/500_page',
);

//pdo主库
$config['pdodb']['master'] = array(
			'dsn' 	=>'mysql:host=localhost;dbname=wikidb',
			'username' 	=>'root',
			'password' 	=>'123456'
);

//主库
$config['db']['master'] = array(
			'host' 	=>'localhost',
			'username' 	=>'root',
			'password' 	=>'123456',
			'dbname' 	=>'wikidb',
			'charset' 	=>'utf-8',
			'prefix' 	=>'',
			'driver' 	=>'mysqli',
);
//从库
$config['db']['slave'] = array(
	array(
			'host' 	=>'localhost',
			'username' 	=>'root',
			'password' 	=>'123456',
			'dbname' 	=>'wikidb',
			'charset' 	=>'utf-8',
			'prefix' 	=>'',
			'driver' 	=>'mysqli'),
	array(
			'host' 	=>'localhost',
			'username' 	=>'root',
			'password' 	=>'123456',
			'dbname' 	=>'wikidb',
			'charset' 	=>'utf-8',
			'prefix' 	=>'',
			'driver' 	=>'mysqli')
	
);