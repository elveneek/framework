<?php

namespace Elveneek; 

class Session{
	
	const ELVSID = 'ELVSID';
	 
	 
	public $isEmptyNow = true;
	protected $_sid = "";
	
	public function __construct($request){
		
		$cookies = $request->getCookieParams();
		if(!isset($cookies[static::ELVSID])){
			//Куки нет совсем
			$this->isEmptyNow = true;
		}else{
			$this->isEmptyNow = false;
			$this->_sid = $cookies[static::ELVSID];
		}
	}
	
	//Возвращает true, если сессионной куки не стояло. Позволяет при организации корзины/авторизации вообще не лезть в таблицы с заказами/ключами авторизации
	public function isEmpty(){
		return $this->isEmptyNow;
	}
	
	public function __get($name){
		if($name=='id'){
			$this->isEmptyNow = false;
			/* ЕСЛИ $persistent == false то при генерации куки сделает её временной (сессионной) */
			if($this->_sid == ''){
				$this->_sid = bin2hex(random_bytes(16));
			}
			return $this->_sid;
		}
		
		return '';
	}
	//Возвращает текущий session_id из куки, либо если его не было, генерирует новый и сохраняет в куку. После этого is_empty будет возвращать false
	public function id($persistent=true){
		$this->isEmptyNow = false;
		/* ЕСЛИ $persistent == false то при генерации куки сделает её временной (сессионной) */
		if($this->_sid == ''){
			$this->_sid = bin2hex(random_bytes(16));
		}
		return $this->_sid;
	}
	
}
 