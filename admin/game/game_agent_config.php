<?php
namespace Admin\Game;

class AgentConfig{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_game_agent_config',
			/*col*/
			array('id', 'account_id', 'game_id', 'percent', 'rakeback', 'status_id'),
			/*col_ch*/
			array('代碼', '代理', '遊戲名稱', '佔成', '返水', '狀態'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_account,account,id', 't_game,name,id', '', '', 't_status,alias,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'hidden',
				'col-md-4 col-sm-4 col-xs-4',
				'col-md-4 col-sm-4 col-xs-4',
				'col-md-4 col-sm-4 col-xs-4',
				'col-md-4 col-sm-4 col-xs-4',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden', 'hidden', 'select,{"disabled": true}', 'text', 'text', 'select'),
			/*authority check*/
			array(
				$_SESSION['auth']['account_review'] ?? 0,
				0,
				$_SESSION['auth']['account_modify'] ?? 0,
				0,
			),
			/*medoo*/
			\Box::obj('db')
		);
		
		$obj->decodeJson($_POST);
		
		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
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