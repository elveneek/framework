<?php
$app = d();
$app->locals['admin'] = new Admin();


$app->get('/admin', function(){
	/* форма авторизации и дашборд */
	//Проверяем авторизацию

	print  View::render('/admin/login.html');
	return ;
});

$app->get('/admin/logout', function(){
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
			d()->loginNotOk=true;
			d()->loginUsed=$login;
			print  View::render('/admin/login.html');
			return ;
		}
		d()->adminAuth->login($login, $request);

		print 'залогинен';
	}
	return;
});




$app->get('/admin/:tablename/:method', function($tablename, $method,    $request){

	$generator = new UIGenerator();
	$generator->addControlsDir('/admin/controls');
	$template = "/fields/"  . $tablename . '.ehtml';
	$ehtml =  View::renderEHTML($template, $method, ['request'=>$request, 'generator'=>$generator]);
	return View::render('/admin/templates/edit.html', ['ehtml'=>$ehtml]);

});

$app->get('/admin/:tablename/:method/:id', function($tablename, $method, $id,    $request){

	$generator = new UIGenerator();
	$generator->addControlsDir('/admin/controls');
	$template = "/fields/"  . $tablename . '.ehtml';
	if($id == "add"){
        $generator->setContextValue("id", "new_". uniqid());
        d()->this = ActiveRecord::fromTable($tablename)->where('false');
    }else{
        $generator->setContextValue("id", $id);
        d()->this = ActiveRecord::fromTable($tablename)->find($id);
    }
    $generator->setContextValue("table", $tablename);

	$ehtml =  View::renderEHTML($template, $method, ['request'=>$request, 'generator'=>$generator]);
	return View::render('/admin/templates/edit.html', ['ehtml'=>$ehtml]);

});

$app->post('/admin/save', function($request){

	//сохранение данных

	\Elveneek\AdminSave::call(
		data: json_decode((string)($request->getBody()), true)
	);
	print 'alert(1);';

});

