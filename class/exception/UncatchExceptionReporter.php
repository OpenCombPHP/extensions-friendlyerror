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
	
	public function readSourceSegment($sFile,$nLine,$nRange=5)
	{
		$arrLines = (array)@file($sFile) ;
		
		$nOffsetLine = $nLine-$nRange ;
		if($nOffsetLine<0)
		{
			$nOffsetLine = 0 ;
		}
		$nLines = $nRange*2+1 ;
		if( count($arrLines)<$nOffsetLine+$nLines )
		{
			$nLines = count($arrLines)-$nOffsetLine + 1 ;
		}
		
		$arrLines = array_slice($arrLines,$nOffsetLine,$nLines) ;
		
		return $arrLines ;
	}
}

?>