<?php

class HyperAdminMiddleware{
	
	function process($request, $requestHandler){
		
		$uri = $request->getUri()->getPath();
		if(substr($uri,0,6)=='/admin'){
			//Мы в режиме админки. плюём на всё, передаём управление в код
			return d()->process($request, d());
		}
		$response = $requestHandler->handle($request);
		return $response->withHeader('X-FirstHeader', 'first');
	}
}
 