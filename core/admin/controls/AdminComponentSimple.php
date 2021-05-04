<?php

class AdminComponentSimple{
	
	function prepareData($input){
		$input['name']="new value";
		$input['id']="15";
		$input['title']=$input[0];
		return $input;
	}
}