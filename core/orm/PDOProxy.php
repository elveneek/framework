<?php 

class PDOProxy extends \PDO {
 
  
	public function prepare($statement, $driver_options = array())
	{
		try {
			return parent::prepare($statement, $driver_options);
		} catch  (PDOException $exception) {
			if($exception->getCode() == 'HY000' && $exception->errorInfo[1]==2006){
				App::$instance->db = ActiveRecord::connect();
				return App::$instance->db->prepare($statement, $driver_options);
			}
			throw $exception;
		}
	}
	/* 
	public function query($statement, $fetch_style=false, $classname=false, $ctorargs=false)
	{
		try {
			if($fetch_style===false){
				$result = parent::query($statement);
			}elseif($classname===false){
				$result = parent::query($statement, $fetch_style);
			}elseif($classname===false){
				$result = parent::query($statement, $fetch_style, $classname);
			}else{
				$result = parent::query($statement, $fetch_style, $classname, $ctorargs);
			}
		}catch  (PDOException $exception) {
			if($exception->getCode() === 'HY000' && $exception->errorInfo[1]===2006){
				App::$instance->db = ActiveRecord::connect();
				return call_user_func_array(array(App::$instance->db, 'query'), func_get_args());
			}
			throw $exception;
		}
		
		return($result);
	}
	*/
	public function query($statement, $fetch_style = null, ...$fetch_mode_args)
	{
		try {
			if($fetch_style===null){
				$result = parent::query($statement);
			}elseif($classname===false){
				$result = parent::query($statement, $fetch_style);
			}elseif($classname===false){
				$result = parent::query($statement, $fetch_style, $classname);
			}else{
				$result = parent::query($statement, $fetch_style, $classname, $ctorargs);
			}
		}catch  (PDOException $exception) {
			if($exception->getCode() === 'HY000' && $exception->errorInfo[1]===2006){
				App::$instance->db = ActiveRecord::connect();
				return call_user_func_array(array(App::$instance->db, 'query'), func_get_args());
			}
			throw $exception;
		}
		
		return($result);
	}
	
	
	
	public function exec($statement)
	{
		try {
			return parent::exec($statement);
		} catch  (PDOException $exception) {
			if($exception->getCode() == 'HY000' && $exception->errorInfo[1]==2006){
				App::$instance->db = ActiveRecord::connect();
				return App::$instance->db->exec($statement);
			}
			throw $exception;
		}
	} 
}