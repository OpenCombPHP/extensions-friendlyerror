<?php
namespace org\opencomb\friendlyerror ;

use org\jecat\framework\lang\Type;

use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\lang\Object;
use org\jecat\framework\ui\xhtml\UIFactory;
use org\jecat\framework\mvc\controller\Response;

class ErrorReporter extends Object
{
	static public function errorHandler($nErr,$sErrMsg,$sFile,$nLine,$context)
 	{
 		// skip @ operator
 		if (error_reporting() == 0)
 		{
        	return ;
    	}
    	
    	$arrCalltrace = debug_backtrace() ;
    	if( @$arrCalltrace[0]['class']==__CLASS__ and @$arrCalltrace[0]['function'] )
    	{
    		unset($arrCalltrace[0]) ;
    		$arrCalltrace = array_values($arrCalltrace) ;
    	}
    	
    	self::singleton()->reportError($nErr,$sErrMsg,$sFile,$nLine,$arrCalltrace) ;
	}
	
	public function reportError($nErr,$sErrMsg,$sFile,$nLine,$arrCalltrace,IOutputStream $aOutput=null)
	{
		if(!$aOutput)
		{
			$aOutput = Response::singleton()->printer() ;
		}
		
		FriendlyError::enableSyntaxHighLighter() ;

		$sErrType = self::$arrErrorTypes[$nErr] ;
		$nErrorIdx = self::$nErrorIdx ++ ;
				

		
		$aOutput->write( <<<OUTPUT
<div style="font-size:11px;">
	<div>{$sErrType} ({$nErr}): {$sErrMsg}</div>
	<div>
		发生位置：{$sFile} （Line: {$nLine}）
OUTPUT
		) ;
		
		$this->outputExecutePoint($aOutput,$sFile,$nLine) ;
		
		$sDivId = "error-{$nErrorIdx}-calltrace" ;
		$aOutput->write("[<a href=\"javascript:void(0)\" onclick=\"".self::toggleDivJs($sDivId)."\">调用堆栈</a>]") ;
		$aOutput->write("<div style='display:none' id='{$sDivId}'>") ;
	
		$this->outputCallStack($arrCalltrace,$aOutput) ;
		
		$aOutput->write("</div></div>") ;
	}
	
	public function outputCallStack($arrCalltrace,IOutputStream $aOutput=null)
	{
		$this->tidyCalltrace($arrCalltrace) ;
		
		foreach($arrCalltrace as $nStackIdx=>$arrCall)
		{
			$aOutput->write('<div style="margin-left:30px;">') ;
			
			$aOutput->write("#{$nStackIdx}") ;
			
			$this->outputExecutePoint($aOutput,$arrCall['file'],$arrCall['line'],@$arrCall['function'],@$arrCall['args'],@$arrCall['class'],@$arrCall['type']) ;
			
			$aOutput->write('</div>') ;
		}
	}
	
	public function outputExecutePoint(IOutputStream $aOutput,$sFile,$nLine,$sFunction=null,$arrArgvs=array(),$sClass='',$sCallType='')
	{
		$nExecutePointIdx = self::$nExecutePointIdx ++ ;
		$sDivId = "source-executepoint-{$nExecutePointIdx}" ;
		
		// 文件源码链接
		if($sFile)
		{
			$aOutput->write("[<a href=\"javascript:void(0);\" onclick=\"".self::toggleDivJs($sDivId)."\">source</a>]") ;
		}
		
		// 函数名称 和 参数表
		if($sFunction)
		{
			$aOutput->write("{$sClass}{$sCallType}{$sFunction}(") ;
			foreach (array_values($arrArgvs) as $i=>$argv)
			{
				if($i)
				{
					$aOutput->write(",") ;
				}
				$aOutput->write(is_object($argv)? 'object': Type::reflectType($argv)) ;
			}
			$aOutput->write(")") ;
		}
		
		// 源文件内容
		if($sFile)
		{
			$nSourceSegmentOffset = $nLine - 30 ;
			if($nSourceSegmentOffset<0)
			{
				$nSourceSegmentOffset = 0 ;
			}
			$nSegmentLength = $nLine-$nSourceSegmentOffset + 1 + 10 ;
			
			$aOutput->write("<div style=\"margin-left:30px;color:#999999;display:none\" id='{$sDivId}'>") ;
			
			$aOutput->write("{$sFile} (Line: {$nLine})") ;
			$aOutput->write("<pre class=\"brush: php; first-line: {$nSourceSegmentOffset}; highlight: [{$nLine}]\">") ;
			$aOutput->write("// ... ...\r\n") ;
			foreach (self::readSourceSegment($sFile,$nSourceSegmentOffset,41) as $sLineContents)
			{
				$aOutput->write($sLineContents) ;
			}
			$aOutput->write("// ... ...\r\n") ;
			$aOutput->write('</pre>') ;
			
			$aOutput->write('</div>') ;
		}
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
	
	static public function readSourceSegment($sFile,$nOffsetLine,$nLines=5)
	{
		$arrLines = (array)@file($sFile) ;
		
		// 处理 <<<doc 语法（syntaxhighlighter组件不支持该语法）
		foreach($arrLines as &$sLine)
		{
			$sLine = htmlspecialchars($sLine) ;
		}
		
		if( count($arrLines)<$nOffsetLine+$nLines )
		{
			$nLines = count($arrLines)-$nOffsetLine + 1 ;
		}
	
		$arrLines = array_slice($arrLines,$nOffsetLine,$nLines) ;
	
		return $arrLines ;
	}
	
	static public function toggleDivJs($sDivId)
	{
		return "document.getElementById('{$sDivId}').style.display = (document.getElementById('{$sDivId}').style.display=='none')? 'block':'none'" ; 
	}

	static private $nExecutePointIdx = 0 ;
	
	static private $nErrorIdx = 0 ;
	static private $nCalltraceIdx = 0 ;
	static private $nSegmentRange = 10 ;
	
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