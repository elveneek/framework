<?php

function loadElveneek(){

	require_once ROOT. '/vendor/elveneek/framework/core/core.php';	
	require_once ROOT. '/app/App.php';

	if (!isset(App::$instance)) {
		new App();
	}

	$app = App::$instance;
	return $app;
}
