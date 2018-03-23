<?php
namespace Admin;

class Message{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_message',
			/*col*/
			array('id', 'cdate', 'branch_id', 'member_id', 'title', 'content', 'read'),
			/*col_ch*/
			array('代碼', '建立時間', '分站', '會員', '標題', '內容', '狀態'),
			/*empty check*/
			array(0, 0, 0, 1, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array(
				'', '', 't_branch,name,id', 
				't_member,account,id' . (($_SESSION['user']['cross'])? '': ',{"branch_id":' . $_SESSION['user']['branch_id'] . '}'),
				'', '', 't_message_type,alias,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-3 col-sm-3 col-xs-4 hidden-create',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs': 'hidden',
				'col-md-2 col-sm-2 col-xs-4',
				'col-md-2 col-sm-2 hidden-xs',
				'col-md-3 col-sm-3 hidden-xs',
				'col-md-2 col-sm-2 col-xs-4 hidden-create',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				'datepicker,{"format": "Y-m-d H:i:s"}', 
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'autocomplete', 'text', 'textarea', 'select'),
			/*authority check*/
			array(
				$_SESSION['auth']['message_review'] ?? 0,
				$_SESSION['auth']['message_create'] ?? 0,
				0,
				0,
			),
			/*medoo*/
			\Box::obj('db'),
			/*config*/
			array(
				'module' => array(
					array(
						'url' => _url('form_reply'),
						'tag' => '回覆列表',
						'sql' => array('message_id' => 'id'),
					)
				)
			)
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
					
					$obj->arg['data']['cdate'] = time();
					$obj->arg['data']['udate'] = time();
					$obj->arg['data']['read'] = 2;
					$result = $obj->{$obj->act}($obj->arg);
					$result = json_decode($result, true);

					if($result['code']){
						//fail
					}else{
						//set notice to unread
						\App::make('Lib\\Mix')->setUnread($result['data']);
					}
					echo json_encode($result);
					exit;

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