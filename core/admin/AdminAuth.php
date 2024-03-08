<?php


class AdminAuth {

	protected $_isGuest = true;
	protected $_login = "";
	protected $isInitialized = false;

	public function __construct(){
		$this->clear();
	}
	//Запускается при старте системы,
	public function clear(){
		$this->_isGuest = true;
		$this->_login = '';
	}

	public function checkUserPassword($login, $password){
		if(isset($_ENV['DEVELOPER_LOGIN']) && $_ENV['DEVELOPER_LOGIN']!==''){
			$developerLogin = $_ENV['DEVELOPER_LOGIN'];
		}else{
			$developerLogin = 'developer';
		}

		if($login===$developerLogin){
			//PASSWORD_PEPPER не используется для методов md5 и plain.
			if(!isset($_ENV['DEVELOPER_PASSWORD'])){
				throw new Exception('Developer password is unknown. Please, check .env file for DEVELOPER_PASSWORD option.');
				return false;
			}
			//Проверка на алгоритм.
			$parts = explode(':', $_ENV['DEVELOPER_PASSWORD'], 2);
			if(count($parts)==2 && $parts[0]=='md5'){
				//Режим md5
				return md5($password) === $parts[1];
			}
			if(count($parts)==2 && $parts[0]=='plain'){
				//Режим md5
				return  ($password) === $parts[1];
			}
			//Оставшийся алгоритм используется по-умолчанию
			if(isset($_ENV['PASSWORD_PEPPER']) && $_ENV['PASSWORD_PEPPER']!=''){
				//Предполагается, что при задании пароля был задан тот же самый PASSWORD_PEPPER.
				$password = hash_hmac("sha256", $password, $_ENV['PASSWORD_PEPPER']);
			}

			return   password_verify ($password, $_ENV['DEVELOPER_PASSWORD']);
		}else{
			return false;
		}
	}

	public function login($login=false, $request = false){
		//Если передано без параметров, то возвращается текущий логин авторизованный
		if($login===false){
			if(!$this->isInitialized){
				$this->initialize();
			}
			return $this->_login;
		}


		$session_id =  ElveneekCore::$instance->session->id();
		//Ищем существующую сессию
		$session = Admin_session::where('sid = ?', $session_id)->limit(1)->order('');

		if($session->isEmpty){
			//Сессия не найдена
			$session = Admin_session::create();
			$session->sid = $session_id;
		}
		$session->login = $login;
		$session->is_active = 1;
		if($request !== false){
			$headers = $request->getHeaders();
			$session->ip = getUserIP($request);
			$session->user_agent = $headers["User-Agent"][0];
		}
		$session->save();
		//print 'всё';
		return;
		$this->_isGuest = false;
		$this->_login = $login;
		$this->isInitialized = true;
	}

	public function logout(){

	}


	public function isGuest(){
		if(!$this->isInitialized){
			$this->initialize();
		}
		return $this->_isGuest;
	}

	//Обращается в базу данных и ищет там нужную сессию. Проверяет, что она включена.
	public function initialize(){
		if(ElveneekCore::$instance->session==''){
			$this->_isGuest = true;
			$this->_login = '';
			$this->isInitialized = true;
			return;
		}
		if(ElveneekCore::$instance->session->isEmptyNow){
			$this->_isGuest = true;
			$this->_login = '';
			$this->isInitialized = true;
			return;
		}

		//Запрос в базу данных
		$session = Admin_session::where('sid = ?', ElveneekCore::$instance->session->id())->limit(1)->order('')->select('is_active, login');
		if($session->is_empty){
			$this->_isGuest = true;
			$this->_login = '';
			$this->isInitialized = true;
			return;
		}elseif($session->get('is_active') == '1'){
			$this->_isGuest = false;
			$this->_login = $session->get('login');
			$this->isInitialized = true;
			return;
		}else{
			$this->_isGuest = true;
			$this->_login = '';
			$this->isInitialized = true;
			return;
		}
	}


}
