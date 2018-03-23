<?php

namespace Front;

class Ajax{
	
	public function login($account = '', $password = '')
	{
		$result = ['code' => 0, 'data' => ''];
		$account = $account?: \Request::get('account');
		$password = $password?: \Request::get('password');
		$branch_id = \Config::get('branch.branch_id');

		//Ip banlist check
		$ip = [
			($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''),
			($_SERVER['HTTP_X_FORWARDED_FOR']  ?? ''),
			($_SERVER['HTTP_X_REAL_IP']        ?? ''),
			($_SERVER['REMOTE_ADDR']           ?? ''),
		];
		$ip_str = implode(',', $ip);
		
		if(!$account || !$password || !$branch_id){
			$result = ['code' => 1, 'text' => '請填寫帳號密碼'];
		}else if(\App::make('Lib\Invalid')->ip($ip_str)){
			$result = ['code' => 1, 'text' => '此IP已被禁止'];
		}else{
			$where = [
				'account' => $account,
				'branch_id' => $branch_id,
				'status_id' => 1,
			];
			
			// validate account with branch_id
			$user = \DB::table('t_member')->where($where)->first();
			
			if($user && ($user->password == \App::make('Lib\Mix')->password_hash($account, $password))){
				\Session::put('auth', $user->id);
				\Session::save();
				\DB::table('t_member')->where('id', $user->id)->update(['ldate' => time()]);
			}else{
				$result = ['code' => 1, 'text' => '帳號或密碼錯誤'];
			}

		}

		//log
		$member_id = \Session::get('auth');
		$session_id = \Session::getId();
		$ip = ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? '')?: ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')?: ($_SERVER['HTTP_X_REAL_IP'] ?? '')?: $_SERVER['REMOTE_ADDR'];
		$log = [
			'入口:' . $_SERVER['HTTP_HOST'],
			// '登入IP:' . $ip,
			'訊息:' . ($result['text'] ?? ''),
			'帳號:' . $account,
		];
		\DB::table('t_log_login')->insert([
			'branch_id' => $branch_id,
			'cdate' => time(),
			'title' => implode(' ', $log),
			'content' => json_encode($_SERVER),
			'account' => $account ?? '',
			'status_id' => $result['code']? 2: 1,
			'session_id' => $session_id,
			'ip' => $ip,
			'loc' => geoip_country_name_by_name($ip) ?: '',
		]);

		//kick dup login
		\Redis::publish('client', json_encode(['method' => 'logout', 'member_id' => $member_id, 'session_id' => $session_id]));
		\Redis::set('member_login_' . $member_id, $session_id);
		\Redis::expire('member_login_' . $member_id, 86400*3);

		return $result;
	}

	public function register(){
		$result = ['code' => 0, 'data' => ''];
		//inputs
		$account = \Request::get('account');
		$password = \Request::get('password');
		$password_confirm = \Request::get('password_confirm');
		$name = \Request::get('name');
		$phone = \Request::get('phone');
		$captcha = \Request::get('captcha');
		$line = \Request::get('line');
		$wechat = \Request::get('wechat');
		$contact_type = \Request::get('contact_type');
		$contact = \Request::get('contact');
		$branch_id = \Config::get('branch.branch_id');

		//Validations
		if(!$account || !$password || !$password_confirm || !$name || !$phone || (!$line && !$wechat && !($contact_type && $contact))){
			$result = ['code' => 1, 'text' => '請填寫所有欄位'];
		}else if($password != $password_confirm){
			$result = ['code' => 1, 'text' => '密碼兩次輸入不符'];
		}else if(!preg_match('/^[\w]{3,20}$/', $account)){
			$result = ['code' => 1, 'text' => '帳號格式需為3~20英數'];
		}else if(strlen($password) == 0){
			$result = ['code' => 1, 'text' => '密碼至少為一個字元'];
		}else if(!preg_match('/^[\d]{10}$/', $phone)){
			$result = ['code' => 1, 'text' => '行動電話格式需為10碼數字'];
		}else if(\Validator::make(['captcha' => $captcha], ['captcha' => 'required|captcha'])->fails()){
			$result = ['code' => 1, 'text' => '驗證碼錯誤'];
		}else if(\DB::table('t_member')->where('account', $account)->where('branch_id', $branch_id)->first()){
			$result = ['code' => 1, 'text' => '已有此會員'];
		}else if(\App::make('Lib\Invalid')->phone($phone)){
			$result = ['code' => 1, 'text' => '此電話號碼已被禁止'];
		}else if(\App::make('Lib\Invalid')->limit($phone)){
			$result = ['code' => 1, 'text' => '過多帳戶使用此電話號碼'];
		}

		//has error code
		if($result['code']){
			// fail
		}else{
			//success
			$agent = \Config::get('branch.agent');
			$account_id = \DB::table('t_account')
				->select('id')
				->where('branch_id', $branch_id)
				->where('account', $agent)
				->where('level_id', 6)
				->first()->id ?? 0;
			if(!$account_id){
				$account_id = \DB::table('t_account')
					->select('id')
					->where('branch_id', $branch_id)
					->where('account', 'www')
					->where('level_id', 6)
					->first()->id ?? 0;
			}

			$member_id = \DB::table('t_member')->insertGetId([
				'account' => $account,
				'password' => \App::make('Lib\Mix')->password_hash($account, $password),
				'name' => $name,
				'phone' => $phone,
				'branch_id' => $branch_id,
				'status_id' => 1,
				'valid_id' => 1,
				'wallet' => 0,
				'cdate' => time(),
				'account_id' => $account_id,
				'contact' => json_encode(['line' => $line, 'wechat' => $wechat, 'other' => ($contact_type && $contact)? '(' . $contact_type . ') ' . $contact: '']),
			]);

			\App::make('Game\Config')->set_member_config($member_id);

			//Log in
			$this->login();
			$result = ['code' => 0, 'text' => '註冊成功'];
		}

		return $result;
	}

	public function edit()
	{
		$result = ['code' => 0, 'data' => ''];
		//inputs
		$password = \Request::get('password');
		$password_confirm = \Request::get('password_confirm');

		//validations
		if($password != $password_confirm){
			$result = ['code' => 1, 'text' => '密碼兩次輸入不符'];
		}else if(strlen($password) == 0){
			$result = ['code' => 1, 'text' => '密碼至少為一個字元'];
		}

		//success
		if($result['code']){
			// fail
		}else{	
			//get user account
			$member_id = \Session::get('auth');
			$account = \DB::table('t_member')->where('id', $member_id)->first()->account;

			\DB::table('t_member')->where('id', $member_id)->update([
				'password' => \App::make('Lib\Mix')->password_hash($account, $password),
			]);

			$result = ['code' => 0, 'text' => '修改成功'];
		}

		return $result;
	}

	public function send_code()
	{
		$result = ['code' => 0, 'data' => ''];
		$member_id = \Session::get('auth');
		$phone = \Config::get('branch.user-phone');
		$branch_id = \Config::get('branch.branch_id');
		$number = rand('111111', '999999');
		$text = '親愛的會員您好，您的註冊驗證碼為『' . $number . '』，請立即於網頁上輸入，本驗證碼有效時間為30分鐘。';
		$old_sms = \DB::table('t_sms')->select('cdate')->where('member_id', $member_id)->orderBy('cdate', 'desc')->first();
		
		//check is banned
		if(\App::make('Lib\Invalid')->phone($phone)){
			$result = ['code' => 1, 'text' => '此號碼已被禁止'];
		}else if($old_sms && (time() - $old_sms->cdate) <= 1800){
			$result = ['code' => 1, 'text' => '30分鐘內只能請求一次發送驗證碼'];
		}
		
		//success
		if($result['code']){
			// fail
		}else{	
			//success
			$send = \App::make('Lib\Sms')->send($phone, $text);
			if($send['code']){
				$status_id = 2;
				$send = $send['text'];
			}else{
				$status_id = 1;
				$send = $send['text'];
			}

			\DB::table('t_sms')->insert([
				'cdate' => time(),
				'member_id' => $member_id,
				'remark' => $number,
				'branch_id' => $branch_id,
				'content' => $text,
				'phone' => $phone,
				'result' => $send,
				'status_id' => $status_id,
			]);

			$result = ['code' => 0, 'text' => '簡訊發送成功，請於30分內完成驗證'];
		}

		return $result;
	}

	public function verify_code()
	{
		$result = ['code' => 0, 'data' => ''];
		$member_id = \Session::get('auth');
		$verify_code = \Request::get('verify_code'); //input code
		$sms = \DB::table('t_sms') //code for current user
			->select('remark', 'phone')
			->where('member_id', $member_id)
			->where('cdate', '>', time()-1800)
			->orderBy('id', 'desc')->first();
		$code = $sms->remark ?? '';
		$phone = $sms->phone ?? '';

		//check if codes match
		if($verify_code == $code && \Config::get('branch.user-phone') == $phone){
			//validify user's phone number
			\DB::table('t_member')->where('id', $member_id)->update(['valid_id' => 2]);

			$result = ['code' => 0, 'text' => '手機號碼驗證完成'];
		}else{
			$result = ['code' => 1, 'text' => '驗證碼錯誤'];
		}

		return $result;
	}

	public function upload()
	{
		if(!file_exists('upload')){
			mkdir('upload', 0755);
		}
		
		$files = $_FILES ?? array();
		
		foreach($files as $file){
			// {"name":"new 2.txt","type":"text\/plain","tmp_name":"\/tmp\/phpRJ91Ks","error":0,"size":1295}
			$url = 'upload/' . time() . md5(rand());
			$result[] = array('url' => $url, 'name' => $file['name'], 'size' => $file['size']);
			move_uploaded_file($file['tmp_name'], $url);
		}
		
		return json_encode($result, JSON_UNESCAPED_UNICODE);
	}

	public function bank()
	{
		$result = ['code' => 0, 'data' => ''];
		$auth = \Session::get('auth');
		$member_id = \Session::get('auth');
		$bank_id = \Request::get('bank_id');
		$account_no = \Request::get('account_no');
		$account_name = \Request::get('account_name');
		$pic = \Request::get('pic');
		$bank_code = \DB::table('t_bank')
			->select('code')
			->where('id', $bank_id)
			->first()->code ?? 0;

		if(\App::make('Lib\Invalid')->account($bank_code . '-' . $account_no) || \App::make('Lib\Invalid')->is_bank_account_dup($bank_id, $account_no)){
			$result = ['code' => 1, 'text' => '此帳戶已被禁止'];
		}else if(!($pic && $account_name && $account_no && $bank_id && $member_id && $auth)){
			$result = ['code' => 1, 'text' => '請填寫所有欄位'];
		}

		//success
		if($result['code']){
			// fail
		}else{
			$insert = [
				'member_id' => $member_id,
				'bank_id' => $bank_id,
				'account_no' => $account_no,
				'account_name' => $account_name,
				'pic' => $pic,
				'status_id' => 1,
			];
			
			\DB::table('t_member_bank')->insert($insert);
			$result = ['code' => 0, 'text' => '上傳成功'];
		}

		return $result;
	}

	public function send()
	{
		$result = ['code' => 0, 'data' => ''];
		//inputs
		$title = \Request::get('title');
		$content = \Request::get('content');
		$branch_id = \Config::get('branch.branch_id');
		$account_id = \Session::get('auth');

		//validations
		if(!$title || !$content){
			$result = ['code' => 1, 'text' => '請填寫所有欄位'];
		}

		//success
		if($result['code']){
			// fail
		}else{	
			$id = \DB::table('t_message')->insertGetId([
				'title' => $title,
				'content' => $content,
				'read' => 1,
				'cdate' => time(),
				'member_id' => $account_id,
				'branch_id' => $branch_id,
			]);
			\App::make('Lib\\Mix')->setUnread($id);

			$result = ['code' => 0, 'text' => '送出成功'];
		}

		return $result;
	}

	public function joinus()
	{
		$result = ['code' => 0, 'data' => ''];
		//inputs
		$name = \Request::get('name');
		$email = \Request::get('email');
		$phone = \Request::get('phone');
		$skype = \Request::get('skype');
		$title = \Request::get('title');
		$content = \Request::get('content');
		$captcha = \Request::get('captcha');
		$branch_id = \Config::get('branch.branch_id');

		//Validations
		if(!$email || !$skype || !$title || !$name || !$phone || !$content){
			$result = ['code' => 1, 'text' => '請填寫所有欄位'];
		}else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$result = ['code' => 1, 'text' => 'email格式不正確'];
		}else if(\Validator::make(['captcha' => $captcha], ['captcha' => 'required|captcha'])->fails()){
			$result = ['code' => 1, 'text' => '驗證碼錯誤'];
		}

		//has error code
		if($result['code']){
			// fail
		}else{
			//success
			$agent = \Config::get('branch.agent')?: 'www';
			$account_id = \DB::table('t_account')
				->select('id')
				->where('branch_id', $branch_id)		
				->where('account', $agent)
				->first()->id ?? 0;

			\DB::table('t_joinus')->insert([
				'name' => $name,
				'email' => $email,
				'phone' => $phone,
				'skype' => $skype,
				'title' => $title,
				'content' => $content,
				'cdate' => time(),
				'branch_id' => $branch_id,
				'account_id' => $account_id,
				'read' => 1,
			]);

			$result = ['code' => 0, 'text' => '送出成功'];
		}

		return $result;
	}

	public function set_read()
	{
		$result = ['code' => 0, 'data' => ''];
		$auth = \Session::get('auth');
		$member_id = \Session::get('auth');
		$id = \Request::get('id');
		
		if($id && $member_id && $auth){
			$where = [
				'id' => $id,
				'member_id' => $member_id,
			];
			
			$update = [
				'read' => 3,
			];
			
			// set message as read
			\DB::table('t_message')->where($where)->update($update);
			\App::make('Lib\\Mix')->setUnread($id);
		}else{
			$result = ['code' => 1, 'text' => 'err'];
		}
		
		return $result;
	}

	public function get_unread()
	{
		$result = ['code' => 0, 'data' => ''];
		$auth = \Session::get('auth');
		$member_id = \Session::get('auth');
		
		if($member_id && $auth){
			$where = [
				'member_id' => $member_id,
				'read' => 2,
			];
			
			// count unread messages
			$result['data'] = \DB::table('t_message')->where($where)->count();
		}else{
			$result = ['code' => 1, 'text' => 'err'];
		}
		
		return $result;
	}

	public function store()
	{
		$data['src_id'] = \Request::get('src_id');
		$data['tar_id'] = \Request::get('tar_id');
		$data['total'] = \Request::get('total');
		$data['member_id'] = \Session::get('auth');
		$result = \App::make('Lib\Order')->store($data);
		if($result['code']){
			// fail
		}else{
			if($result['html'] ?? ''){
				// credit card flow
			}else{
				// show payment code
				$order = \DB::table('t_order')->where('id', $result['data'])->first();
				$result['text'] = '繳費代碼: <span class="small">' . ($order->src_text ?? '') . '</span>';
			}
		}
		
		return $result;
	}

	public function transfer()
	{
		$data['src_id'] = \Request::get('src_id');
		$data['tar_id'] = \Request::get('tar_id');
		$data['total'] = \Request::get('total');
		$data['member_id'] = \Session::get('auth');
		$result = \App::make('Lib\Order')->transfer($data);
		if($result['code']){
			// fail
		}else{
			// 自動嘗試轉移
			$arr = [];
			$result = \DB::table('t_order')->where('id', $result['data'])->first();
			foreach($result as $k=>$v){
				$arr[$k] = $v;
			}
			$arr['status_id'] = 2;
			$result = \App::make('Lib\Order')->transfer($arr);
		}
		
		return $result;
	}

	public function withdraw()
	{
		$data['src_id'] = \Request::get('src_id');
		$data['total'] = \Request::get('total');
		$data['member_id'] = \Session::get('auth');
		$result = \App::make('Lib\Order')->withdraw($data);
		
		return $result;
	}

	public function get_credit()
	{
		return \App::make('Lib\Mix')->get_credit(\Session::get('auth'));
	}
}