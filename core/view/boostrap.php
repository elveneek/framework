<?php

		
		
		//Объявление template patterns
		
		
			// Массив для шаблонизатора
		
		//Первым делом убираем директивы компилятора
		View::$template_patterns[]=View::LAYOUT_TEMPLATE_WITH_NEWLINE ;
		View::$template_replacements[]='';
		 
		 
		// <foreach users as user>
		View::$template_patterns[]=	'/<foreach\s+(.*?)\s+as\s+([a-zA-Z0-9_]+)>/';
		View::$template_replacements[]='<'.'?php $tmparr= $d->$1;
		if(!isset($d->locals[\'this\'])){
			$d->locals[\'this\']=array();
		}
		array_push($d->_this_cache,$d->locals[\'this\']);
if(is_string($tmparr)) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$d->key = $key;
		$d->locals["override"]="";
		if(is_object($subval)){
			 $d->locals[\'$2\']=$subval;
			 $d->locals[\'this\']=$subval;
			 $d->locals[\'override\']=$subval->override;
		}else{
		$d->locals[\'this\']=array();
		foreach($subval as $subkey=>$subvalue) {
		$d->locals[\'$2\'][$subkey]=$subvalue;
		$d->locals[\'this\'][$subkey]=$subvalue;
		}   }
		if ($d->locals["override"]!="") { print $d->{$d->locals["override"]}(); } else { ?'.'>';

 
		View::$template_patterns[]='/<foreach\s+(.*)>/';
		View::$template_replacements[]='<'.'?php $tmparr= $d->$1;

		if(!isset($d->locals[\'this\'])){
			$d->locals[\'this\']=array();
		}
		array_push($d->_this_cache,$d->locals[\'this\']);
if(is_string($tmparr)) $tmparr=array($tmparr);
foreach($tmparr as $key=>$subval)
	if(is_string($subval)) print $subval;else {
		$d->key = $key;
		$d->locals["override"]="";
		if(is_object($subval)){
			 $d->locals[\'this\']=$subval;
			 $d->locals[\'override\']=$subval->override;
		}else{
		$d->locals[\'this\']=array();
		foreach($subval as $subkey=>$subvalue) {
		$d->locals[\'this\'][$subkey]=$subvalue;
		}   }
		if ($d->locals["override"]!="") { print $d->{$d->locals["override"]}(); } else { ?'.'>';

		// {* comment *}
		View::$template_patterns[]='#{\*.*?\*}#muis';
		View::$template_replacements[]='';

		// @ print 2+2;
		View::$template_patterns[]='#^\s*@((?!import|page|namespace|charset|media|font-face|keyframes|-webkit|-moz-|-ms-|-o-|region|supports|document).+)$#mui';
		View::$template_replacements[]='<?php $1; ?>';

		
		View::$template_patterns[]='/<tree\s+(.*)>/';
		View::$template_replacements[]='<?php 
		$passed_tree_elements = array();
		$child_branch_name = "$1";
		$call_stack = array();
		$last_next = true;
		d()->level = 0;
		while (true) {
			if(is_object(d()->this)){
				$is_valid = d()->this->valid();
			}else{
				break;
			}
			if($is_valid){
				if(isset($passed_tree_elements[d()->this["id"]])){
					break;
				}
				$passed_tree_elements[d()->this["id"]]=true;
			?>';

				
		View::$template_patterns[]='/<\/tree>/' ;
		View::$template_replacements[]='<?php 
											
			 }
			
			if( isset( d()->this[$child_branch_name]) && count(d()->this[$child_branch_name])>0){
				$call_stack[] = d()->this;
				d()->this = d()->this[$child_branch_name];
				d()->level++;
				continue;
			}else{
				if(is_object(d()->this)){
					if(!d()->this->valid()){
						if( count($call_stack)>0){
							d()->this = array_pop($call_stack);
							d()->level--;
							d()->this->next();
							continue;
						}else {
							break;
						}
					}else{
						d()->this->next();
					}
					continue 1;
				}else{
 					break;
				}
			}
		} ?>';
				
    	
		
		
 
		// </foreach>
	View::$template_patterns[]='/<\/foreach>/' ;
		View::$template_replacements[]='<'.'?php } }
		$d->locals[\'this\'] = array_pop($d->_this_cache );
		 ?'.'>';

 

		View::$template_patterns[]='#\{{([\\\\a-zA-Z0-9_/]+\.html)}}#';
		View::$template_replacements[]='<'.'?php print View::partial("$1", relativePath: $currentTemplateDirectory); ?'.'>';

 
		// {{helper param}}
		View::$template_patterns[]='/\{{([#a-zA-Z0-9_]+)\s+([a-zA-Z0-9_]+)\}}/';
		View::$template_replacements[]= '<'.'?php print $d->$1(array(d()->$2));  ?'.'>';
	
 
		// {.title}
		View::$template_patterns[]='/\{\.([a-zA-Z0-9_]+)\}/';
		View::$template_replacements[]='<'.'?php if(is_array($d->this)) {  print  $d->this[\'$1\']; }else{ print  $d->this->$1; } ?'.'>';

		// {.title|h}
		View::$template_patterns[]='/\{\.([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
		View::$template_replacements[]='<'.'?php if(is_array($d->this)) {  print  $d->$2($d->this[\'$1\']); }else{ print  $d->$2($d->this->$1); } ?'.'>';
		
 

		// {"userlist"|t}
		View::$template_patterns[]='/\{\"(.+?)\"\|([a-zA-Z0-9_]+)\}/';
		View::$template_replacements[]='<'.'?php print $2("$1"); ?'.'>'; 

		// {page.title|h}
		View::$template_patterns[]='/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\|([a-zA-Z0-9_]+)\}/';
		View::$template_replacements[]='<'.'?php if(is_array($d->$1)) {  print $3($d->$1[\'$2\']); }else{ print $3($d->$1->$2); } ?'.'>';
 

		// {{/form}}
		View::$template_patterns[]='/\{{\/([a-zA-Z0-9_]+)\}}/';
		View::$template_replacements[]='</$1>';//Синтаксический сахар

		
		// {=url(0)}
		View::$template_patterns[]='/\{=(.+)\}/';
		View::$template_replacements[]='<'.'?php print  $1; ?'.'>';