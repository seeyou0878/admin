<?php
namespace Admin;

class User{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_account',
			/*col*/
			array('id', 'branch_id', 'account', 'account_id', 'level_id', 'password', 'name', 'cnt_member', 'auth', 'status_id', 'cdate', 'ldate'),
			/*col_ch*/
			array('代碼', '分站,新增站長時必填', '帳號', '下線', '階層', '密碼', '暱稱', '會員數', '權限', '狀態', '註冊日期', '最後登入日期'),
			/*empty check*/
			array(0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', '', 't_account,account,id', 't_level,name,id', '', '', '', 't_auth,alias,id,{"ORDER": "sort"}', 't_status,alias,id', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				$_SESSION['user']['cross']? 'col-md-1 col-sm-1 hidden-xs hidden-modify': 'hidden',
				'col-md-2 col-sm-2 col-xs-3',
				'col-md-1 col-sm-1 col-xs-1 hidden-create',
				'hidden',
				'hidden',
				'hidden',
				'col-md-2 col-sm-2 col-xs-3 text-right',
				'hidden hidden-create',
				'col-md-2 col-sm-2 hidden-xs',
				'hidden hidden-create',
				'hidden hidden-create',
			),
			/*select/radiobox/checkbox/text/password/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'text', 
				($_SESSION['user']['cross'])? 'autocomplete': 'hidden',
				'hidden',
				'password', 
				'text', 
				'value',
				($_SESSION['user']['cross'])? 'checkbox': 'hidden',
				'select',
				'datepicker,{"disabled": true}',
				'datepicker,{"format": "Y-m-d H:i:s", "disabled": true}',
			),
			/*authority check*/
			array(
				$_SESSION['auth']['account_review'] ?? 0,
				$_SESSION['auth']['account_create'] ?? 0,
				$_SESSION['auth']['account_modify'] ?? 0,
				$_SESSION['auth']['account_delete'] ?? 0,
			),
			/*medoo*/
			\Box::obj('db'),
			/*config*/
			array(
				'perpage' => 0,
				'root' => $_SESSION['user']['cross']? 0: $_SESSION['user']['root'],
				'sum' => ['cnt_member'],
				'level' => 6,
				'admin' => ($_SESSION['user']['cross'] ?? 0)? true: false,
				'module' => array(
					array(
						'url' => _url('form_member'),
						'tag' => '會員列表',
						'sql' => ['account_id' => 'id'] + (($_SESSION['user']['cross'])? []: ['branch_id' => 'branch_id']),
					),
					array(
						'url' => _url('form_child'),
						'tag' => '子帳號列表',
						'sql' => ['account_id' => 'id'] + (($_SESSION['user']['cross'])? []: ['branch_id' => 'branch_id']),
					),
					array(
						'url' => _url('game_agent_config'),
						'tag' => '佔成返水',
						'sql' => ['account_id' => 'id'],
					)
				)
			)
		);
		
		$arr = $obj->decodeJson($_POST);

		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
				case 'review':
					if($_SESSION['user']['cross']){
						// pass
					}else{
						$obj->arg['where']['AND']['branch_id'] = $_SESSION['user']['branch_id'];
					}
					
					$r = \Box::obj('db')->query('
					SELECT
						COUNT(id) as cnt_member,
						account_id as id
					FROM t_member
					GROUP BY account_id;
					')->fetchAll(\PDO::FETCH_ASSOC);
					
					$obj->bind($r);

					echo $obj->{$obj->act}($obj->arg, function($r) use ($obj){
						$tpl = new \Yatp(__DIR__ . '/report.tpl');
						foreach($r['data'] as $k=>$v){
							$r['data'][$k]['account'] = $obj->raw($v['account'] . '(' . $v['name'] . ')<br>' . ($v['level_id']?: '會員'));
						}
						return $r;
					});
					exit;
					break;
					
				case 'create':
					$result = ['code' => 0, 'text' => ''];
					
					if(!$obj->arg['data']['branch_id'] && !$obj->arg['data']['account_id']){
						$result = ['code' => 1, 'text' => '請填寫分站'];
					}else if(!$obj->arg['data']['account'] || !$obj->arg['data']['password'] || !$obj->arg['data']['name'] || !$obj->arg['data']['status_id']){
						$result = ['code' => 1, 'text' => '請填寫所有欄位'];
					}else if(\App::make('Lib\Invalid')->has_master($obj->arg['data']['branch_id']) && !$obj->arg['data']['account_id']){
						$result = ['code' => 1, 'text' => '此分站已存在站長'];
					}else if($obj->arg['data']['account_id']){
						$account = \DB::table('t_account')->where('id', $obj->arg['data']['account_id'])->first();
						$obj->arg['data']['branch_id'] = $account->branch_id;
						$obj->arg['data']['level_id'] = $account->level_id + 1;
					}else{
						$obj->arg['data']['level_id'] = 1;
					}
					$obj->arg['data']['password'] = \App::make('Lib\Mix')->password_hash($obj->arg['data']['account'], $obj->arg['data']['password']);
					
					if($result['code']){
						// fail
					}else if(\App::make('Lib\Invalid')->is_acc_dup($obj->arg['data']['account'], '', $obj->arg['data']['branch_id'])){
						// Check if acc duplicated in t_account & t_child
						$result = ['code' => 1, 'text' => '帳號重複'];
					}else if(!\App::make('Lib\Invalid')->is_valid_level($obj->arg['data']['level_id'])){
						$result = ['code' => 1, 'text' => '階層錯誤'];
					}else{
						$auth = \DB::table('t_level')->select('auth')->find($obj->arg['data']['level_id'])->auth;
						
						$obj->arg['data']['auth'] = $auth;
						$obj->arg['data']['cdate'] = time();
						$obj->arg['data']['ldate'] = null;

						$result = $obj->{$obj->act}($obj->arg);
						$result = json_decode($result, true);
					}

					if($result['code']){
						// fail
					}else{
						\App::make('Game\Config')->set_agent_config($result['data']);
					}
					echo json_encode($result);
					exit;
					
				case 'modify':
					$result = ['code' => 0, 'text' => ''];
					
					$acc = \DB::table('t_account')->where('id', $obj->arg['data']['id'])->first();
					$ucc = \DB::table('t_account')->where('id', $obj->arg['data']['account_id'])->first();
					
					if(\App::make('Lib\Invalid')->is_acc_dup($obj->arg['data']['account'], $obj->arg['data']['id'], $acc->branch_id ?? 0)){
						// Check if acc duplicated in t_account & t_child
						$result = ['code' => 1, 'text' => '帳號重複'];
					}else if((!$ucc && $acc->level_id != 1) || ($ucc && ($ucc->level_id != $acc->level_id-1 || $ucc->branch_id != $acc->branch_id))){
						$result = ['code' => 1, 'text' => '階層錯誤'];
					}else{
						$org_data = \DB::table('t_account')->where('id', $obj->arg['data']['id'])->first();
						$account = $org_data->account;
						$password = $org_data->password;
						
						if($account != $obj->arg['data']['account'] && $password == $obj->arg['data']['password']){
							$result = ['code' => 1, 'text' => '若修改帳號，請重新輸入密碼'];
						}
					}

					if($result['code']){
						// fail
						echo json_encode($result);
						exit;
					}else{
						$password = \DB::table('t_account')->where('id', $obj->arg['data']['id'])->first()->password;
						if($password != $obj->arg['data']['password']){
							$obj->arg['data']['password'] = \App::make('Lib\Mix')->password_hash($obj->arg['data']['account'], $obj->arg['data']['password']);
						}
					}
					break;
					
				default:
					break;
			}
			
			//do the work
			echo $obj->{$obj->act}($obj->arg);
		}else{
			$obj->render();
		}

		unset($obj);
		exit;
	}
}