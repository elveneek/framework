<?php
namespace Elveneek;
class RecursiveFilterIterator extends \RecursiveFilterIterator {

	public $bigLetterFolders ;
	public $doit ;
 
	  public function accept() {
	  $current = $this->current();
    $name = $current->getFilename();
    $fullName = $current->getPathname();

    // Skip hidden files and directories.
	
	if ($name === '..') {
		
		
		return false;
	}
    if ($name[0] === '.') {
		//Вернуть ли нам директорию?
	//	var_dump("Вернуть ли нам директорию?" . $fullName. ' = '. dirname($fullName));
		$parentName = basename( dirname($fullName));
		if($parentName[0]>='A' && $parentName[0]<='Z'){
			return true;
		}
		
      return false;
    }
	 
	
    if ($this->isDir()) {
		$parentName = basename( dirname($fullName));
		//print "\n вопрос с $parentName and $fullName \n";
		if($parentName[0]>='A' && $parentName[0]<='Z'){
			return false;
		}
		
       return true;//!($name{0}>='A' && $name{0}<='Z');//$name === 'wanted_dirname';
    }
    else {
		//итак, мы имеем файл
      // Only consume files of interest.
	  
	  //print "\n проверяем ". $fullName ."\n";
	  $parentName = basename( dirname($fullName));
	  if($parentName[0]>='A' && $parentName[0]<='Z'){
		  return false;
	  }
	  //print "\n проверяем ". $parentName ."\n";
	  
      return true;// strpos($name, 'wanted_filename') === 0;
    }
  }

}