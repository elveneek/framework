<?php

namespace Elveneek; 

/*
Три режима

нет ничего - запросов не делается
есть строка, но не спрашиваем поля сессий, только SID (для корзины существующей)
нет ничего, не спрашиваем поля сессий, только SID (для того чтобы организовать корзину) - в этом случае в момент первого прочтения создается и запоминается session_id





*/
class SessionsMiddleware{
	private $app;
	public function __construct($app){
		$this->app = $app;
	}
	function process($request, $requestHandler){
		
		$this->app->locals['session'] = new \Elveneek\Session($request);
		//Запоминаем, была ли сессия пустой до того, как сработал код
		$beenEmpty = $this->app->locals['session']->isEmptyNow;
		
		$response = $requestHandler->handle($request);
		
		//Устанавливаем сессионную куку в случае: 1) было пусто (чтобы не перезаписывать много раз) и 2) сейчас она не пустая
		if($beenEmpty && !$this->app->locals['session']->isEmptyNow){
			$cookie = \HansOtt\PSR7Cookies\SetCookie::thatStaysForever(\Elveneek\Session::ELVSID, $this->app->locals['session']->id());
			$responseWithCookie = $cookie->addToResponse($response);
			return $responseWithCookie;
		}
		
		return $response;
	}
}
 