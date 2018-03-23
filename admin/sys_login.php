<?php
namespace Admin;

class Login{
	
	public $act;
	private $database;
	
	function __construct(){
		
		header('P3P: CP="CAO PSA OUR"'); // Damn frameset on IE!!!!!!!
		
		$this->act = isset($_REQUEST['method'])? $_REQUEST['method']: '';
		$this->database = \Box::obj('db');
		
		if(method_exists($this, $this->act)){
			echo $this->{$this->act}();
			exit;
		}
	}
	
	function login(){
		
		//check parameter
		if(($_POST['account'] ?? 0) && ($_POST['password'] ?? 0)){
			$is_child = false;
			$datas = $this->database->select('t_account', '*', array('AND' => array('account'=>$_POST['account'], 'branch_id'=>\Config::get('branch.branch_id'), 'status_id'=>1) ) );
			if(!$datas){
				$datas = $this->database->select('t_child', '*', array('AND' => array('account'=>$_POST['account'], 'branch_id'=>\Config::get('branch.branch_id'), 'status_id'=>1) ) );
				$is_child = true;
			}

			//set session
			$password = \App::make('Lib\Mix')->password_hash($_POST['account'], $_POST['password']);
			if($datas && $password == $datas[0]['password']){
				
				$datas_auth = $this->database->select('t_auth', '*');
				$arr_auth = array();
				$arr_tmp = preg_split('/[\s,]+/', $datas[0]['auth'] ?? '');
				
				foreach($datas_auth as $v){
					$arr_auth[$v['name']] = in_array($v['id'], $arr_tmp);
				}
				
				$_SESSION['auth'] = $arr_auth;
				$_SESSION['user'] = $datas[0];
				$_SESSION['user']['cross'] = ($_SESSION['user']['branch_id'] == 1);
				$_SESSION['user']['root'] = ($is_child)? $datas[0]['account_id']: $datas[0]['id'];

				\DB::table(($is_child)?'t_child': 't_account')->where('id', $datas[0]['id'])->update(['ldate' => time()]);

				$result = ['code' => 0, 'text' => ''];
				
			}else{
				$result = ['code' => 1, 'text' => '帳號或密碼錯誤'];
			}	
		}else{
			$result = ['code' => 1, 'text' => '所有欄位必填'];
		}

		//Log
		$session_id = \Session::getId();
		$ip = ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? '')?: ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')?: ($_SERVER['HTTP_X_REAL_IP'] ?? '')?: $_SERVER['REMOTE_ADDR'];
		$log = [
			'入口:' . $_SERVER['HTTP_HOST'],
			// '登入IP:' . $ip,
			'訊息:' . ($result['text'] ?? ''),
			'帳號:' . $_POST['account'],
		];

		\DB::table('t_log_login')->insert([
			'branch_id' => \Config::get('branch.branch_id'),
			'cdate' => time(),
			'title' => implode(' ', $log),
			'content' => json_encode($_SERVER),
			'account' => $_POST['account'] ?? '',
			'status_id' => $result['code']? 2: 1,
			'session_id' => $session_id,
			'ip' => $ip,
			'loc' => geoip_country_name_by_name($ip) ?: '',
		]);
		
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
	
	function logout(){
		unset($_SESSION['user']);
		unset($_SESSION['auth']);
		header('Location: ./');
	}
	
	// change password
	function change(){
		
		if($_POST['password'] ?? 0){
			
			$is_child = false;
			
			$datas = $this->database->select('t_account', '*', array('AND' => array('account'=>$_SESSION['user']['account'], 'branch_id'=>\Config::get('branch.branch_id'), 'status_id'=>1) ) );
			if(!$datas){
				$datas = $this->database->select('t_child', '*', array('AND' => array('account'=>$_SESSION['user']['account'], 'branch_id'=>\Config::get('branch.branch_id'), 'status_id'=>1) ) );
				$is_child = true;
			}
			
			$password = \App::make('Lib\Mix')->password_hash($_SESSION['user']['account'], $_POST['password']);
			
			if($is_child){
				$this->database->update('t_child', ['password' => $password], ['id' => $_SESSION['user']['id']]);
			}else{
				$this->database->update('t_account', ['password' => $password], ['id' => $_SESSION['user']['id']]);
			}
			
			$result = ['code' => 0, 'text' => '密碼修改成功'];
			
		}else{
			$result = ['code' => 1, 'text' => '密碼務必填寫'];
		}
		
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}
}