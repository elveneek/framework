<?php

class UIGenerator{
	public static $components = [];
	public $controlsDirs=[];

    public $context=[];

	static function addComponent($name, $class, $template){
		static::$components[$name]=[
			"class"=>$class,
			"template"=>$template,
		];
	}

	function addControlsDir($directory){
		$this->controlsDirs[]=$directory;
	}

	function setContextValue($name, $value){
        $this->context[$name] = $value;
    }

	function renderComponent($component, $data){

		if(!isset(static::$components[$component])){
			return "<div>Component <b>$component</b> not found</div>";
		}

		$controlClass=static::$components[$component]['class'];
		//return "Я рендерю компонет $component";
		$template =static::$components[$component]['template'];

		$control = new $controlClass();


		$newData = $control->prepareData(array_merge($this->context, $data));
		return View::partial($template, $newData);
	}


}
