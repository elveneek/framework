<?php

class ResponseException extends Exception
{
	public $response;
	public function __construct(\Psr\Http\Message\ResponseInterface $response) {
		$this->response = $response;
		parent::__construct("Uncatchable response exception");
	}
	function getResponse(){
		return $this->response;
	}
	
}
