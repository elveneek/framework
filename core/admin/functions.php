<?php
function iam($user=false){
	if($user===false){
		return !d()->adminAuth->isGuest();
	}
	return d()->adminAuth->login() === $user;
}
