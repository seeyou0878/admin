<?php
namespace Admin\Detail;

class Allbet{
	function __construct(){
		new \Admin\Detail\Member('Allbet', get_class($this));
	}
}

class ComeBets{
	function __construct(){
		new \Admin\Detail\Member('ComeBets', get_class($this));
	}
}

class GlobalGaming{
	function __construct(){
		new \Admin\Detail\Member('GlobalGaming', get_class($this));
	}
}

class Microsova{
	function __construct(){
		new \Admin\Detail\Member('Microsova', get_class($this));
	}
}

class Salon{
	function __construct(){
		new \Admin\Detail\Member('Salon', get_class($this));
	}
}

class SuperSport{
	function __construct(){
		new \Admin\Detail\Member('SuperSport', get_class($this));
	}
}

class XinXin{
	function __construct(){
		new \Admin\Detail\Member('XinXin', get_class($this));
	}
}

class Ebet{
	function __construct(){
		new \Admin\Detail\Member('Ebet', get_class($this));
	}
}

class DreamGame{
	function __construct(){
		new \Admin\Detail\Member('DreamGame', get_class($this));
	}
}

class OrientalGame{
	function __construct(){
		new \Admin\Detail\Member('OrientalGame', get_class($this));
	}
}

class ZhiFuBao{
	function __construct(){
		new \Admin\Detail\Member('ZhiFuBao', get_class($this));
	}
}

class NinetyNine{
	function __construct(){
		new \Admin\Detail\Member('NinetyNine', get_class($this));
	}
}

class GlobeBet{
	function __construct(){
		new \Admin\Detail\Member('GlobeBet', get_class($this));
	}
}

class PlayStar{
	function __construct(){
		new \Admin\Detail\Member('PlayStar', get_class($this));
	}
}

class EvoPlay{
	function __construct(){
		new \Admin\Detail\Member('EvoPlay', get_class($this));
	}
}

class BooonGo{
	function __construct(){
		new \Admin\Detail\Member('BooonGo', get_class($this));
	}
}

class Member{
	function __construct($game, $class){
		
		$obj = new \Yapa(
			/*file*/
			_url($class),
			/*table*/
			't_report_' . strtolower($game),
			/*col*/
			array('id', 'report_uid', 'account', 'info', 	'bet_date', 'set_date', 'bet_amount', 'set_amount', 'win_amount', 'type_id', 'status_id', 'member_id'),
			/*col_ch*/
			array('單號', '投注時間/單號', '帳號/局號/遊戲', '投注內容', '投注時間', '結束時間', '投注金額',   '有效投注',   '輸贏金額',   '輸贏', '狀態', '會員'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', '', '', '', '', '', '', '', '', '', 't_status,c3,id', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-3 col-sm-4 col-xs-4',
				'col-md-3 col-sm-4 col-xs-4',
				($this->has_content($game))? 'col-md-4 hidden-sm hidden-xs': 'hidden',
				'hidden',
				'hidden',
				'col-md-2 hidden-sm hidden-xs text-right',
				'col-md-2 hidden-sm hidden-xs text-right',
				'col-md-2 col-sm-4 col-xs-4 text-right',
				'hidden',
				($_SESSION['auth']['report_modify'] ?? 0 )? 'col-md-1 hidden-sm hidden-xs': 'hidden',
				'hidden',
			),
			/*select/radiobox/checkbox/text/password/textarea/autocomplete/datepicker */
			array('hidden', 'text,{"disabled": true}', 'text,{"disabled": true}', 'hidden', 'hidden', 'hidden', 'hidden', 'hidden', 'hidden', 'hidden', 
				($_SESSION['auth']['report_modify'] ?? 0 )? 'radiobox': 'hidden', 
				'hidden'),
			/*authority check*/
			array(
				$_SESSION['auth']['report_review'] ?? 0,
				0,
				$_SESSION['auth']['report_modify'] ?? 0,
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
					//Date correction
					$search = $obj->arg['where']['SEARCH_ADV'] ?? '';
					if($search){
						$adv =  json_decode($search, true);
						$search = $adv['AND'];
						$sdate = strtotime($search['search_sdate']);
						$edate = strtotime($search['search_edate']);
						unset($search['search_sdate']);
						unset($search['search_edate']);
						switch ($game) {
							case 'EvoPlay':
								$search['bet_date[>=]'] = $sdate + 28800;
								$search['bet_date[<=]'] = $edate + 86400 + 28799;
								break;
								
							case 'Salon':
								$search['bet_date[>=]'] = $sdate + 43200;
								$search['bet_date[<=]'] = $edate + 43199;
								break;
							
							case 'XinXin':
							case 'SuperSport':
							case 'GlobeBet':
								$search['set_date[>=]'] = $sdate;
								$search['set_date[<=]'] = $edate + 86399;
								break;
							
							default:
								$search['bet_date[>=]'] = $sdate;
								$search['bet_date[<=]'] = $edate + 86399;
								break;
						}
						if(!($_SESSION['auth']['report_modify'] ?? 0)){
							$search['status_id'] = 1;
						}
						$adv['AND'] = $search;
						$obj->arg['where']['SEARCH_ADV'] = json_encode($adv);
					}

					//custom format
					$r = $obj->getData($obj->arg);
					$d = [];
					
					$tpl = new \Yatp(__DIR__ . '/report.tpl');

					foreach($r['data'] ?? [] as $k=>$v){
						//單號
						if ($game != 'SuperSport' && $game != 'GlobeBet') {
							$html1 = $tpl->block('default_uid')->assign([
								'report_uid' => $v['report_uid'],
								'bet_date' => date('Y-m-d H:i:s', $v['bet_date']),
							])->render(false);
						}else{
							$html1 = $tpl->block('supersport_uid')->assign([
								'bet_date' => date('Y-m-d H:i:s', $v['bet_date']),
								'set_date' => date('Y-m-d', $v['set_date']),
								'report_uid' => $v['report_uid'],
							])->render(false);
						}

						//帳號/類別/遊戲
						$info = json_decode($v['info']); // to array
						$game_class = 'Game\\Report\\' . $game;
						$game_type_string = $game_class::get_game(json_decode(json_encode($info), true));
						$html2 = $tpl->block('default_info')->assign([
							'account' => $v['account'],
							'game_type' => $game_type_string,
						])->render(false);

						//投注內容
						if($this->has_content($game)){
							$html3 = $game_class::create_bet_content_column($info, $tpl);
						}

						//投注金額 有效投注 輸贏金額
						$bet_amount = number_format($v['bet_amount'], 1);
						$set_amount = number_format($v['set_amount'], 1);
						$win_amount = number_format($v['win_amount'], 1);
						$win_amount = $tpl->block('numbers')->assign([
							'class' => ($v['win_amount'] > 0)? 'text-success': 'text-danger',
							'bet_amount' => '投注: ' . $bet_amount . '<br>',
							'set_amount' => '有效: ' . $set_amount . '<br>',
							'win_amount' => $win_amount,
						])->render(false);

						//組成單筆資料
						$tmp = array(
							'id' => $v['id'],
							'report_uid' => $obj->raw($html1),
							'account' => $obj->raw($html2),
							'info' => $obj->raw($html3 ?? ''),
							'bet_amount' => $obj->raw($bet_amount),
							'set_amount' => $obj->raw($set_amount),
							'win_amount' => $obj->raw($win_amount),
						);
						$d[] = $tmp;
					}
					
					$obj->bind($d);

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

	private function has_content($game){
		if(in_array($game, ['ComeBets', 'Microsova', 'GlobalGaming', 'PlayStar', 'EvoPlay', 'BooonGo'])){
			return false;
		}
		return true;
	}
}