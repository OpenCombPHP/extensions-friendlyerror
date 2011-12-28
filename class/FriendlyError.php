<?php 
namespace org\opencomb\friendlyerror ;

use org\jecat\framework\lang\Type;

use org\jecat\framework\mvc\view\UIFactory;
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
		
		// 错误
		set_error_handler( array(__CLASS__,'errorHandler'), E_ALL ) ;
	}
	
	static public function uncatchExceptionHandler(\Exception $aException)
	{
		$aExceptionReporter = new UncatchExceptionReporter( array('exception'=>$aException) ) ;
		$aExceptionReporter->mainRun() ;
		return ;
	}
	
	static public function errorHandler($nErr,$sErrMsg,$sFile,$nLine,$context)
 	{
 		// skip @ operator
 		if (error_reporting() == 0)
 		{
        	return ;
    	}
    	
		$aUI = UIFactory::singleton()->create() ;
		$aUI->display('friendlyerror:ErrorMessage.html',array(
				'nErrorCode' => $nErr ,
				'nErrorMessage' => $sErrMsg ,
				'sFile' => $sFile ,
				'nLine' => $nLine ,
				'context' => $context ,
				'nErrorIdx' => self::$nErrorIdx++ ,
				'arrCalltrace' => debug_backtrace() ,
		)) ;
	}
	
	static public function readSourceSegment($sFile,$nLine,$nRange=5)
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
	
	static private $nErrorIdx = 0 ;
}