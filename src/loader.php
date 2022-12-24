<?php

function loadElveneek(){


	require_once ROOT. '/vendor/elveneek/framework/core/core.php';
	require_once ROOT. '/app/App.php';
    \Elveneek\ActiveRecord::$db = false; //Для того, чтобы система ЗНАЛА что этот класс есть, иначе автолоадер пытается сделаеть его из его
	if (!isset(App::$instance)) {
		new App();
	}

	$app = App::$instance;
	return $app;
}
