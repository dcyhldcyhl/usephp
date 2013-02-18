<?php
namespace application\models{
	
	use system\lib\db\dbMysqli;
	use system\lib\db\dbPdo;
	use system\lib\db\dbMysql;
	
	class user
	{
		
		public static function test()
		{
			//$test = dbMysqli::getAll("select * from user where user_id=1",true);
			//$test = dbPdo::getAll("select * from user where user_id=1",true);
			//$test = dbmysql::getAll("select * from user where user_id=1",true);
			//print_r($test);
			//$test = $this->insert(array('user_name'=>$aa));
			
			$aa="hello";	
			return array('id'=>200098670,'name'=>'mc');
		}
		
	}
}