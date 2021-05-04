<?php
$app = d();
$app->locals['admin'] = new Admin();

 
$app->get('/admin', function(){
	/* форма авторизации и дашборд */
	//Проверяем авторизацию
	
	print  View::render('/admin/login.html');
	return ;
});
$app->post('/admin/login', function($request, $response){
	
	$login = $request->getParsedBody()['login'];
	$password = $request->getParsedBody()['password'];
 
	if(true){
		//Проверяем, пускаем ли пользователя в систему
		$passwordAndLoginOK = d()->adminAuth->checkUserPassword($login, $password);
		if(!$passwordAndLoginOK){
			print 'Логин или пароль неверный';
			return;
		}
		d()->adminAuth->login($login, $request);
	
		print 'залогинен';
	}
	return;
});

$app->get('/admin/list/:tablename', function($tablename){
	//filter[] && sort[] && paginate
	
});


$app->get('/admin/edit/:tablename/:id', function($tablename, $id){
	return View::render('/admin/templates/edit.html');
	
});

$app->post('/admin/save/:tablename', function($tablename, $id){
	
	
});

$app->get('/admin/:tablename/:method', function($tablename, $method, Nyholm\Psr7\ServerRequest  $request){
	
	$generator = new UIGenerator();
	$generator->addControlsDir('/admin/controls');
	$template = "/fields/"  . $tablename . '.ehtml';
	print View::renderEHTML($template, $method, ['request'=>$request, 'generator'=>$generator]);
	
});


