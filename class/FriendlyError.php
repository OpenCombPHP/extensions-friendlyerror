<?php 
namespace org\opencomb\friendlyerror ;

use org\jecat\framework\lang\Object;

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
    	__HighterActiver::singleton() ;
    	
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
    	
    	__HighterActiver::singleton() ;
    	    	
		$aUI = UIFactory::singleton()->create() ;
		$aUI->display('friendlyerror:ErrorMessage.html',array(
    			'nErrorCode' => $nErr ,
    			'nErrorMessage' => $sErrMsg ,
    			'sFile' => $sFile ,
    			'nLine' => $nLine ,
    			'context' => $context ,
    			'nErrorIdx' => self::$nErrorIdx++ ,
    			'arrCalltrace' => self::typeCalltrace(debug_backtrace()) ,
    	)) ;
	}
	
	static public function typeCalltrace($arrCalltrace)
	{
		foreach($arrCalltrace as &$arrCall)
		{
	    	if( preg_match("/(.+)\\((\\d+)\\) : eval\\(\\)'d code$/", $arrCall['file'],$arrRes) )
	    	{
	    		$arrCall['file'] = $arrRes[1] ;
	    		$arrCall['line'] = (int)$arrRes[2] ;
	    	}
	    		    	
	    	$arrCall['segmentOffset'] = $arrCall['line']-self::$nSegmentRange ;
	    	if($arrCall['segmentOffset']<0)
	    	{
	    		$arrCall['segmentOffset'] = 0 ;
	    	}
	    	$arrCall['segmentLength'] = self::$nSegmentRange*2+1 ;
		}
		
		return $arrCalltrace ;
	}
	
	static public function readSourceSegment($sFile,$nOffsetLine,$nLines=5)
	{
    	
		$arrLines = (array)@file($sFile) ;
		
		if( count($arrLines)<$nOffsetLine+$nLines )
		{
			$nLines = count($arrLines)-$nOffsetLine + 1 ;
		}
	
		$arrLines = array_slice($arrLines,$nOffsetLine,$nLines) ;
	
		return $arrLines ;
	}
	
	static private $nErrorIdx = 0 ;
	static private $nSegmentRange = 5 ;
}

class __HighterActiver extends Object
{
	function __destruct()
	{
		echo "
<script type=\"text/javascript\">
//隐藏无用的工具栏
SyntaxHighlighter.defaults['toolbar'] = false;
//启动语法高亮
SyntaxHighlighter.all();
</script>" ;
	}
}