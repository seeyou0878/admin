<?php
namespace Admin\Game;

class Account{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_game_account',
			/*col*/
			array('id', 'branch_id', 'game_id', 'account', 'password', 'status_id'),
			/*col_ch*/
			array('代碼', '分站', '遊戲名稱', '帳號', '密碼', '狀態'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', 't_game,name,id', '', '', 't_status,alias,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs': 'hidden',
				'col-md-4 col-sm-4 col-xs-4',
				'col-md-4 col-sm-4 col-xs-4',
				'hidden',
				'col-md-4 col-sm-4 col-xs-4',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'select', 'text', 'text', 'select'),
			/*authority check*/
			array(
				$_SESSION['auth']['branch_review'] ?? 0,
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