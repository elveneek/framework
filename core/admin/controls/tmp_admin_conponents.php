<?php

UIGenerator::addComponent('small', AdminComponentSimple::class,  ELVENEEKROOT . 'core/admin/controls/small.html');
UIGenerator::addComponent('big', AdminComponentSimple::class, ELVENEEKROOT . 'core/admin/controls/big.html');
 

$app->route('/testgenarator', function(){
	
	$gen = new UIGenerator() ;
	print $gen->renderComponent('small', ['title', 'Текст']);
	
});
/*




$gen->renderComponent('small', ['title', 'Текст']);
$gen->renderComponent('big', ['title', 'Текст']);
$gen->renderEHTML('ehtml.ehtml');

*/