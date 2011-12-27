<?php 
namespace org\opencomb\friendlyerror ;

use org\opencomb\friendlyerror\exception\UncatchExceptionReporter;
use org\opencomb\platform\ext\Extension ;

class FriendlyError extends Extension 
{
	/**
	 * 载入扩展
	 */
	public function load()
	{
		// todo ...
	}
	
	public function active()
	{
		// 未捕获异常
		set_exception_handler( array(__CLASS__,'uncatchExceptionHandler') ) ;
		
		throw new \Exception('xx') ;
	}
	
	static public function uncatchExceptionHandler(\Exception $aException)
	{
		$aExceptionReporter = new UncatchExceptionReporter( array('exception'=>$aException) ) ;
		$aExceptionReporter->mainRun() ;
		return ;
		
		$sContents = "<pre>" ;
	
		do{
			
			$sContents.= "------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------\r\n" ;
			
			$sContents.= "0无法处理的异常：".get_class($aException)."\r\n" ;
				
			if($aException instanceof \org\jecat\framework\lang\Exception)
			{
				$sContents.= $aException->message()."\r\n" ;
			}
			else
			{
				$sContents.= $aException->getMessage()."\r\n" ;
			}
			
			$sContents.= 'Line '.$aException->getLine().' in file: '.$aException->getFile()."\r\n" ;
			$sContents.= $aException->getTraceAsString()."\r\n" ;
		
		// 递归 cause
		} while( $aException = $aException->getPrevious() ) ;
		
		$sContents.= "</pre>\r\n" ;
		
		echo $sContents ;
	}
}