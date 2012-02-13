<?php 
namespace org\opencomb\friendlyerror ;

use org\opencomb\advcmpnt\lib\LibManager;

use org\jecat\framework\resrc\HtmlResourcePool;

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
		set_error_handler( array('org\\opencomb\\friendlyerror\\ErrorReporter','errorHandler'), E_ALL ) ;
	}
	
	static public function uncatchExceptionHandler(\Exception $aException)
	{
    	__HighterActiver::singleton() ;
    	
		$aExceptionReporter = new UncatchExceptionReporter( array('exception'=>$aException) ) ;
		$aExceptionReporter->mainRun() ;
		return ;
	}
	
}

class __HighterActiver extends Object
{
	function __construct()
	{
		LibManager::singleton()->loadLibrary('syntaxhighlighter:php') ;
	}
	
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