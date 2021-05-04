<?php

namespace Elveneek; 

/*
Три режима

нет ничего - запросов не делается
есть строка, но не спрашиваем поля сессий, только SID (для корзины существующей)
нет ничего, не спрашиваем поля сессий, только SID (для того чтобы организовать корзину) - в этом случае в момент первого прочтения создается и запоминается session_id





*/
class SessionsMiddleware{
	
	function process($request, $requestHandler){
		
		d()->locals['session'] = new \Elveneek\Session($request);
		//Запоминаем, была ли сессия пустой до того, как сработал код
		$beenEmpty = d()->locals['session']->isEmptyNow;
		
		$response = $requestHandler->handle($request);
		
		//Устанавливаем сессионную куку в случае: 1) было пусто (чтобы не перезаписывать много раз) и 2) сейчас она не пустая
		if($beenEmpty && !d()->locals['session']->isEmptyNow){
			$cookie = \HansOtt\PSR7Cookies\SetCookie::thatStaysForever(\Elveneek\Session::ELVSID, d()->locals['session']->id());
			$responseWithCookie = $cookie->addToResponse($response);
			return $responseWithCookie;
		}
		
		return $response;
	}
}
 