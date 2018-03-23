<?php
namespace Admin;

class Child{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_child',
			/*col*/
			array('id', 'branch_id', 'account_id', 'account', 'password', 'name', 'auth', 'status_id', 'cdate', 'ldate'),
			/*col_ch*/
			array('代碼', '分站', '階層', '帳號', '密碼', '暱稱', '權限', '狀態', '註冊日期', '最後登入日期'),
			/*empty check*/
			array(0, 0, 0, 1, 1, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', '', '', '', '', 't_auth,alias,id', 't_status,alias,id', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'hidden',
				'hidden',
				'col-md-4 col-sm-4 col-xs-4',
				'hidden',
				'col-md-4 col-sm-4 col-xs-4',
				'hidden hidden-create',
				'col-md-4 col-sm-4 col-xs-4',
				'hidden hidden-create',
				'hidden hidden-create',
			),
			/*select/radiobox/checkbox/text/password/textarea/autocomplete/datepicker */
			array(
				'hidden',
				'hidden',
				'hidden',
				'text', 'password', 'text',
				($_SESSION['user']['cross'])? 'checkbox': 'hidden',
				'select',
				'datepicker,{"disabled": true}',
				'datepicker,{"format": "Y-m-d H:i:s", "disabled": true}',
			),
			/*authority check*/
			array(
				$_SESSION['auth']['account_child'] ?? 0,
				$_SESSION['auth']['account_child'] ?? 0,
				$_SESSION['auth']['account_child'] ?? 0,
				0,
			),
			/*medoo*/
			\Box::obj('db')
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
					break;
					
				case 'create':
					$acc = \DB::table('t_account')->where('id', $obj->arg['data']['account_id'])->first();
					$obj->arg['data']['branch_id'] = $acc->branch_id;
					$obj->arg['data']['auth'] = $acc->auth;
					$obj->arg['data']['password'] = \App::make('Lib\Mix')->password_hash($obj->arg['data']['account'], $obj->arg['data']['password']);
					$obj->arg['data']['cdate'] = time();
					$obj->arg['data']['ldate'] = null;

					//Check if acc duplicated in t_account & t_child
					if(\App::make('Lib\Invalid')->is_acc_dup($obj->arg['data']['account'], '', $acc->branch_id ?? 0)){
						$result = ['code' => 1, 'text' => '帳號重複'];
					}else{
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
					
					break;
					
				case 'modify':
					$result = ['code' => 0, 'text' => ''];
					
					$acc = \DB::table('t_child')->where('id', $obj->arg['data']['id'])->first();
					
					if(\App::make('Lib\Invalid')->is_acc_dup($obj->arg['data']['account'], $obj->arg['data']['id'], $acc->branch_id ?? 0)){
						// Check if acc duplicated in t_account & t_child
						$result = ['code' => 1, 'text' => '帳號重複'];
					}else{
						$org_data = \DB::table('t_account')->where('id', $obj->arg['data']['id'])->first();
						$account = $org_data->account ?? '';
						$password = $org_data->password ?? '';
						
						if($account != $obj->arg['data']['account'] && $password == $obj->arg['data']['password']){
							$result = ['code' => 1, 'text' => '若修改帳號，請重新輸入密碼'];
						}
					}

					if($result['code']){
						// fail
						echo json_encode($result);
						exit;
					}else{
						$password = \DB::table('t_child')->where('id', $obj->arg['data']['id'])->first()->password;
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