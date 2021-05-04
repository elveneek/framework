<?php

/*
	В данном (и подобных) классах перечислены компоненты. С точки зрения получения данных, многие они похожи друг на друга
	(например small и rich отличаются только внешним видом, шаблоном), но при этом механизм передачи именно данных должен быть описан.
	При этом т.н. "поведения по умолчанию" быть не может - генератор для фильтра и генератор формы для администраторской панели имеют разное
	поведение по-умолчанию, но оба являются генераторами.
	

*/
class AdminLibrary extends AbstractLibrary{
	
	/*
	//Можно переопределить метод componentExists.
	function componentExists($name){
		return method_exists($this, $name);
	}
	*/
	
	
	function small($params){
		return $this->simple($params);
	}
	
	function big($params){
		return $this->simple($params);
	}
	
	function rich($params){
		return $this->simple($params);
	}
	
	function checkbox($params){
		return $this->simple($params);
	}
	
	function url_translit($params){
		return $this->simple($params);
	}
	
	function legend($params){
		return $this->decorative($params);
	}
	
	function container_begin($params){
		return $this->decorative($params);
	}
	
	function container_end($params){
		return $this->decorative($params);
	}
	 
	
	function all_fields($params){
		
	}
	
	function checkboxes($params){
		
	}
	function connected_checkboxes($params){
		
	}
	function container($params){
		
	}
	function date($params){
		
	}
	function image($params){
		
	}
	function file($params){
		
	}
	function eval($params){
		
	}
	function select($params){
		
	}
	function select_table($params){
		
	} 
	
}