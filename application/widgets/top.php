<?php
namespace application\widgets {
	use system\core as core;
	class top extends core\Controller 
	{
	
		public  function index($argv) 
		{
			$this->dump($argv);
			$data['widget_content']='I am a Widget';			
			$this->view->display($data,'widgets/top');
			
		}		
		
	}
}