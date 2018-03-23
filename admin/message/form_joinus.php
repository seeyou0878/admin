<?php
namespace Admin;

class Joinus{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_joinus',
			/*col*/
			array('id', 'cdate', 'branch_id', 'account_id', 'name', 'title', 'email', 'phone', 'skype', 'content'),
			/*col_ch*/
			array('代碼', '建立時間', '分站', '代理', '建立人', '標題', '信箱', '電話', 'Skype', '內容'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', '', 't_branch,name,id', 't_account,account,id', '', '', '', '', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-2 col-sm-2 col-xs-4 col-lg-2',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs col-lg-1': 'hidden',
				'col-md-3 col-sm-3 col-xs-4 col-lg-1',
				'col-md-2 col-sm-2 hidden-xs col-lg-1',
				'col-md-3 col-sm-3 col-xs-4 col-lg-2',
				'hidden',
				'hidden',
				'hidden',
				'col-md-2 col-sm-2 hidden-xs col-lg-3',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden', 
				'datepicker,{"format": "Y-m-d H:i:s"}',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'autocomplete', 'text', 'text', 'text', 'text', 'text', 'textarea'),
			/*authority check*/
			array(
				$_SESSION['auth']['message_review'] ?? 0,
				0,
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