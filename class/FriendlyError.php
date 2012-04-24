<?php 
namespace org\opencomb\friendlyerror ;

use org\opencomb\platform\service\Service;

use org\opencomb\coresystem\lib\LibManager;
use org\opencomb\friendlyerror\exception\UncatchExceptionReporter;
use org\opencomb\platform\ext\Extension;

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
		
		// 关闭事件
		register_shutdown_function(array(__CLASS__,'shutdown')) ;
	}
	
	static public function uncatchExceptionHandler(\Exception $aException)
	{
    	self::enableSyntaxHighLighter() ;
    	
		$aExceptionReporter = new UncatchExceptionReporter( array('exception'=>$aException) ) ;
		$aExceptionReporter->mainRun() ;
		return ;
	}
	
	static public function enableSyntaxHighLighter()
	{
		self::$bEnableSyntaxHighLighter = true ;
	}
	
	static public function shutdown()
	{
		if( self::$bEnableSyntaxHighLighter )
		{
			foreach( LibManager::singleton()->libraryFileIterator('js','syntaxhighlighter:php') as $sFile )
			{
				echo "<script src='".Service::singleton()->publicFolders()->find($sFile,'*',true)."'></script>\r\n" ;
			}
			foreach( LibManager::singleton()->libraryFileIterator('css','syntaxhighlighter:php') as $sFile )
			{
				echo "<link rel='stylesheet' type='text/css' href='".Service::singleton()->publicFolders()->find($sFile,'*',true)."' />\r\n" ;
			}
			
			echo "
<script type=\"text/javascript\">
//隐藏无用的工具栏
SyntaxHighlighter.defaults['toolbar'] = false;
//启动语法高亮
SyntaxHighlighter.all();
</script>" ;
		}
	}
	
	static $bEnableSyntaxHighLighter = false ;
}

