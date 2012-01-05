<?php
namespace org\opencomb\friendlyerror ;

use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\lang\Object;
use org\jecat\framework\ui\xhtml\UIFactory;
use org\jecat\framework\system\Response;

class ErrorReporter extends Object
{
	static public function errorHandler($nErr,$sErrMsg,$sFile,$nLine,$context)
 	{
 		// skip @ operator
 		if (error_reporting() == 0)
 		{
        	return ;
    	}
    	
    	self::singleton()->reportError($nErr,$sErrMsg,$sFile,$nLine,debug_backtrace()) ;
	}
	
	public function reportError($nErr,$sErrMsg,$sFile,$nLine,$arrCalltrace,IOutputStream $aOutput=null)
	{
		if(!$aOutput)
		{
			$aOutput = Response::singleton()->printer() ;
		}

		__HighterActiver::singleton() ;

		$sErrType = self::$arrErrorTypes[$nErr] ;
		$nErrorIdx = self::$nErrorIdx ++ ;
		
		$aOutput->write(
<<<OUTPUT
<div style="font-size:11px;">
	{$sErrType} ({$nErr}): {$sErrMsg} <a href="javascript:jquery('#error-{$nErrorIdx}-calltrace').toggle()">调用堆栈</a>
	<div style="display:none" id='error-{$nErrorIdx}-calltrace'>
OUTPUT
		) ;
		
		$this->outputCalltrace($arrCalltrace,$aOutput) ;
		
		$aOutput->write("</div></div>") ;
	}
	
	public function outputCalltrace($arrCalltrace,IOutputStream $aOutput=null)
	{
		if( !class_exists('org\\jecat\\framework\\ui\\UIFactory') or !class_exists('org\\jecat\\framework\\ui\\UI') )
		{
			return ;
		}
				
		__HighterActiver::singleton() ;
		
		$aUI = UIFactory::singleton()->create() ;
		$aUI->display('friendlyerror:DisplayCalltrace.html',array(
				'arrCallTrace' => $this->tidyCalltrace($arrCalltrace) ,
				'sCallTraceId' => 'error-'.(self::$nCalltraceIdx++).'-calltrace' ,
		),$aOutput) ;
	}
	
	public function tidyCalltrace($arrCalltrace)
	{
		// 顺序
		$arrCalltrace=array_reverse($arrCalltrace);
		$arrCalltrace=array_reverse($arrCalltrace,true);
	
		foreach($arrCalltrace as &$arrCall)
		{
			// 处理 eval()
			if( preg_match("/(.+)\\((\\d+)\\) : eval\\(\\)'d code$/", @$arrCall['file'],$arrRes) )
			{
				$arrCall['file'] = $arrRes[1] ;
				$arrCall['line'] = (int)$arrRes[2] ;
			}
	
			// 代码片段范围
			$arrCall['segmentOffset'] = @$arrCall['line']-self::$nSegmentRange ;
			if($arrCall['segmentOffset']<0)
			{
				$arrCall['segmentOffset'] = 0 ;
			}
			$arrCall['segmentLength'] = self::$nSegmentRange*2+1 ;
		}
	
		return $arrCalltrace ;
	}
	
	public function readSourceSegment($sFile,$nOffsetLine,$nLines=5)
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
	static private $nCalltraceIdx = 0 ;
	static private $nSegmentRange = 5 ;
	
	static private $arrErrorTypes = array (
			E_ERROR            => 'Error',
			E_WARNING        => 'Warning',
			E_PARSE          => 'Parsing Error',
			E_NOTICE         => 'Notice',
			E_CORE_ERROR     => 'Core Error',
			E_CORE_WARNING   => 'Core Warning',
			E_COMPILE_ERROR  => 'Compile Error',
			E_COMPILE_WARNING => 'Compile Warning',
			E_USER_ERROR     => 'User Error',
			E_USER_WARNING   => 'User Warning',
			E_USER_NOTICE    => 'User Notice',
			E_STRICT         => 'Strict Notice',
			E_RECOVERABLE_ERROR  => 'Recoverable Error'
	);
}

?>