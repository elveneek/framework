<?php

function abc3(){
	return 2;
} 
 
$_ENV['DB_HOST'] = "localhost";
$_ENV['DB_NAME'] = "elveneek";
$_ENV['DB_PASSWORD'] = "1122";
$_ENV['DB_USER'] = "root";
 
 
 
require_once  dirname (dirname (dirname (__DIR__))) . '/core/core.php' ;
\Elveneek\ActiveRecord::$db = false; //Для того, чтобы система ЗНАЛА что этот класс есть, иначе автолоадер пытается сделаеть его из его
require_once      (dirname (__DIR__)) .  '/app/App1.php';




class MyCustomComponentForTesting{

	function prepareData($input){
        $input['first_param']=$input[0];
        $input['second_param']=$input[1];
		return $input;
	}
}



class WarningWithStacktrace extends ErrorException {}
set_error_handler(function($severity, $message, $file, $line) {
    if ((error_reporting() & $severity)) {
        if ($severity & (E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE)) {
            $ex = new WarningWithStacktrace($message, 0, $severity, $file, $line);
            echo "\n" . $ex . "\n\n";
            return true;
        } else {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
    }
});
 
test('test_view_paths', function () {
    $app  =  new App1();

    //Если добавить явно ничгео не должно ломаться
    View::addTemplate( dirname(__DIR__).'/app/template_root.html');
    
    //явно нужно регистрировать шаблоны с абсолютным путем
    View::addTemplate( dirname(__FILE__).'/directory_with_template/template3.html');

    View::setTemplateRoot( dirname(__DIR__).'/app');
    expect(View::getTemplateByFile("/template_root.html"))->toBe( str_replace( DIRECTORY_SEPARATOR, '/',  dirname(__DIR__).'/app/template_root.html')  );
    //Дублирование для проверки кеширования
    expect(View::getTemplateByFile("/template_root.html"))->toBe( str_replace( DIRECTORY_SEPARATOR, '/',  dirname(__DIR__).'/app/template_root.html')  );
    

    expect(View::partial("/template_root.html"))->toBe(  'template_root_value' );
    expect(View::partial(dirname(__DIR__).'/app\\template_root2.html'))->toBe(  'template_root_value2' );
    expect(View::partial('directory_with_template/template3.html'))->toBe(  'template_value3' );
    expect(View::partial('directory_with_template/template3.html'))->toBe(  'template_value3' );
    
    expect(View::partial("../app/template_root.html"))->toBe(  'template_root_value' );
    expect(View::render("../app/template_root_with_layout.html"))->toBe(  "<header>\ntemplate_root_value\n</header>" );
    expect(View::render("../app/template_root_with_layout_oneline.html"))->toBe(  "<header>template_root_value</header>" );
    
    
    expect(View::render('directory_with_template/template3.html'))->toBe(  '<main>template_value3</main>' );
    
    expect(View::partial("/with_var.html", ["var1"=>"variable_value"]))->toBe(  'variable_value' );

    expect(View::partial("/template_with_includes.html"))->toBe(  'template_root_valuetemplate_root_valuetemplate_root_value2template_root_value2' );
    expect(View::partial("file_in_directory.html", relativePath: dirname(__DIR__).'/app/anotherdirectory' ))->toBe(  'file_in_directory_value' );


    //Тестируем ehtml

   
 

    UIGenerator::addComponent('small_custom_test_component', MyCustomComponentForTesting::class, '/components/small_custom_test_component_template.html');
    $generator = new UIGenerator();
   
    
    expect(  View::renderEHTML('/products.ehtml', 'edit', ['generator'=>$generator]) )->toBe(  "<elements>\n<component>title</component> \n</elements>" );
    //expect(  View::renderEHTML('/products.ehtml', 'edit', ['generator'=>$generator]) )->toBe(  "<elements>\n<component>title</component>\n</elements>" );
});
