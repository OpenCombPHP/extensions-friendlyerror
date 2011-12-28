<?php
namespace org\opencomb\friendlyerror\exception ;

use org\jecat\framework\mvc\controller\Controller;

class UncatchExceptionReporter extends Controller
{
	public function createBeanConfig()
	{
		return array(
			'view:view' => array(
				'template'=>'friendlyerror:exception/UncatchExceptionReporter.html'
			) ,				
		) ;
	}
	
	public function process()
	{
		$this->view->variables()->set('aException',$this->params['exception']) ;
	}
}

?>