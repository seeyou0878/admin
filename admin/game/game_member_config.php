<?php
namespace Admin\Game;

class MemberConfig{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_game_member_config',
			/*col*/
			array('id', 'member_id', 'game_id', 'game_account_id', 'rakeback', 'status_id'),
			/*col_ch*/
			array('代碼', '會員', '遊戲名稱', '帳號', '返水', '狀態'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_member,account,id', 't_game,name,id', 't_game_account,account,id', '', 't_status,alias,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'hidden',
				'col-md-3 col-sm-3 col-xs-3',
				'col-md-3 col-sm-3 col-xs-3',
				'col-md-3 col-sm-3 col-xs-3',
				'col-md-3 col-sm-3 col-xs-3',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden', 'hidden', 'select,{"disabled": true}', 'autocomplete,{"disabled": true}', 'text', 'select'),
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
				case 'review':
						$games = \DB::table('t_game')
							->select(\DB::raw('GROUP_CONCAT(id) as ids'))
							->where('game', '!=', 'Wallet')
							->where('status_id', 1)
							->first()->ids ?? '';
						$game_ids = explode(',', $games);
						$obj->arg['where']['AND']['game_id'] = $game_ids;
					break;
				case 'exec':
					$result = ['code' => 0, 'text' => ''];
					$member_id = json_decode($obj->arg['data']['id'], true);
					$member_id = $member_id['AND']['member_id'] ?? 0;
					$game_id = $obj->arg['data']['game_id'] ?? 0;


					if(!$member_id || !$game_id){
						$result = ['code' => 1, 'text' => '配置帳號錯誤'];
					}

					if($result['code']){
						// fail
					}else{
						//get new game account for the requested game
						$new_acc = \App::make('Game\Account')->get_game_account($member_id, $game_id);
						$new_acc = array_values($new_acc)[0] ?? 0; //get first index

						if (!$new_acc) {
							$result = ['code' => 1, 'text' => '庫存帳號數不足'];
						}else{
							// update account
							$id = \DB::table('t_game_member_config')
								->where('member_id', $member_id)
								->where('game_id', $game_id)
								->first()->id;

							\DB::table('t_game_member_config')
								->where('id', $id)
								->update(['game_account_id' => $new_acc]);

							$result = ['code' => 0, 'data' => $id, 'text' => '配置成功'];
						}
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

			$games = \DB::table('t_game')
				->where('game', '!=', 'Wallet')
				->where('status_id', 1)
				->get();
			
			$li = [];
			foreach($games as $v){
				$li[] = ['id' => $v->id, 'name' => $v->name . '配置新帳號'];
			}
			$tpl = new \Yatp(__DIR__ . '/game.tpl');
			$tpl->block('script')->assign([
				'unique_id' => $obj->unique_id,
				'url' => $obj->file,
				'confirm' => '確認配置新帳號?',
				'li' => $tpl->block('li')->nest($li),
			])->render();

			$obj->render();
		}
		
		unset($obj);
		exit;
	}
}