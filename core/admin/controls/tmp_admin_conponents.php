<?php

UIGenerator::addComponent('small', AdminComponentSimple::class, '/admin/controls/small.html');
UIGenerator::addComponent('big', AdminComponentSimple::class, '/admin/controls/big.html');
 

d()->route('/testgenarator', function(){
	
	$gen = new UIGenerator() ;
	print $gen->renderComponent('small', ['title', 'Текст']);
	
});
/*




$gen->renderComponent('small', ['title', 'Текст']);
$gen->renderComponent('big', ['title', 'Текст']);
$gen->renderEHTML('ehtml.ehtml');

*/