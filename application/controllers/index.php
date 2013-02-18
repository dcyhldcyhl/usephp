<?php
namespace application\controllers{

	use system\core as core;
	use application\models as models;

	class index extends core\Controller 
	{
		public function index() 
		{
			$value = models\user::test();
			$content='aaa';
			$this->view->assign('content',$content);	
			$this->view->display();	
			
		}
		
		public function view()
		{	
			//$this->view = core\View::loadView();
			$value = models\user::test();			
			$content='bbbb';	
			$this->view->assign('content',$content);
			$this->view->display();					
			
		}
		
	}
}