<?php
namespace Admin\Report;

class Allbet{
	function __construct(){
		new \Admin\Report\Agent('allbet', get_class($this));
	}
}

class ComeBets{
	function __construct(){
		new \Admin\Report\Agent('comebets', get_class($this));
	}
}

class GlobalGaming{
	function __construct(){
		new \Admin\Report\Agent('globalgaming', get_class($this));
	}
}

class Microsova{
	function __construct(){
		new \Admin\Report\Agent('microsova', get_class($this));
	}
}

class Salon{
	function __construct(){
		new \Admin\Report\Agent('salon', get_class($this));
	}
}

class SuperSport{
	function __construct(){
		new \Admin\Report\Agent('supersport', get_class($this));
	}
}

class XinXin{
	function __construct(){
		new \Admin\Report\Agent('xinxin', get_class($this));
	}
}

class Ebet{
	function __construct(){
		new \Admin\Report\Agent('ebet', get_class($this));
	}
}

class DreamGame{
	function __construct(){
		new \Admin\Report\Agent('dreamgame', get_class($this));
	}
}

class OrientalGame{
	function __construct(){
		new \Admin\Report\Agent('orientalgame', get_class($this));
	}
}

class ZhiFuBao{
	function __construct(){
		new \Admin\Report\Agent('zhifubao', get_class($this));
	}
}

class NinetyNine{
	function __construct(){
		new \Admin\Report\Agent('ninetynine', get_class($this));
	}
}

class GlobeBet{
	function __construct(){
		new \Admin\Report\Agent('globebet', get_class($this));
	}
}

class PlayStar{
	function __construct(){
		new \Admin\Report\Agent('playstar', get_class($this));
	}
}

class EvoPlay{
	function __construct(){
		new \Admin\Report\Agent('evoplay', get_class($this));
	}
}

class BooonGo{
	function __construct(){
		new \Admin\Report\Agent('booongo', get_class($this));
	}
}

class Agent{
	
	function __construct($game, $class){
		
		$obj = new \Yapa(
			/*file*/
			//_url(get_class($this)),
			_url($class),
			/*table*/
			'demo',
			/*col*/
			array('id', 'branch_id', 'account', 'account_id', 'level_id', 'name', 'bet_amount', 'set_amount', 'win_amount', 'hbet_amount', 'hset_amount', 'hwin_amount'),
			/*col_ch*/
			array('代碼', '分站', '帳號', '下線', '階層', '暱稱', '投注', '有效', '輸贏', 'h投注', 'h有效', 'h輸贏'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', '', 'demo2,account,id', 't_level,name,id', '', '', '', '', '', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-2 col-sm-2 col-xs-4': 'hidden',
				'col-md-2 col-sm-2 col-xs-4',
				'col-md-1 col-sm-1 col-xs-1',
				'hidden',
				'hidden',
				'col-md-3 col-sm-3 hidden-xs text-right',
				'col-md-3 col-sm-3 hidden-xs text-right',
				'col-md-3 col-sm-3 col-xs-8 text-right',
				'hidden',
				'hidden',
				'hidden',
			),
			/*select/radiobox/checkbox/text/password/textarea/autocomplete/datepicker */
			array('hidden', 'hidden', 'text', 'hidden', 'hidden', 'hidden', 'value', 'value', 'value', 'value', 'value', 'value'),
			/*authority check*/
			array(
				$_SESSION['auth']['report_review'] ?? 0,
				0,
				0,
				0,
			),
			/*medoo*/
			\Box::obj('db'),
			/*config*/
			array(
				'tree' => 3,
				'perpage' => 0,
				'root' => ($_SESSION['user']['cross'])? 0: $_SESSION['user']['root'],
				'sum' => ['bet_amount', 'set_amount', 'win_amount', 'hbet_amount', 'hset_amount', 'hwin_amount'],
				'modal-width' => '1020px',
				'module' => array(
					array(
						'url' => _url('detail_' . $game),
						'tag' => '投注明細',
						'sql' => ['member_id' => 'id', 'search_sdate' => 'search_sdate', 'search_edate' => 'search_edate'],
						'css' => 'height: 700px',
					),
				)
			)
		);
		
		$arr = $obj->decodeJson($_POST);

		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
				case 'getJson':
					//member_id = 9_xxxxx
					$tmp = explode('_', $obj->arg['where']['AND']['id']);
					$id = $tmp[1] ?? 0;
					if($tmp[1] ?? 0){
						$acc = \DB::table('t_member')->where('id', $tmp[1])->first()->account;
					}else{
						$acc = \DB::table('t_account')->where('id', $tmp[0])->first()->account;
					}
					echo json_encode(['code'=>0, 'data'=>[['id'=>$id, 'account'=> $acc]]]);
					exit;
					break;
				case 'review':
					$cus = $obj->arg['where']['SEARCH_CUS'] ?? '';
					
					$sdate = strtotime($cus['search_sdate'] ?? date('Y-m-d'));
					$edate = strtotime($cus['search_edate'] ?? date('Y-m-d'));
					switch($game){
						default:
							//預設
							break;
						case 'salon':
							$edate -= 86400; //中午制
							break;
					}
					
					$rc = $this->init($sdate, $edate, $game);
					$obj->bind($rc);

					echo $obj->{$obj->act}($obj->arg, function($r) use ($obj){
						$tpl = new \Yatp(__DIR__ . '/report.tpl');
						foreach($r['data'] as $k=>$v){
							$acc = explode(':', $v['account']); // 會員與遊戲帳號
							$r['data'][$k]['account'] = $obj->raw($acc[0] . '(' . $v['name'] . ')<br>' . (($acc[1] ?? 0)? $acc[1] . '<br>': '') . ($v['level_id']?: '會員'));
							
							if($v['bet_amount'] == 0){
								unset($r['data'][$k]);
								continue;
							}
							
							$bet_amount = number_format($v['bet_amount'], 1);
							$hbet_amount = number_format($v['hbet_amount'], 1);
							$set_amount = number_format($v['set_amount'], 1);
							$win_amount = number_format($v['win_amount'], 1);
							$hset_amount = number_format($v['hset_amount'], 1);
							$hwin_amount = number_format($v['hwin_amount'], 1);
							$r['data'][$k]['bet_amount'] = $obj->raw($tpl->block('col-hid-amt')->assign([
								'num' => $bet_amount,
								'hid-amt' => ($_SESSION['auth']['report_modify'] ?? 0)? '<br>' . $hbet_amount: '',
							])->render(false));
							$r['data'][$k]['set_amount'] = $obj->raw($tpl->block('col-hid-amt')->assign([
								'num' => $set_amount,
								'hid-amt' => ($_SESSION['auth']['report_modify'] ?? 0)? '<br>' . $hset_amount: '',
							])->render(false));
							$r['data'][$k]['win_amount'] = $obj->raw($tpl->block('numbers')->assign([
								'class' => ($v['win_amount'] > 0)? 'text-success': 'text-danger',
								'bet_amount' => '投注: ' . $bet_amount . '<br>',
								'set_amount' => '有效: ' . $set_amount . '<br>',
								'win_amount' => $win_amount . '<br>',
								'hwin_amount' => ($_SESSION['auth']['report_modify'] ?? 0)? $hwin_amount: '',
							])->render(false));
						}
						return $r;
					});
					exit;
					break;
				default:
					break;
			}
			
			//do the work
			echo $obj->{$obj->act}($obj->arg);
		}else{
			switch($game){
				default:
					$type = 1; //預設
					break;
				case 'salon':
					$type = 2; //中午制
					break;
				case 'supersport':
				case 'xinxin':
					$type = 3; //跨日
					break;
			}
			
			$tpl = new \Yatp(__DIR__ . '/report.tpl');
			
			$tmp = $tpl->block('search_date')->assign([
				'unique_id' => $obj->unique_id,
				'type' => $type,
			])->render(false);

			//sum
			$tpl->block('report-sums')->assign([
				'unique_id' => $obj->unique_id,
				'cols' => "['bet_amount', 'set_amount', 'win_amount']",
				'fix' => 1,
			])->render();

			$obj->render(['search' => $tmp]);
		}
		
		unset($obj);
		exit;
	}
	
	public function init($sdate, $edate, $game){
		$rc = \Box::obj('db')->query('
		SELECT
			SUM(bet_' . $game . ') as bet_amount,
			SUM(set_' . $game . ') as set_amount,
			SUM(win_' . $game . ') as win_amount,
			SUM(hbet_' . $game . ') as hbet_amount,
			SUM(hset_' . $game . ') as hset_amount,
			SUM(hwin_' . $game . ') as hwin_amount,
			member_id as id
		FROM t_report_cache
		WHERE bet_date >= ' . $sdate . ' AND bet_date <= ' . $edate . '
		GROUP BY member_id
		HAVING bet_amount > 0;
		')->fetchAll(\PDO::FETCH_ASSOC);
		
		$acc = \Box::obj('db')->select('t_account', ['id', 'account', 'account_id', 'name', 'level_id', 'branch_id'], ['branch_id[!]'=>1]);
		
		\Box::obj('db')->query('
		CREATE TEMPORARY TABLE `demo`(
		`id` VARCHAR(50) NOT NULL,
		`account` VARCHAR(50) NOT NULL,
		`account_id` INT NOT NULL,
		`name` VARCHAR(50) NOT NULL,
		`level_id` INT NOT NULL,
		`branch_id` INT NOT NULL,
		PRIMARY KEY(`id`))
		')->fetchAll(\PDO::FETCH_ASSOC);
		
		\Box::obj('db')->query('
		CREATE TEMPORARY TABLE `demo2`(
		`id` VARCHAR(50) NOT NULL,
		`account` VARCHAR(50) NOT NULL,
		`account_id` INT NOT NULL,
		`name` VARCHAR(50) NOT NULL,
		`level_id` INT NOT NULL,
		`branch_id` INT NOT NULL,
		PRIMARY KEY(`id`))
		')->fetchAll(\PDO::FETCH_ASSOC);
		
		$arr = [];
		$prefix = '9_';
		foreach($rc as $k=>$v){
			$arr[] = $v['id'];
			$rc[$k]['id'] = $prefix . $rc[$k]['id'];
		}
		$l = implode(',', $arr);
		$m = [];
		if($l){
			$m = \Box::obj('db')->query('
			SELECT m.id, CONCAT(m.account, \':\', a.account) as account, m.name, m.account_id, m.branch_id
			FROM t_member as m
			LEFT JOIN t_game_account as a
			ON a.member_id = m.id /*AND a.login_time != 0*/
			JOIN t_game as g
			ON g.id = a.game_id
			WHERE m.id IN (' . $l . ')
			AND g.game = \'' . $game . '\'
			GROUP BY m.id
			')->fetchAll(\PDO::FETCH_ASSOC);
			
			foreach($m as $k=>$v){
				$m[$k]['id'] = $prefix . $v['id'];
				$m[$k]['account'] = $v['account'];
			}
			// merge agent and member
			$insert = array_merge($m, $acc);
			
			\Box::obj('db')->insert('demo', $insert);
			\Box::obj('db')->insert('demo2', $insert);
		}
		
		return $rc;
	}
}