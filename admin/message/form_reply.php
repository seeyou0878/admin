<?php
namespace Admin;

class Reply{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_reply',
			/*col*/
			array('id', 'cdate', 'last', 'message_id', 'content'),
			/*col_ch*/
			array('代碼', '建立時間', '回覆人', '主題', '內容'),
			/*empty check*/
			array(0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', '', '', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-3 col-sm-3 col-xs-4 hidden-create',
				'col-md-3 col-sm-3 col-xs-4',
				'hidden',
				'col-md-3 col-sm-3 col-xs-4',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden', 'datepicker,{"format": "Y-m-d H:i:s"}', 'hidden', 'hidden', 'textarea'),
			/*authority check*/
			array(
				$_SESSION['auth']['message_review'] ?? 0,
				$_SESSION['auth']['message_create'] ?? 0,
				0,
				0,
			),
			/*medoo*/
			\Box::obj('db')
		);
		
		$obj->decodeJson($_POST);
		
		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
				case 'create':
					$last = $_SESSION['user']['account'];
					$obj->arg['data']['last'] = $last;
					$obj->arg['data']['cdate'] = time();

					$msg_id = $obj->arg['data']['message_id'];
					\DB::table('t_message')->where('id', $msg_id)->update(['read' => 2, 'udate' => time()]);

					//set notice to unread
					\App::make('Lib\\Mix')->setUnread($msg_id);
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