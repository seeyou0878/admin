<?php
namespace Admin\Report;

class All{
	
	function __construct(){

		$games = \DB::table('t_game')
			->where('game', '!=', 'Wallet')
			->where('status_id',1)
			->get();

		$arr = [];
		foreach($games as $g){
			$name = strtolower($g->game);
			$arr[] = [
				'cols' => ["{$name}_bet", "{$name}_hbet", "{$name}_win", "{$name}_hwin"],
				'col_ch' => ["{$name}投注","{$name}h投注","{$name}輸贏","{$name}h輸贏",],
				'empty_check' => [0, 0, 0, 0],
				'dup_check' => [0, 0, 0, 0],
				'join' => ['', '' ,'', ''],
				'grid_css' => ['hidden', 'hidden', 'hidden', 'hidden'],
				'input' => ['value', 'value' ,'value', 'value'],
			];
		}
		$cols = [];
		foreach($arr as $a){
			$cols = array_merge_recursive($cols, $a);
		}
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			'demo',
			/*col*/
			array_merge(array('id', 'branch_id', 'account', 'account_id', 'level_id', 'name', 'win_detail', 'bet_total', 'win_total'), $cols['cols']),
			/*col_ch*/
			array_merge(array('代碼', '分站', '帳號', '下線', '階層', '暱稱', '輸贏明細', '總投注', '總輸贏'), $cols['col_ch']),
			/*empty check*/
			array_merge(array(0, 0, 0, 0, 0, 0, 0, 0, 0), $cols['empty_check']),
			/*exist(duplicate) check*/
			array_merge(array(0, 0, 0, 0, 0, 0, 0, 0, 0), $cols['dup_check']),
			/*chain(join) check (table, content, id)*/
			array_merge(array('', 't_branch,name,id', '', 'demo2,account,id', 't_level,name,id', '', '', '', ''), $cols['join']),
			/*show bootstrap grid class*/
			array_merge(
				array(
					'hidden', 
					($_SESSION['user']['cross'])? 'col-md-2 col-sm-2 col-xs-2 func': 'hidden', 
					'col-md-2 col-sm-2 col-xs-2 func', 
					'col-md-1 col-sm-1 col-xs-1', 
					'hidden', 
					'hidden', 
					'col-md-3 col-sm-3 hidden-xs func', 
					'col-md-2 col-sm-2 col-xs-2 text-right func', 
					'col-md-2 col-sm-2 col-xs-2 text-right func', 
				),
				$cols['grid_css']
			),
			/*select/radiobox/checkbox/text/password/textarea/autocomplete/datepicker */
			array_merge(array('hidden', 'hidden', 'text', 'hidden', 'hidden', 'hidden', 'value', 'value', 'value'), $cols['input']),
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
				'sum' => $cols['cols'],
			)
		);
		
		$arr = $obj->decodeJson($_POST);

		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
				case 'review':
					ini_set('memory_limit', '1024M');
					$cus = $obj->arg['where']['SEARCH_CUS'] ?? '';
					
					$sdate = strtotime($cus['search_sdate'] ?? date('Y-m-d'));
					$edate = strtotime($cus['search_edate'] ?? date('Y-m-d'));
					
					$rc = $this->init($sdate, $edate);
					$obj->bind($rc);
					
					echo $obj->{$obj->act}($obj->arg, function($r) use ($obj){
						
						$tpl = new \Yatp(__DIR__ . '/report.tpl');
						$games = \DB::table('t_game')
							->where('game', '!=', 'Wallet')
							->where('status_id',1)
							->get();
							
						//each row of data
						foreach($r['data'] as $k=>$v){
							$bet_total = 0;
							$hbet_total = 0;
							$win_total = 0;
							$hwin_total = 0;
							$win_detail = '';

							//do not display if row is empty(0 for all game).
							foreach ($games as $g) {
								$name = strtolower($g->game);
								$win_total += $v["{$name}" . '_win'];
							}
							if($win_total == 0){
								unset($r['data'][$k]);
								continue;
							}
							
							$r['data'][$k]['account'] = $obj->raw($v['account'] . '(' . $v['name'] . ')<br>' . ($v['level_id']?: '會員'));

							//make win detail column
							$win_detail .= '<table class="table small table-report-all" style="background-color: transparent; margin: 0;">';
							foreach ($games as $g) {
								$name = strtolower($g->game);
								$bet = number_format($v["{$name}" . '_bet'], 1);
								$hbet = number_format($v["{$name}" . '_hbet'], 1);
								$win = number_format($v["{$name}" . '_win'], 1);
								$hwin = number_format($v["{$name}" . '_hwin'], 1);
								
								$bet_total += $v["{$name}" . '_bet'];
								$hbet_total += $v["{$name}" . '_hbet'];
								$hwin_total += $v["{$name}" . '_hwin'];

								$win_detail .= $tpl->block('report-all-win-detail')->assign([
									'class' => ($v["{$name}" . '_win'] > 0)? 'text-success': 'text-danger',
									'game' => $g->name,
									'num' => $win,
									'hid-amt' => ($_SESSION['auth']['report_modify'] ?? 0)? '<br>' . $hwin . '<br>': '',
								])->render(false);
							}
							$win_detail .= '</table>';

							//replace win_detail, bet_total and win_total columns
							$bet_total = number_format($bet_total, 1);
							$hbet_total = number_format($hbet_total, 1);
							$win_total = number_format($win_total, 1);
							$hwin_total = number_format($hwin_total, 1);
							$r['data'][$k]['win_detail'] = $obj->raw($win_detail);
							$r['data'][$k]['bet_total'] = $obj->raw($tpl->block('col-hid-amt')->assign([
								'num' => $bet_total,
								'hid-amt' => ($_SESSION['auth']['report_modify'] ?? 0)? '<br>' . $hbet_total : '',
							])->render(false));
							$r['data'][$k]['win_total'] = $obj->raw($tpl->block('numbers')->assign([
								'class' => ($win_total > 0)? 'text-success': 'text-danger',
								'bet_amount' => '',
								'win_amount' => $win_total,
								'hwin_amount' => ($_SESSION['auth']['report_modify'] ?? 0)? '<br>' . $hwin_total : '',
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
			$tpl = new \Yatp(__DIR__ . '/report.tpl');
			
			$tmp = $tpl->block('search_date')->assign([
				'unique_id' => $obj->unique_id,
				'type' => 1,
			])->render(false);

			//css style
			$tpl->block('report-all-style')->render();

			//sum
			$tpl->block('report-sums')->assign([
				'unique_id' => $obj->unique_id,
				'cols' => "['bet_total', 'win_total']",
				'fix' => 2,
			])->render();
			
			$obj->render(['search' => $tmp]);
		}
		
		unset($obj);
		exit;
	}
	
	//hierarchy stuff
	public function init($sdate, $edate){
		$games = \DB::table('t_game')
			->where('game', '!=', 'Wallet')
			->where('status_id',1)
			->get();

		$select = '';
		foreach ($games as $game) {
			$name = strtolower($game->game);
			$select .= 'SUM(bet_' . $name . ') as ' . $name . '_bet,'.
						'SUM(win_' . $name . ') as ' . $name . '_win,'.
						'SUM(hbet_' . $name . ') as ' . $name . '_hbet,'.
						'SUM(hwin_' . $name . ') as ' . $name . '_hwin,';
		}

		$rc = \Box::obj('db')->query("
		SELECT
			{$select}
			member_id as id
		FROM t_report_cache
		WHERE bet_date >= " . $sdate . ' AND bet_date <= ' . $edate . '
		GROUP BY member_id
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
			SELECT id, account, name, account_id, branch_id
			FROM t_member
			WHERE id IN (' . $l . ')
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