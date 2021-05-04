<?php
namespace Elveneek;
class RouteCallback {
	public $callback;
	public $currentIncludedFilename;
	public $currentNicePath;
	public $currentURL;
	function __construct($callback, $currentIncludedFilename, $currentURL){
		$this->callback = $callback;
		$this->currentIncludedFilename = $currentIncludedFilename;
		//Обрезаем полный путь к файлу (оставляем только директорию)
		if(substr($currentIncludedFilename,0,strlen(ELVENEEKROOT))==ELVENEEKROOT){
			$this->currentNicePath =  dirname(substr($currentIncludedFilename, strlen(realpath (ELVENEEKROOT))+1));
		}else{
			$this->currentNicePath =  dirname(substr($currentIncludedFilename, strlen(realpath (ROOT))+1));
		}
		
		
		$this->currentURL = $currentURL;
		\View::$routesPaths[dirname($currentIncludedFilename)]=true;	
		/*
		if(DIRECTORY_SEPARATOR !== '/'){
			View::$routesPaths[ str_replace( DIRECTORY_SEPARATOR, '/',dirname($currentIncludedFilename))]=true;	
		}else{
			View::$routesPaths[dirname($currentIncludedFilename)]=true;	
		}*/
		
		//Так как View отвечает за однозначное соответствие роутов и всего остального, пусть сам разбирается.
	}
}