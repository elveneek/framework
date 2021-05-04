<?php

	function getUserIP($request){
		$headers = $request->getHeaders();
		foreach( ['X-Real-IP', 'Forwarded', 'X-Forwarded-For', 'X-Forwarded', 'X-Cluster-Client-Ip', 'Client-Ip'] as $header){
			if(isset($headers[$header])){
				return $header;
			}
		}
		return  $request->getServerParams()["REMOTE_ADDR"];
	}
	
	function h($str){
		return htmlspecialchars($str);
	}