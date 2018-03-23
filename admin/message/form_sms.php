<?php
namespace Admin;

class Sms{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_sms',
			/*col*/
			array('id', 'cdate', 'branch_id', 'last', 'member_id', 'content', 'status_id', 'result'),
			/*col_ch*/
			array('代碼', '建立時間', '分站', '發送人', '會員', '內容', '狀態', '結果'),
			/*empty check*/
			array(0, 0, 0, 0, 1, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array(
				'', '', 't_branch,name,id', '',
				't_member,account,id' . (($_SESSION['user']['cross'])? '': ',{"branch_id":' . $_SESSION['user']['branch_id'] . '}'),
				'', 't_status,c2,id', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-2 col-sm-2 col-xs-4 hidden-create',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs': 'hidden',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-2 col-sm-2 hidden-xs',
				'col-md-2 col-sm-2 hidden-xs hidden-create',
				'hidden hidden-create',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden', 
				'datepicker,{"format": "Y-m-d H:i:s"}',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'hidden', 'autocomplete', 'textarea', 'select', 'text'),
			/*authority check*/
			array(
				$_SESSION['auth']['message_review'] ?? 0,
				$_SESSION['auth']['message_create'] ?? 0,
				0,
				$_SESSION['auth']['message_delete'] ?? 0,
			),
			/*medoo*/
			\Box::obj('db')
		);
		
		$obj->decodeJson($_POST);
		
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
					if($_SESSION['user']['cross']){
						// pass
					}else{
						$obj->arg['data']['branch_id'] = $_SESSION['user']['branch_id'];
					}
					$obj->arg['data']['last'] = $_SESSION['user']['account'];
					$obj->arg['data']['cdate'] = time();
					
					// phone
					$member = \DB::table('t_member')->where('id', $obj->arg['data']['member_id'])->first();
					if($member && $member->phone){
						$phone = $member->phone;
					}
					else{
						echo json_encode(['code' => 1, 'text' => '會員名稱或手機錯誤']);
						exit;
					}
					
					// sms
					$send = \App::make('Lib\Sms')->send($phone, $obj->arg['data']['content']);
					if($send['code']){
						$obj->arg['data']['status_id'] = 2;
						$obj->arg['data']['result'] = $send['text'];
					}else{
						$obj->arg['data']['status_id'] = 1;
						$obj->arg['data']['result'] = $send['text'];
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