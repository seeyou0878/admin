<?php
namespace Admin\Game;

class Game{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_game',
			/*col*/
			array('id', 'name', 'game', 'cnt_account', 'status_id'),
			/*col_ch*/
			array('代碼', '遊戲名稱', '系統識別字', '庫存帳號', '狀態'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', '', '', '', 't_status,alias,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-4 col-sm-4 col-xs-4',
				'col-md-4 col-sm-4 col-xs-4',
				'col-md-2 col-sm-2 col-xs-2 text-right',
				'col-md-2 col-sm-2 col-xs-2',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden', 'text', 'text', 'value', 'select'),
			/*authority check*/
			array(
				$_SESSION['auth']['branch_review'] ?? 0,
				0,
				$_SESSION['auth']['branch_modify'] ?? 0,
				0,
			),
			/*medoo*/
			\Box::obj('db'),
			/*config*/
			array(
				'module' => array(
					array(
						'url' => _url('game_account'),
						'tag' => '帳號列表',
						'sql' => ($_SESSION['user']['cross'])? array('game_id' => 'id') : array('game_id' => 'id', 'branch_id' => $_SESSION['user']['branch_id']),
					)
				)
			)
		);
		
		$obj->decodeJson($_POST);
		
		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
				case 'review':
					$w = ($_SESSION['user']['cross'])? 'status_id = 1': 'branch_id = ' . $_SESSION['user']['branch_id'] . ' AND status_id = 1';
					$r = \Box::obj('db')->query('
					SELECT
						COUNT(id) as cnt_account,
						game_id as id
					FROM t_game_account
					WHERE ' . $w . '
					GROUP BY game_id;
					')->fetchAll(\PDO::FETCH_ASSOC);
					
					$obj->bind($r);
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