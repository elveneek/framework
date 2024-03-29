<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use \Elveneek\ActiveRecord;

define ('ELVENEEKROOT',substr( __DIR__ ,0,-4));

class ElveneekCore  implements RequestHandlerInterface, MiddlewareInterface {
	public static bool|ElveneekCore $instance ;
    public $dynamicRoutes=[];
    public $defaultValue="";

    public $dynamicRoutesCallbacks=[];
	public $staticRoutesCallbacks=[];

	public $currentRoute = false;
	public $rpc = false;
	public AdminAuth | null $adminAuth = null;

	public \DI\Container $container;

    public $currentIncludedFilename="";


    public $routesRegexp=[];
    public $autoloadDirs=[];
    public $middlewaresCollection=[];
    public $currentMiddleware=-1;
	public $appRootDirectory='';
	public bool|\PDO $db=false;
	public $_this_cache=[];
	public $locals=[];
	public $globals=[];
	function __construct()
	{
		self::$instance = $this;
		ElveneekCore::$instance = $this;

		//Обегаем файлы
		$this->dynamicRoutes['POST']=[];
		$this->dynamicRoutes['GET']=[];

		$this->dynamicRoutesCallbacks['POST']=[];
		$this->dynamicRoutesCallbacks['GET']=[];
		$this->staticRoutesCallbacks['POST']=[];
		$this->staticRoutesCallbacks['GET']=[];

		$this->routesRegexp['POST']="";
		$this->routesRegexp['GET']="";



		/*
		$this->middlewaresCollection=[
			$adminMiddleware,
			$first,
			$second,
			$this //Эта херовина всегда должна быть в самом конце
		];
		 */


		$this->loadAndIncludeProject();
		//выполняем bootstrap

		$this->middlewaresCollection[] = $this;
		$this->prepareAndCompileRoutes();
		//Подключаемся к базе данных

	

		ActiveRecord::$db = ActiveRecord::connect();



	}

	public function addMiddleware($middleware){
		$this->middlewaresCollection[] = $middleware;
	}
	public function loadAndIncludeProject(){
		$app = $this;

		$loadInis=[];
		$compileTemplates=[];

		$includeFiles=[];
		$includeClasses=[];

		$this->container = new \DI\Container();

		//Loading core
		$dir = 'core';
		$absolutePartLength = strlen(realpath (ELVENEEKROOT))+1;
		$directory = new \RecursiveDirectoryIterator(ELVENEEKROOT . '/'. $dir, \FilesystemIterator::FOLLOW_SYMLINKS);
		$filter = new \Elveneek\RecursiveFilterIterator($directory);



		$iterator = new \RecursiveIteratorIterator($filter);
		foreach ($iterator as $info) {
			$fullPath = realpath($info->getPathname());
			$onlyFilename =  $info->getFilename() ;
			$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
			if($ext=="ini"){
				$loadInis[]=$fullPath;
				continue;
			}

			if($ext=="html" || $ext=="ehtml"){
				$compileTemplates[]=$fullPath ;
				continue;
			}

			if($onlyFilename == '.'){
				//Нашли директорию которая начинается с большой буквы

				$relativePath = substr(dirname( $fullPath), $absolutePartLength);
				$this->autoloadDirs[$relativePath] = $relativePath;
			}
			if($ext=="php"){

				if($onlyFilename[0]>='A' && $onlyFilename[0]<='Z'){

					$relativePath = substr(dirname( $fullPath), $absolutePartLength);
					$this->autoloadDirs[$relativePath] = $relativePath;


					$includeClasses[] = $fullPath;
				}else{
					$includeFiles[] = $fullPath;
				}
				continue;
			}


		}
		// loading app
		$dir = 'app';

		$reflector = new \ReflectionClass(get_class($this));
		$dirFull =  dirname($reflector->getFileName());
		 
		if(!defined("ROOT")){
			define("ROOT",  dirname ( $dirFull ));
		}
		$this->appRootDirectory = $dirFull;
		$absolutePartLength = strlen(realpath (  dirname ( $dirFull )  ))+1;
		$directory = new \RecursiveDirectoryIterator( $dirFull , \FilesystemIterator::FOLLOW_SYMLINKS);
		$filter = new \Elveneek\RecursiveFilterIterator($directory);



		$iterator = new \RecursiveIteratorIterator($filter);
		foreach ($iterator as $info) {
			$fullPath = realpath($info->getPathname());
			$onlyFilename =  $info->getFilename() ;
			$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
			if($ext=="ini"){
				$loadInis[]=$fullPath;
				continue;
			}

			if($ext=="html" || $ext=="ehtml"){
				$compileTemplates[]=$fullPath ;
				continue;
			}

			if($onlyFilename == '.'){
				//Нашли директорию которая начинается с большой буквы

				$relativePath = substr(dirname( $fullPath), $absolutePartLength);
				$this->autoloadDirs[$relativePath] = $relativePath;
			}
			if($ext=="php"){

				if($onlyFilename[0]>='A' && $onlyFilename[0]<='Z'){

					$relativePath = substr(dirname( $fullPath), $absolutePartLength);
					$this->autoloadDirs[$relativePath] = $relativePath;


					$includeClasses[] = $fullPath;
				}else{
					$includeFiles[] = $fullPath;
				}
				continue;
			}


		}


	//	print "\loadInis:\n";
	//	var_dump($loadInis);
		//1. Компилируем ini файлы

	//	print "\autoloadDirs:\n";
	//	var_dump($this->autoloadDirs);
		//2. ПРописываем автолоады



		spl_autoload_register(function  ($class_name) {

			$class_name = ltrim($class_name, '\\');
			$fileName  = '';
			$namespace = '';
			if ($lastNsPos = strripos($class_name, '\\')) {
				$namespace = substr($class_name, 0, $lastNsPos);
				$class_name = substr($class_name, $lastNsPos + 1);
				$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
			}
			$fileName_simple = $fileName .  $class_name . '.php';
			$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
			//$fileName = 'vendors'.DIRECTORY_SEPARATOR.$fileName;
			$lover_class_name=strtolower($class_name);

			foreach ($this->autoloadDirs as $path){


				if(is_file(ROOT.'/'. $path . '/'.$fileName  )){
					require_once ROOT.'/'. $path . '/'.$fileName ;
					return;
				}
				if(is_file(ROOT.'/'. $path . '/'.$fileName_simple  )){
					require_once ROOT.'/'. $path . '/'.$fileName_simple ;
					return;
				}
				if(is_file(ELVENEEKROOT.'/'. $path . '/'.$fileName  )){
					require_once ELVENEEKROOT.'/'. $path . '/'.$fileName ;
					return;
				}
				if(is_file(ELVENEEKROOT.'/'. $path . '/'.$fileName_simple  )){
					require_once ELVENEEKROOT.'/'. $path . '/'.$fileName_simple ;
					return;
				}

			}
			if(substr(strtolower($class_name),-10)!='controller' && $class_name[0]>='A' && $class_name[0]<='Z'){
				//Если совсем ничего не найдено, попытка использовать ActiveRecord.
				//eval ("class ".$class_name." extends \Elveneek\ActiveRecord {}");
			}


		},true,true);


		//3. запрашиваем классы
		foreach($includeClasses as $filename){
			require_once ($filename);
		}
		View::setTemplateRoot($dirFull);
		View::addTemplates($compileTemplates);
		

		//4. запрашиваем файлы
		foreach($includeFiles as $filename){
			$this->currentIncludedFilename = $filename;
			require_once ($filename);
		}
		
		$this->adminAuth = new AdminAuth();
		

	}


	//

	function splitRoutesToChains($routes){
		//Идём сверху вниз
		$cleanRoutes=[];
		$rows = [];
		$bracketStarted = [];
		$maximumLength = 0;
		$cnt = count($routes);
		for ($i = 0; $i<= $cnt-1;$i++){
			$routePart = explode('(', $routes[$i])[0];
			$oneRouteClean = preg_split('//u', $routePart, -1, PREG_SPLIT_NO_EMPTY);
			if($maximumLength < count($oneRouteClean)){
				$maximumLength = count($oneRouteClean);
			}
			$rows[] = $oneRouteClean;
			$cleanRoutes[] = $routePart;
			$bracketStarted[]=false;

		}

		$groups=[]; //Группы роутов, вложенных по уровням
		$groups[0]=[];
		if(!empty($rows[0])){
			foreach($rows[0] as $n=>$symbol){
				$groups[$n][0]=0;
			}
		}
		$current_group=0; //Первая группа, она есть всегда

		//последовательно перебираемся по столбцам
		$current_column = 0; //Начиная с первого
		$found_one_match = true;
		$rowsCount = count($rows);
		$rows_in_current_group = 0;
		while($found_one_match){
			$found_one_match = false;
			$rows_in_current_group=0;
			for ($i = 1; $i<= $rowsCount-1;$i++){ //пропускаем первую строку
				//Сравниваем символы текущей строки и предыдущей
				$need_group = false;
				if(count($rows[$i]) <= $current_column){
					//Текущая строка слишком короткая
					$need_group = true;
				}elseif(count($rows[$i-1]) <= $current_column){
					//Предыдущая строка слишком короткая
					$need_group = true;

				}elseif($rows[$i][$current_column] === $rows[$i-1][$current_column]){
					//Строки достаточно длинные, чтобы продолжат их сравнивать
					$rows_in_current_group++;
					$found_one_match = true;
				}else{
					//Следующий символ не равен предыдущему. Создаем новую группу
				//	print "Create Group $current_group row: $i	column:	$current_column \n";
					$need_group = true;
					$found_one_match = true;
				}
				if($need_group){
					$current_group++;

						$groups[$current_column][$i]=$i;

					$rows_in_current_group=0;
				//	$found_one_match = true;
				}
			}
			$current_column++;
		}
		//ЧИстим группы

		$lastgroup=false;



		$maxcolumn = 0; //Максимально большой встречаемый номер строки
		foreach($groups as $column=>$els){
			if($maxcolumn < $column){
				$maxcolumn = $column;
			}
		}
		//Заполняем пустоты в линиях. Они могут возникнуть, если в середине строки есть совпадение по символу у соседних строка
		$seenSomethingInRow=[];
		$linesCount = count($rows);
		foreach($groups as $column=>$els){
			foreach($els as $line){
				$seenSomethingInRow[$line]=true;
			}
			//Сейчас мы находимся на колонке X
			for ($i=0;$i<=$linesCount-1;$i++){
				if(isset ($seenSomethingInRow[$i])){
					$groups[$column][$i]=$i;
				}
			}
		}
		foreach($groups as $column=>$els){
			ksort($groups[$column]);
		}

		//делаем группы массивов, где группа может включать одну или больше дочерних
		$groupParents=[];
		$groupParents[""] = [];
		for($column=0;$column<=$maxcolumn;$column++){
			$els = $groups[$column];
			foreach ($els as $row){
				if($column==0){
					$parent = "";
				}else{
					$parent = mb_substr($routes[$row],0,$column );
				}
				//Дальше у родителя будет идти слеш  abc
				if($column >= mb_strlen($cleanRoutes[$row]) ){
					$parent = $cleanRoutes[$row];
				}

				if($column === $maxcolumn){
					//Всё, дальше пустота, сворачиваем машину
					$name = $routes[$row]  ;
				}elseif($column == mb_strlen($cleanRoutes[$row])-1){
					//Идёт символ скобок!
					$name = $cleanRoutes[$row]  ;
				}elseif($column >= mb_strlen($cleanRoutes[$row])-1){
					$name = $routes[$row]   ; //в теории должен заходит сюдась
				}else{
						if($column === $maxcolumn){
							//Всё, дальше пустота, сворачиваем машину
							$name = $routes[$row];
						}else{
							$name = mb_substr($routes[$row],0,$column+1);
						}
				}
				$groupParents[$parent][]=$name;
			}
		}


		foreach ($groupParents as $groupname => $elements){
			$groupParents[$groupname] = array_unique ($elements);
		}

		$deletedOnce = true;
		while($deletedOnce){
			$deletedOnce = false;
			foreach ($groupParents as $groupname => $elements){
				foreach ($elements as $key=>$element){
					if(isset($groupParents[$element]) && count($groupParents[$element])==1){
						$groupParents[$groupname][$key] = $groupParents[$element][0];
						unset($groupParents[$element]);
						$deletedOnce = true;
						break 2;
					}
				}

			}
		}
		//Чистим массив, делая его короче
		foreach($groupParents as $parent=>$values){
			if($parent==""){
				continue;
			}
			$parentLen = mb_strlen($parent);
			foreach ($values as $key=>$value){
			//	$groupParents[$parent][$key] = mb_substr($value,$parentlen);
			}
		}


		return  $this->createRegexpStringFromGroups($groupParents, "");


	}
	//Router part
	//https://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html

	function createRegexpStringFromGroups($groupParents, $current, $lastparent='' ){


	//	dd($groupParents);
		$str = "";
		if(count($groupParents[$current])>1){
			if($current !=''){
				$currentCleaned = mb_substr($current,mb_strlen($lastparent));

				$str.='|'.$currentCleaned.'(?';
			}
			foreach($groupParents[$current] as $subelement){
				if( isset($groupParents[$subelement])){
					//Есть дочерние элементы
					$str.=$this->createRegexpStringFromGroups($groupParents, $subelement, $current);
				}else{
					$currentCleaned = mb_substr($subelement,mb_strlen($current));
					$str.='|'.$currentCleaned;
				}

			}
			if($current !=''){
				$str.=')';
			}
		}else{

			if(!empty($groupParents[$current])) { //Это условие добавлено, потому что иначе $groupParents[$current][0] - null
				$subelement = $groupParents[$current][0];
				$currentCleaned = mb_substr($subelement,mb_strlen($lastparent));

				$str.='|'.$currentCleaned;

			}
			//$str.="\n|$subelement";
		}
		return $str;

	}


	public static function get($url, $callback){
		$callback = new \Elveneek\RouteCallback($callback, self::$instance->currentIncludedFilename, $url);
		//Определяем, роут статический или динамический
		if(strpos($url,':') === false && strpos($url,'(') === false) {
            self::$instance->staticRoutesCallbacks['GET'][$url]=$callback;
		}else{
            self::$instance->dynamicRoutes['GET'][]=$url;
            self::$instance->dynamicRoutesCallbacks['GET'][]=$callback;
		}
		return $callback;
	}

	function post($url, $callback){
		$callback = new \Elveneek\RouteCallback($callback, self::$instance->currentIncludedFilename, $url);
		if(strpos($url,':') === false && strpos($url,'(') === false) {
            self::$instance->staticRoutesCallbacks['POST'][$url]=$callback;
		}else{
            self::$instance->dynamicRoutes['POST'][]=$url;
            self::$instance->dynamicRoutesCallbacks['POST'][]=$callback;
		}
		return $callback;
	}

	function route($url, $callback){
		$callback = new \Elveneek\RouteCallback($callback, self::$instance->currentIncludedFilename, $url);
		if(strpos($url,':') === false && strpos($url,'(') === false) {
            self::$instance->staticRoutesCallbacks['GET'][$url]=$callback;
            self::$instance->staticRoutesCallbacks['POST'][$url]=$callback;
		}else{
            self::$instance->dynamicRoutes['GET'][]=$url;
            self::$instance->dynamicRoutesCallbacks['GET'][]=$callback;
            self::$instance->dynamicRoutes['POST'][]=$url;
            self::$instance->dynamicRoutesCallbacks['POST'][]=$callback;
		}
		return $callback;
	}

	function prepareAndCompileRoutes(){

		foreach(['GET', 'POST'] as $method){
			$routes = [];

			foreach($this->dynamicRoutes[$method] as $key=>$url){
				//Преобразуем строку вида "/catalog/:url" в "catalog/(.*?) (*M:23)"
				$routedRegexp = substr($url,1); //Убираем ведущий слеш.
				/*
				:url+ ==> (.+?)
				:url* ==> (.*?)
				:url  ==> ([^\/]+?)
				*/
				$routedRegexp = preg_replace(array('#\:[a-z_][a-zA-Z0-9_]*\+#','#\:[a-z_][a-zA-Z0-9_]*\*#','#\:[a-z_][a-zA-Z0-9_]*#'),array('(.+?)','(.*?)','([^\/]+?)'),$routedRegexp);
				$routes[] = $routedRegexp.' '.'(*MARK:'.$key.')';

			}
			rsort($routes);
			$resultRegExp = $this->splitRoutesToChains($routes);

			$routeRegexp='#^/(?'.$resultRegExp.')$#x';
			$this->routesRegexp[$method] = $routeRegexp;
		}
	}

	function getRouteForRequest($request, $method){
		//Сначала ищем статические функции

		//Потом ищем динамические
		//
	}

	/**
	 * Выполняет цепочку Middlewares, пока может, передавая самого себя в качестве обработчика.
	 *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
	public function handle(ServerRequestInterface $request) : ResponseInterface
	{
		//Эта херовина всегда должна возвращать response.
		//Пошёл цикл!
		//Берем текущую активную мидлверину
		$this->currentMiddleware++;
		try {
			//Сначала это первая, потом вторая, потом в самом конце  текущий класс
			return $this->middlewaresCollection[$this->currentMiddleware]->process($request, $this);
		}catch (ResponseException $e) {
			return $e->getResponse();
		}
	}

	/**
     * ?
	 *
	 * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

		//FIXME: Если в качестве хендлера каким то образом прислали мидлверю, добавляем её в очередь (ЗАЧЕМ???)
		//Тут наступила мякотка, начинаем наконец работать уже
		//При этом $handler игнорируем вообще, иначе будет рекурсия

		$response = new \Nyholm\Psr7\Response(200, ['Content-Type'=> 'text/html; charset=utf-8']); //TODO: тут нужна фабрика.
		//Так как пошла мякотка, нужно организовать поведение по умолчанию, типа например контент тайп text/html
		ob_start();


		$uri = $request->getUri()->getPath();
		$requestUri=$uri;


		$method = $request->getMethod();
		$regexpResult =[];
		 //Ищем статический метод
		if(isset($this->staticRoutesCallbacks[$method][$requestUri])){
			$callback = $this->staticRoutesCallbacks[$method][$requestUri];
			$this->currentRoute = $callback;

			$callParams=[$request, $response];

			$refFunction = new ReflectionFunction($callback->callback);
			$parameters = $refFunction->getParameters();
			if(count($parameters)<=2){
				$forRun = $callback->callback;
				$result = $forRun($request, $response); //todo: ob_start
			}else{
				foreach($parameters as $parameter){

					if ($parameter->isOptional()) {
						continue;
					}
					$type = $parameter->getType();
					$class = null !== $type && !$type->isBuiltin() ? $type->getName() : null;
					if(is_null($class)){
						continue;
					}
					$callParams[]=$this->container->get($class);
				}
				$result = call_user_func_array($callback->callback,  array_values($callParams)); //todo: ob_start
			}
		}elseif (preg_match($this->routesRegexp[$method], $requestUri, $regexpResult) &&
			isset($this->dynamicRoutesCallbacks['GET'][$regexpResult['MARK']])){
				$callback = $this->dynamicRoutesCallbacks['GET'][$regexpResult['MARK']];
				$this->currentRoute = $callback;
				unset($regexpResult['MARK']);
				unset($regexpResult[0]);
				$regexpResult[]=$request;
				$regexpResult[]=$response;

				$refFunction = new ReflectionFunction($callback->callback);
				$parameters = $refFunction->getParameters();
				foreach($parameters as $parameter){

					if ($parameter->isOptional()) {
						continue;
					}
					$type = $parameter->getType();
					$class = null !== $type && !$type->isBuiltin() ? $type->getName() : null;
					if(is_null($class)){
						continue;
					}
					$regexpResult[]=$this->container->get($class);
				}

				$result = call_user_func_array($callback->callback,  array_values($regexpResult)); //todo: ob_start
		}else{
			$callback = function(){ print 'DEDAULT'; };
			$result = $callback($request, $response); //todo: ob_start
			$this->currentRoute = false;
		}

		//Потом отдаём пустую заготовку про 404

		//Здесь возможно несколько вариантов:
		/*
		$result - объект класса $response;
		$result - NULL
		$result - строка. В этом случае мы грустим. В этом случае программист не может поменять результат работы (поставить заголовок).
		Выдаём ошибку
		*/

		$obResult = ob_get_contents();
		ob_end_clean();
		if (!isset($result)) { //NULL
			$response->getBody()->write($obResult);
		} elseif ($result instanceof ResponseInterface) {  //RESPONSE
			$response = $result;
			$response->getBody()->write($obResult);
		}else{
			$response->getBody()->write($result);
		}

		return $response;
    }

	function dispachAll($request){

		$this->locals = $this->globals; //Очищается массив локальных переменных d()->[имя переменной], которые остались с предыдущего запроса
		$this->adminAuth->clear(); //Сбрасывается состояние объекта авторизации администратора, вместо того чтобы пересоздавать объект "с нуля"
		$this->currentMiddleware=-1;
		//Эта херовина должна вернуть $response;
		return $this->handle($request);
	}





	//locals && globals
	function __set($name,$value)
	{
		$this->locals[$name]=$value;
	}

	function &__get($name)
	{


		if(isset($this->locals[$name])) {
			return $this->locals[$name];
		}

		//$fistrsim =  ord(substr($name,0,1));
		//if($fistrsim>64 && $fistrsim<91){
		if(preg_match('/^[A-Z].+/', $name)) { //FIXME: переделать на кваратные или фигурные скобочки
			$result = new $name();
			return $result;
		}
		$default = "";
		return $default;
	}


	public function __isset($name) {
		return isset($this->locals[$name]);
	}

	public function __unset($name) {
		unset($this->locals[$name]);
	}


}



function d()
{
	return ElveneekCore::$instance;
}
