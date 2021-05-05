<?php

class AdminComponentSimple{
	
	function prepareData($input){
		
		$id=d()->this->id;
		$table = d()->this->table;
		$input['comment']='';	
		if(isset($input[2])){
			$input['comment']=$input[2];
		}
		
		$input['name']='data[' . $table . ']['. $id .']';
		$input['title']=$input[1];
		$input['value']=d()->this->get($input[0]);
		
		return $input;
	}
}