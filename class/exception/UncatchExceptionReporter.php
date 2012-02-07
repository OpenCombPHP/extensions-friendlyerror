<?php
namespace org\opencomb\friendlyerror\exception ;

use org\opencomb\friendlyerror\__HighterActiver;
use org\opencomb\friendlyerror\ErrorReporter;
use org\jecat\framework\resrc\HtmlResourcePool;
use org\opencomb\advcmpnt\lib\LibManager;
use org\jecat\framework\mvc\controller\Controller;

class UncatchExceptionReporter extends Controller
{	
	public function process()
	{
		LibManager::singleton()->loadLibrary('syntaxhighlighter:php') ;
		__HighterActiver::singleton() ;
		
		$aPrinter = $this->response()->printer() ;
		
		$aPrinter->write( HtmlResourcePool::singleton() ) ;
		$aPrinter->write("<h2>系统遇到无法处理的异常</h2>") ;
		
		$aException = $this->params['exception'] ;
		$nExceptionIdx = 0 ;
		
		do{
			
			$aPrinter->write("<div style=\"font-size:11px;margin-top:25px;margin-left:".($nExceptionIdx*40)."px\">") ;
			
			$aPrinter->write("<h4>异常类：".get_class($aException)."</h4>") ;
			
			$aPrinter->write("<div>") ;
			$aPrinter->write("<div>Code：".$aException->getCode()."</div>") ;
			$aPrinter->write("<div>") ;
			
			$aPrinter->write("<pre>") ;
			if( $aException instanceof \org\jecat\framework\lang\Exception )
			{
				$aPrinter->write($aException->message()) ;
			}
			else
			{
				$aPrinter->write($aException->getMessage()) ;
			}
			$aPrinter->write("</pre>") ;
			
			$aPrinter->write("</div>") ;
			$aPrinter->write("</div>") ;
			
			// 发生位置
			$aPrinter->write("<div>") ;
			$aPrinter->write("发生位置：".$aException->getFile()." (Line:".$aException->getLine().")") ;
			ErrorReporter::singleton()->outputExecutePoint($aPrinter,$aException->getFile(),$aException->getLine()) ;
			$aPrinter->write("</div>") ;
			
			// 执行堆栈
			// 
			
			$sDivId = "error-{=$nExceptionIdx}-calltrace" ;
			$aPrinter->write("[<a href=\"javascript:void(0)\" onclick=\"".ErrorReporter::toggleDivJs($sDivId)."\">调用堆栈</a>]") ;
			$aPrinter->write("<div id='{$sDivId}'>") ;
			ErrorReporter::singleton()->outputCallStack($aException->getTrace(),$aPrinter) ;
			$aPrinter->write("</div>") ;
			
			$aPrinter->write("<hr /></div>") ;
			
			$nExceptionIdx ++ ;
		}while($aException=$aException->getPrevious()) ;
		
		
		//$this->view->variables()->set('aException',$this->params['exception']) ;
		//$this->view->variables()->set('arrCalltrace',$this->params['calltrace']) ;
	}
}

?>