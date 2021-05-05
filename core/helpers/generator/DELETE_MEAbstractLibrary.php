<?php

class AbstractLibrary{
	function componentExists($name){
		return method_exists($this, $name);
	}
}