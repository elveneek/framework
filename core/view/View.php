<?php


class View {
	public static $compileTemplates = [];
	public static $routesPaths = [];
	public static $pathsCache = [];
	
	const LAYOUT_TEMPLATE = '|#LAYOUT\s+([\\a-zA-Z0-9_-]+\.e?html)|';
	const LAYOUT_TEMPLATE_WITH_NEWLINE = '|#LAYOUT\s+([\\a-zA-Z0-9_-]+\.e?html)\r?[\n]?|';
	
	public static $template_patterns = [];
	public static $template_replacements = [];
	
	
	public static $compileTemplatesReverted = [];
	public static $compiledTemplatesRegistry = [];
	
	public static $currentTemplatesRoot = "";
	
	public static $registeredTempates = [];

	public static function setTemplateRoot($currentTemplatesRoot){
		if(DIRECTORY_SEPARATOR !== '/'){
			$currentTemplatesRoot = str_replace(DIRECTORY_SEPARATOR, '/', $currentTemplatesRoot);
		}
		static::$currentTemplatesRoot = $currentTemplatesRoot;
	}
	/** 
	 * addTemplate регистрирует шаблон, доступный по абсолютному пути 
	*/
	public static function addTemplate($templateFullPath){
		if(DIRECTORY_SEPARATOR !== '/'){
			$templateFullPath = str_replace(DIRECTORY_SEPARATOR, '/', $templateFullPath);
		}
		static::$registeredTempates[$templateFullPath]=true;
		static::$compileTemplates[count(static::$registeredTempates)-1] = $templateFullPath;
	}

	public static function  getAbsolutePath($path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

	public static function addTemplates($templates){

		foreach($templates as $template){
			static::addTemplate($template);
		}
		return;


		//TODELETE
		static::$compileTemplates = $templates;
		static::$compileTemplatesReverted = [];
		if(DIRECTORY_SEPARATOR !== '/'){
			foreach (static::$compileTemplates as $key=>$value){
				$fixed=str_replace( DIRECTORY_SEPARATOR, '/', $value);
				static::$compileTemplates[$key] = $fixed;
				static::$compileTemplatesReverted[$fixed] = true;
			} 
		}else{
			foreach (static::$compileTemplates as $key=>$value){
				static::$compileTemplatesReverted[$value] = true;
			}
		}
		
		
		

	}
	public static function getTemplateByFile($file, string $relativePath=null){
		if(DIRECTORY_SEPARATOR !== '/'){
			$file=str_replace( DIRECTORY_SEPARATOR, '/', $file);
		}
				 
		//Сначала пытаемся найти сам файл напрямую, возможно указан прямой путь на диске
		if(isset(static::$registeredTempates[$file])){
			return $file;
		}
		
		//Если первыq символы - /, то ищем в корне текущего приложения
		if($file[0]==='/'){
			if(isset(static::$registeredTempates[static::$currentTemplatesRoot . $file])){
				return static::$currentTemplatesRoot . $file;
			}
		}else{
			if($relativePath === null){
				$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
				$callerDir = dirname($trace[1]['file']);
				$fullPath = View::getAbsolutePath($callerDir. '/' . $file);
			}else{
				$fullPath = View::getAbsolutePath($relativePath. '/' . $file);
			}
			
			if(DIRECTORY_SEPARATOR !== '/'){
				$fullPath=str_replace( DIRECTORY_SEPARATOR, '/', $fullPath);
			}

			if(isset(static::$registeredTempates[$fullPath])){
				return $fullPath;
			}

			throw new Exception("Не удалось найти файл шаблона (" .  $fullPath . " в " . $relativePath . ")");

		}

		throw new Exception("Не удалось найти файл шаблона " . $file);
		return false;
	}
	
	//TODELETE
	public static function DELETEDgetTemplateByFileAndPath($file, $path=""){
		
		//Если уже находили путь - выводим
		if(isset(static::$pathsCache[$path.'#'.$file] )){
			return static::$pathsCache[$path.'#'.$file];
		}
		
		//Если первый символ - Слеш, то ищем от корня последовательно в папках ['core', 'app']
		if($file[0]==='/'){
			$niceFile = substr($file, 1);
			foreach (['', 'app/', 'core/']	 as $rootPath){
				if(isset(static::$compileTemplatesReverted[$rootPath . $niceFile])){
					static::$pathsCache[$path.'#'.$file] = $rootPath . $niceFile;
					return $rootPath . $niceFile;
				}
			}
			
			//Если первый символ "/" - никакой поисковой магии
			static::$pathsCache[$path.'#'.$file] = false;
			return false; 
		}
		
		

		//Формируем $nicePath, который содержит разделители в стиле Linux
		if(DIRECTORY_SEPARATOR !== '/'){
			$nicePath = str_replace( DIRECTORY_SEPARATOR, '/', $path);	
		}else{
			$nicePath = $path;
		}


		//Если первые символы - ./, то ищем строго в текущей папке. Остальные (../, ../.. и так далее - не поддерживаются)
		if($file[0]==='.' && $file[1]==='/'){
			$niceFile = substr($file, 2);
			
			//Ищем, есть ли в текущем пути нужный HTML - сразу
			if(isset(static::$compileTemplatesReverted[$nicePath . '/' . $niceFile])){
				//Нашли прямо в текущей папке
				static::$pathsCache[$path.'#'.$file] = $nicePath . '/' . $niceFile;
				return $nicePath . '/' . $niceFile;
			}
			
			//Если первый символ "/" - никакой поисковой магии
			static::$pathsCache[$path.'#'.$file] = false;
			return false; 
		}
		
		
		
		//Ищем, есть ли в текущем пути нужный HTML - сразу
		if(isset(static::$compileTemplatesReverted[$nicePath . '/' . $file])){
			//Нашли прямо в текущей папке
			static::$pathsCache[$path.'#'.$file] = $nicePath . '/' . $file;
			return $nicePath . '/' . $file;
		}
		
		//Ищем рекурсивно вверх по директории
		$pathParts  = explode('/', $nicePath); // [app, news, blabla]
		$countParts = count($pathParts);
		while($countParts > 1){
			$countParts--;
			$newNicePath = implode('/', array_slice($pathParts, 0, $countParts));
			//Проверяем уровнем вверх
			if(isset(static::$compileTemplatesReverted[$newNicePath . '/' . $file])){
				static::$pathsCache[$path.'#'.$file] = $newNicePath . '/' . $file;
				return $newNicePath . '/' . $file;
			}
		}
		
		//Ничего не нашлось - возвращаем false
		static::$pathsCache[$path.'#'.$file] = false;
		return false;
	}
	
	public static function render($template, $params=[], string  $relativePath = null){
		$result_template = View::getTemplateByFile($template, $relativePath);
		if($template===false){
			throw new Exception("Имя шаблона не может быть пустым");
		}
		return View::compileAndRunTemplate($result_template, true, false, $params);
	}

    public static function partial($template, $params=[], string  $relativePath = null){
		$result_template = View::getTemplateByFile($template, $relativePath);
		if($template===false){
			throw new Exception("Имя шаблона не может быть пустым");
		}
		return View::compileAndRunTemplate($result_template, false, false, $params);
	}

		
	static function renderEHTML($template, $method, $params=[], string  $relativePath = null){
		$result_template = View::getTemplateByFile($template, $relativePath);
		return View::compileAndRunTemplate($result_template, false, $method, $params);
	}
	

	//Принимает имя файла (main.html или wrapper.html) и возвращает массив из двух элементов: первая и вторая половинка
	public static function getLayoutRecursive($template, $relativePath=null){
		//Первым делом определяем, какой именно файл будет взят в качестве истояника
		$result_template = View::getTemplateByFile($template, relativePath: $relativePath);
		 
		
		$result_template_contents =  file_get_contents($result_template);
		//Проверяем, включает ли $result_template упоминание директивы компилятора #LAYOUT
		$matches=[];
		$relativePath=dirname($result_template);
		preg_match_all(static::LAYOUT_TEMPLATE,$result_template_contents, $matches);
		if(isset($matches[1][0])){
			$layout = $matches[1][0];
			//На данный момент мы знаем, что к файлу надо прицепить начало и конец другого файла, который описан в layout;
			$parts = static::getLayoutRecursive($layout, $relativePath);
			//типа получили
			$begin = $parts[0];
			$end = $parts[1];
		}else{
			$begin = '';
			$end = '';
		}
		
		$parts = explode('#CONTENT#', $result_template_contents,2);
		if(count($parts)==1){
			$parts[1] = '';
		}
		$parts[0] = $begin . $parts[0];
		$parts[1] =  $parts[1] . $end;
		return $parts;
	}
	public static function compileAndRunTemplate($template, $isFull, $subPart = false, $params=[]){
		//Далее разбираем, какой именно $result_template мы ищем в кеше:
		
		if($template==''){
			return 'ошибка';

		}
		
		if($subPart!==false){
			$result_template = 'EHTML#'.$template.'#'.$subPart;
		}else{
			if(iam()){
				if($isFull){
					$result_template = 'ADMIN_FULL#'.$template;
				}else{
					$result_template = 'ADMIN_PART#'.$template;
				}
			}else{
				if($isFull){
					$result_template = 'FULL#'.$template;
				}else{
					$result_template = 'PART#'.$template;
				}
			}
		}
		
		
		
		//Во-первых, определяем номер функции, которую выделили под этот шаблон
		if(isset(View::$compiledTemplatesRegistry[$result_template])){
			//Хех, нашлось.
			ob_start();
			$result =  call_user_func('compiled_template_'. View::$compiledTemplatesRegistry[$result_template], $params);
			$_end = ob_get_contents();
			ob_end_clean();
			if (!is_null($result)) {
				$_end = $result;
			}
			return $_end;
			
		}
		$current_number = count(View::$compiledTemplatesRegistry);
		View::$compiledTemplatesRegistry[$result_template] = $current_number;
		//$current_number - номер функции, которая содержит в себе скомпилированный шаблон
	 
	/*	
		if(substr($template,0,4)=='core'){
			$root = ELVENEEKROOT;
		}else{
			$root = ROOT;
		}
	*/	
		$currentTemplateDirectory = dirname($template);
		$templateString = file_get_contents($template);
		if ($subPart!== false){
			$matches = [];
			$matchedSubTemplate="";
			if(preg_match('#[a-zA-Z][a-zA-Z0-9_]*#',$subPart)){
					
				preg_match_all('#^\['.$subPart.'.*?](?<result>.*?)(^\[.*?\]|\Z)#sm',$templateString,$matches);
				if(isset($matches["result"][0])){
					$matchedSubTemplate=$matches["result"][0];
				}
			}
			$templateString = $matchedSubTemplate;
		}
		$ehtml = ($subPart!==false);
		//компилим в строку файл  $template
		if($isFull){
			$matches=[];
			preg_match_all(static::LAYOUT_TEMPLATE, $templateString, $matches);
			if(isset($matches[1][0])){
				$currentLayout = $matches[1][0];
			}else{
				$currentLayout = '/main.html';
			}
			//Предварительно компилируем всё, что находится выше, циклично.
			$parts = static::getLayoutRecursive($currentLayout, $currentTemplateDirectory);
			$templateString = $parts[0] . $templateString. $parts[1];
		}
		

		ob_start(); //Подавление стандартного вывода ошибок Parse Error
		$result=eval('function  compiled_template_'.$current_number.'($viewTemplateParams=[]){ extract($viewTemplateParams); $d=ElveneekCore::$instance; ?'.'>'. View::compileTemplateStringtoPHPString($templateString, $ehtml, $currentTemplateDirectory) .'<'.'?php ;} ');
		ob_end_clean();
	 	
		//запускаем функцию compiled_template_{$current_number}
		
		ob_start();
		$params['currentTemplateDirectory'] = $currentTemplateDirectory;
		$result =  call_user_func('compiled_template_'. $current_number, $params);
		$_end = ob_get_contents();
		ob_end_clean();
		if (!is_null($result)) {
			$_end = $result;
		}
		return $_end;
	}
	
	public static function compile_advanced_chain($arr){
		
		$str='';
		foreach($arr as $key=>$value){
			if($key==0){
				$str = '$_c_tmp=$d->'.$value.'';
			}else{
				$str = '$_c_tmp=(is_object('.$str.')?$_c_tmp->'.$value.':$_c_tmp["'.$value.'"])';
			}
			
		}
		return $str;
		
	}
	 
	public static function compileFileToPHPString($template, $ehtml = false, string $currentFileDirectory = null){
		return static::compileTemplateStringtoPHPString(file_get_contents($template), $ehtml,  $currentFileDirectory  );
	}

	public static function compileTemplateStringtoPHPString($_str, $ehtml = false, string $currentFileDirectory = null){
		

		
		$_str   = preg_replace(View::$template_patterns,View::$template_replacements,str_replace(array("\r\n","\r"),array("\n","\n"),$_str));	
		$_str = preg_replace('#{\.(.*?)}#','{this.$1}',$_str);
	 	$_str = preg_replace_callback( "#\{((?:[a-zA-Z_]+[a-zA-Z0-9_]*?\.)*[a-zA-Z_]+[a-z0-9_]*?)}#mui", function($matches){
			// d()->matches = ($matches); //TODO: checkandclean
			$string = $matches[1]; //user.comments.title
			$substrings = explode('.',$string);
			
			$result = '<?php print '.View::compile_advanced_chain($substrings). '; ?>';
			return $result;
		}, $_str);
		 
		if($ehtml){
			$_str = preg_replace_callback( "#^([a-zA-Z][a-zA-Z_]+\s.*?)$#miu", function($matches){
				return '<?=$generator->renderComponent(' . static::EHTMLArrayClean($matches[0]).'); ?> ';
			}, $_str);
		}
		 
		$_str = preg_replace_callback( "#\{((?:[a-z0-9_]+\.)*[a-z0-9_]+)((?:\|[a-z0-9_]+)+)}#mui", function($matches){
			//d()->matches = ($matches);
			$string = $matches[1]; //user.comments.title
 
			$substrings = explode('.',$string);
			
			
			$result = '  '.View::compile_advanced_chain($substrings);
	
			
			$functions = $matches[2]; //|h|title|htmlspecialchars
			$substrings = (explode('|',$functions));
			array_shift($substrings);
			$result = '<?php print  ' . array_reduce($substrings, function($all, $item){
				return '$d->'.$item.'('. $all .')';
			}, $result) .  ' ; ?>'; 
			
			return $result;
		}, $_str);
	 
		
		
		 
		$_str = preg_replace_callback( "#\{((?:[a-z0-9_]+\.)*[a-z0-9_]+)((?:\|.*?)+)}#mui", function($matches){
			//d()->matches = ($matches);
			$string = $matches[1]; //user.comments.title
 
			$substrings = explode('.',$string);
			
			
			$result = '  '.View::compile_advanced_chain($substrings);
			

			$functions = $matches[2]; //|h|title|htmlspecialchars
			$substrings = (explode('|',$functions));
			array_shift($substrings);
			$result = '<?php print  ' . array_reduce($substrings, function($all, $item){
			
				preg_match('#([a-z0-9_]+)(\s+.*)?#',$item,$m);
				if(is_null($m[2])){
					return '$d->'.$m[1].'('. $all .')';
				}else{
				
					$attr_params = $m[2]; //'50', '100' '200' user="10"   ===>   '50', '100', '200', 'user'=>"10"
					
					$attr_params = preg_replace('#\s+=\s+\\\'(.*?)\\\'#',' => \'$1\' ',$attr_params);
					$attr_params = preg_replace('#\s+=\s+\\"(.*?)\\"#',' => "$1" ',$attr_params);
					$attr_params = preg_replace('#([\s\$a-zA-Z0-9\\"\\\']+)=\s([\s\$a-zA-Z0-9\\"\\\']+)#','$1=>$2',$attr_params);
					$attr_params = preg_replace('#\s+([a-z0-9_]+?)\s*=>#',' \'$1\' => ',$attr_params);
					return '$d->'.$m[1].'(array('. $all .', '. $attr_params .'))';
				}
				
			}, $result) .  ' ; ?>'; 
			
			return $result;
		}, $_str);
		 
 

		$_str = preg_replace_callback( "/{{([#a-zA-Z0-9_]+)\s+(.*?)\}}/mui", function($matches){
			//file_put_contents('1.txt',json_encode($matches));
			$attr_params = ' '.$matches[2];
			$attr_params = preg_replace('#\s+=\s+\\\'(.*?)\\\'#',' => \'$1\' ',$attr_params);
			$attr_params = preg_replace('#\s+=\s+\\"(.*?)\\"#',' => "$1" ',$attr_params);
			$attr_params = preg_replace('#([\s\$a-zA-Z0-9\\"\\\']+)=\s([\s\$a-zA-Z0-9\\"\\\']+)#','$1=>$2',$attr_params);
			$attr_params = preg_replace('#\s+([a-z0-9_]+?)\s*=>#',' \'$1\' => ',$attr_params);
			return '<?php print $d->'.$matches[1].'( array(array('.$attr_params.')));?>';
		
		}, $_str);
		
		//Итоговые замены
		
		#/routes/1.html#
		$_str = preg_replace_callback( "|#INCLUDE ([A-Za-z0-9_\-\/]+\.html)\r?[\n]?|mui", function($matches)use($currentFileDirectory){
			//d()->matches = ($matches);
			$template = $matches[1]; //user.comments.title
			
			$result_template = View::compileFileToPHPString(View::getTemplateByFile($template, $currentFileDirectory), $currentFileDirectory);
			
			
			return  $result_template ; 
		}, $_str);
		
		return $_str;
	}

	
	
	static function EHTMLArrayClean($str){
		
		$resultStr="";
		$strlen=mb_strlen($str);
		$quoteMarkBegin=false;
		$wordBegin=false;
		$phpCodeStarted=false;
		$firstWordEnds = false;
		for($i=0;$i<=$strlen-1;$i++){
			$symbol = mb_substr($str,$i,1);
			if($symbol=='"'){
				if($wordBegin && $quoteMarkBegin){
					//Слово продолжается в кавычках      "word"|
					$resultStr.=$symbol .',';
					$wordBegin=false;
					$quoteMarkBegin=false;
				}elseif($wordBegin && !$quoteMarkBegin){
					//Слово продолжается, кавычки не было      "word" weea"|
					//ОШИБКА!!!
				}elseif(!$wordBegin && $quoteMarkBegin){
					//Слова не было, кавычка была      "word" ""
					if($strlen-1 != $i){
						$resultStr.=$symbol .',';
					}else{
						$resultStr.=$symbol;	
					}
					$quoteMarkBegin=false;
				}elseif(!$wordBegin && !$quoteMarkBegin){
					//Слова не было, кавычки не было      "word" wew "
					$resultStr.=$symbol;
					$quoteMarkBegin=true;
				}
			}elseif($symbol==' ' || $symbol=="\t"){
				if($wordBegin && $quoteMarkBegin){
					//Слово продолжается в кавычках      "word |
					$resultStr.=$symbol;
				}elseif($wordBegin && !$quoteMarkBegin){
					//Слово продолжается, кавычки не было      "word" weea |
					
					$wordBegin=false;
					if($firstWordEnds==false){
						$resultStr.='",'.$symbol.'[';
					}else{
						$resultStr.='",'.$symbol;	
					}
					$firstWordEnds=true;
				}elseif(!$wordBegin && $quoteMarkBegin){
					//Слова не было, кавычка была      "word" " 
					$resultStr.=$symbol;
					$wordBegin=true;
				}elseif(!$wordBegin && !$quoteMarkBegin){
					//Слова не было, кавычки не было      "word" wew 
					$resultStr.=$symbol;
				}
			}elseif(($symbol>='0' && $symbol<='9') || ($symbol>='A' && $symbol<='Z') || ($symbol>='a' && $symbol<='z') || ($symbol>='а' && $symbol<='я')|| ($symbol>='А' && $symbol<='Я')|| ($symbol=='ё' || $symbol=='Ё' || $symbol=='_')){
				if($wordBegin && $quoteMarkBegin){
					//Слово продолжается в кавычках      "word|
					$resultStr.=$symbol ;
				}elseif($wordBegin && !$quoteMarkBegin){
					//Слово продолжается, кавычки не было      "word" weea|
					$resultStr.=$symbol;
				}elseif(!$wordBegin && $quoteMarkBegin){
					//Слова не было, кавычка была      "word" "ф|
					$resultStr.=$symbol;
				}elseif(!$wordBegin && !$quoteMarkBegin){
					//Слова не было, кавычки не было      "word" wew a|
					$resultStr.='"'.$symbol;
					$wordBegin=true;
				}
			}else{
				
				if($quoteMarkBegin){
					//Слово продолжается в кавычках      "word"|
					$resultStr.=$symbol;
				}else {
					if($wordBegin && !$quoteMarkBegin){
						//Слово продолжается, кавычки не было      "word" weea|
						
						//ТУТ АХТУНГ, РАНЕЕ ЗРЯ ОТКРЫЛИ КАВЫЧКУ
						
						//Ищем последнюю кавычку
						if(mb_substr($str,$i,1)!=','){
							$strpos = mb_strrpos($resultStr,'"');
							$resultStr = mb_substr($resultStr,0, $strpos) .mb_substr($resultStr, $strpos+1) ;
						}else{
							$resultStr.='"';
						}
						//$resultStr.='"';
					}
					if($firstWordEnds==false && $symbol==","){
						 
						$resultStr = mb_substr($resultStr,0,-1);
						$resultStr .= '", [' . mb_substr($str,$i+2);	
						 
					}else{
						if(mb_substr($resultStr,-1)==','){
							$resultStr = mb_substr($resultStr,0,-1);
							$resultStr .= mb_substr($str,$i);	
						}else{
							$resultStr .= mb_substr($str,$i);	
						}						
					}
					return $resultStr.']';
				}
			}
		}
		
		//КОНЕЦ
		if($wordBegin && $quoteMarkBegin){
			//Слово продолжается в кавычках      "word |
			//$resultStr.='"';
		}elseif($wordBegin && !$quoteMarkBegin){
			//Слово продолжается, кавычки не было      "word" weea |
			$wordBegin=false;
			if($firstWordEnds==false){
				$resultStr.='", [';
			}else{
				$resultStr.='"';	
			}
			$firstWordEnds=true;
		}elseif(!$wordBegin && $quoteMarkBegin){
			//Слова не было, кавычка была      "word" " 
			//Ошибка
		}elseif(!$wordBegin && !$quoteMarkBegin){
			//Слова не было, кавычки не было      "word" wew 
			//обычная ситуация
		}
		return $resultStr.']';
	}
	
}