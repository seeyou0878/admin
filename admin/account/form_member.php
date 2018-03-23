<?php
namespace Admin;

class MemberList{
	function __construct(){
		new \Admin\Member(false, get_class($this));
	}
}

class Member{
	
	function __construct($is_member=true, $class=null){
		
		$basic_review_auth = !(($_SESSION['auth']['account_review_adv'] ?? 0) || ($_SESSION['auth']['account_create'] ?? 0) || ($_SESSION['auth']['account_modify'] ?? 0) || ($_SESSION['auth']['account_delete'] ?? 0) || ($_SESSION['auth']['account_member'] ?? 0));
		
		$obj = new \Yapa(
			/*file*/
			_url($class?:get_class($this)),
			/*table*/
			't_member',
			/*col*/
			array('id', 'branch_id', 'account_id', 'account', 'password', 'name', 'remark', 'wallet', 'phone', 'valid_id', 'contact', 'status_id', 'receive_id', 'cdate', 'ldate'),
			/*col_ch*/
			array(
				'代碼', '分站', '階層', '帳號', '密碼', '暱稱', '備註', '電子錢包', '手機', '手機驗證', '通訊軟體', '狀態', 
				'指定收款,收款設定優先順序:<br>1.會員設定<br>2.分站設定<br>3.金流設定',
				'註冊日期', '最後登入日期'
			),
			/*empty check*/
			array(0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', 't_account,account,id', '', '', '', '', '', '', 't_valid,alias,id', '', 't_status,alias,id',
				($_SESSION['user']['cross'])? 't_receive,title,id': 't_receive,title,id,{"branch_id": ' . $_SESSION['user']['branch_id'] . '}',
				'', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				(!$is_member && $_SESSION['user']['cross'])? 'col-md-2 col-sm-2 col-xs-2': 'hidden',
				'hidden',
				'col-md-4 col-sm-4 col-xs-4',
				'hidden',
				'col-md-4 col-sm-4 col-xs-4',
				'hidden',
				'hidden',
				($is_member)? 'hidden' : 'col-md-4 col-sm-4 col-xs-4',
				'hidden',
				'hidden',
				($is_member)? 'col-md-4 col-sm-4 col-xs-4' : 'col-md-2 col-sm-2 col-xs-2',
				'hidden',
				'col-md-4 col-sm-4 hidden-xs hidden-create',
				($is_member)? 'hidden hidden-create' : 'col-md-4 col-sm-4 col-xs-4 hidden-create',
			),
			/*select/radiobox/checkbox/text/password/textarea/autocomplete/datepicker */
			array(
				'hidden',
				'hidden',
				'hidden',
				'text',
				($basic_review_auth)? 'hidden': 'password',
				'text',
				'textarea',
				'hidden',
				($basic_review_auth)? 'hidden': 'text',
				($basic_review_auth)? 'hidden': 'select',
				($basic_review_auth)? 'hidden': 'json',
				($basic_review_auth)? 'hidden': 'select',
				($basic_review_auth)? 'hidden': 'select',
				'datepicker,{"disabled": true}',
				'datepicker,{"format": "Y-m-d H:i:s", "disabled": true}',
			),
			/*authority check*/
			array(
				($is_member)? (($_SESSION['auth']['account_review'] ?? 0) || ($_SESSION['auth']['account_review_adv'] ?? 0)): ($_SESSION['auth']['account_member'] ?? 0),
				($is_member)? ($_SESSION['auth']['account_create'] ?? 0): 0,
				$_SESSION['auth']['account_modify'] ?? 0,
				$_SESSION['auth']['account_delete'] ?? 0,
			),
			/*medoo*/
			\Box::obj('db'),
			/*config*/
			array(
				'preset' => array(
					'contact' => array('line,Line' => '', 'wechat,Wechat' => '', 'other,其他' => ''),
				),
				'module' => array(
					array(
						'url' => _url('form_member_bank'),
						'tag' => '帳戶列表',
						'sql' => array('member_id' => 'id'),
					),
					array(
						'url' => _url('game_member_config'),
						'tag' => '遊戲設定',
						'sql' => array('member_id' => 'id'),
					),
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
					break;
				case 'create':
					//check level, only lvl6 can create member
					$account_lvl = \DB::table('t_account')
						->select('level_id')
						->where('id', $obj->arg['data']['account_id'])
						->first()->level_id;
					if($account_lvl < 6){
						echo json_encode(['code' => 1, 'text' => '僅有代理商可以新增會員']);
						exit;
					}

					//process data
					$obj->arg['data']['password'] = \App::make('Lib\Mix')->password_hash($obj->arg['data']['account'], $obj->arg['data']['password']);
					$obj->arg['data']['wallet'] = 0; // init wallet
					$obj->arg['data']['branch_id'] = \DB::table('t_account')->where('id', $obj->arg['data']['account_id'])->first()->branch_id;
					$obj->arg['data']['cdate'] = time();
					$obj->arg['data']['ldate'] = null;

					//create member
					$data = $obj->{$obj->act}($obj->arg);
					$data = json_decode($data, true);
					
					if($data['code']){
						// fail
					}else{
						\App::make('Game\Config')->set_member_config($data['data']);
					}
					echo json_encode($data);
					exit;
					break;
					
				case 'modify':
					$password = \DB::table('t_member')->where('id', $obj->arg['data']['id'])->first()->password;
					if($password != $obj->arg['data']['password']){
						$obj->arg['data']['password'] = \App::make('Lib\Mix')->password_hash($obj->arg['data']['account'], $obj->arg['data']['password']);
					}
					// avoid change wallet
					unset($obj->arg['data']['wallet']);
					break;
				
				case 'exec':
					$res_cred = \App::make('Lib\Mix')->get_credit($obj->arg['data']['id'])['data'];
					$res_ord = \App::make('Lib\Mix')->get_order_info($obj->arg['data']['id'], strtotime(date('Y-m-d')), strtotime(date('Y-m-d') + 86399), 2);
					$arr = array_merge($res_cred, array_map('number_format', $res_ord));

					echo json_encode(['code' => 0, 'data' => $arr]);
					exit;
					break;
				case 'get_level':
					if($is_member){
						echo json_encode(['code' => 0, 'data' => '']);
						exit;
					}

					$arr = [];
					$accounts = [];

					$id = $obj->arg['data']['id'];
					$id = \DB::table('t_member')->select('account_id')->where('id', $id)->first()->account_id;

					$levels = \DB::table('t_level')->get();
					$levels = \Lib\Mix::to_pairs($levels, 'id', 'name');

					while(true){
						$account = \DB::table('t_account')->select('level_id', 'account', 'account_id')->where('id', $id)->first();
						if($account){
							$id = $account->account_id;
							$arr[] = ['name' => $account->account, 'level' => $levels[$account->level_id]];
						}else{
							break;
						}
					}
					$arr = array_reverse($arr);

					$tpl = new \Yatp(__DIR__ . '/account.tpl');
					$html = $tpl->block('level')->assign([
						'li' => $tpl->block('level.li')->nest($arr),
					])->render(false);

					echo json_encode(['code' => 0, 'data' => $html]);
					exit;
					break;
				default:
					break;
			}
			
			//do the work
			echo $obj->{$obj->act}($obj->arg);
		}else{
			$tpl = new \Yatp(__DIR__ . '/account.tpl');

			/*credit display*/
			$t_game = \DB::table('t_game')->where('status_id', 1)->get();
			$th = [];
			$games = [];
			foreach($t_game as $v){
				$th[] = ['text' => $v->name];
				$games[] = ['game' => $v->game];
			}

			$table = $tpl->block('table')->assign([
				'th' => $tpl->block('table.th')->nest($th),
				'td' => $tpl->block('table.td')->nest($games),
			])->render(false);

			$tpl->block('credits')->assign([
				'unique_id' => $obj->unique_id,
				'url' => $obj->file,
				'level' => preg_replace('/[\\r\\n]+/s', '', $tpl->block('level')->render(false)),
				'table' => preg_replace('/[\\r\\n]+/s', '', $table),
			])->render();

			$obj->render();
		}

		unset($obj);
		exit;
	}
}