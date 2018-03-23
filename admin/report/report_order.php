<?php
namespace Admin\Report;

class Order{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			'demo',
			/*col*/
			array('id', 'branch_id', 'account', 'account_id', 'level_id', 'name', 'store1', 'store2', 'store3', 'store4', 'sum1', 'extra', 'withdraw', 'bonus', 'sum2'),
			/*col_ch*/
			array('代碼', '分站', '帳號', '下線', '階層', '暱稱', 'ATM(a)', '7-11(b)', '其他超商(c)', '信用卡(d)', '儲值小計(e = a+b+c+d)', '手續費(f)', '點數提領(g)', '紅利贈點(h)', '合計(e+f-g-h)'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', '', 'demo2,account,id', 't_level,name,id', '', '', '', '', '', '', '', '', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-2 col-sm-2 col-xs-4 func': 'hidden',
				'col-md-3 col-sm-3 col-xs-3',
				'col-md-1 col-sm-1 col-xs-1',
				'hidden',
				'hidden',
				'col-md-2 hidden-md hidden-xs text-right func',
				'col-md-2 hidden-md hidden-xs text-right func',
				'col-md-2 hidden-md hidden-xs text-right func',
				'col-md-2 hidden-md hidden-xs text-right func',
				'col-md-2 col-sm-2 hidden-xs text-right func',
				'col-md-2 col-sm-2 hidden-xs text-right func',
				'col-md-2 col-sm-2 hidden-xs text-right func',
				'col-md-2 col-sm-2 hidden-xs text-right func',
				'col-md-2 col-sm-2 col-xs-4 text-right func',
			),
			/*select/radiobox/checkbox/text/password/textarea/autocomplete/datepicker */
			array('hidden', 'hidden', 'text', 'hidden', 'hidden', 'hidden', 'value', 'value', 'value', 'value', 'value', 'value', 'value', 'value', 'value'),
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
				'sum' => ['store1', 'store2', 'store3', 'store4', 'withdraw', 'bonus', 'extra', 'sum1', 'sum2'],
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
				case 'review':
					$cus = $obj->arg['where']['SEARCH_CUS'];
					
					$sdate = strtotime($cus['search_sdate'] ?? date('Y-m-d'));
					$edate = (strtotime($cus['search_edate'] ?? date('Y-m-d')) + 86399);
					
					$rc = $this->init($sdate, $edate);
					foreach($rc as $k=>$v){
						$rc[$k]['sum1'] = $rc[$k]['store1'] + $rc[$k]['store2'] + $rc[$k]['store3'] + $rc[$k]['store4'];
						$rc[$k]['sum2'] = $rc[$k]['sum1'] + $rc[$k]['extra'] - $rc[$k]['withdraw'] - $rc[$k]['bonus'];
					}
					$obj->bind($rc);
					
					echo $obj->{$obj->act}($obj->arg, function($r) use ($obj){
						$tpl = new \Yatp(__DIR__ . '/report.tpl');
						
						foreach($r['data'] as $k=>$v){
							$acc = explode(':', $v['account']); // 會員與遊戲帳號
							$r['data'][$k]['account'] = $obj->raw($v['account'] . '(' . $v['name'] . ')<br>' . ($v['level_id']?: '會員'));
						
							if($v['sum1'] == 0 && $v['extra'] == 0 && $v['withdraw'] == 0  && $v['bonus'] == 0){
								unset($r['data'][$k]);
								continue;
							}
							
							$r['data'][$k]['store1'] = number_format($v['store1']);
							$r['data'][$k]['store2'] = number_format($v['store2']);
							$r['data'][$k]['store3'] = number_format($v['store3']);
							$r['data'][$k]['store4'] = number_format($v['store4']);
							$r['data'][$k]['sum1'] = number_format($v['sum1']);
							$r['data'][$k]['extra'] = number_format($v['extra']);
							$r['data'][$k]['withdraw'] = number_format($v['withdraw']);
							$r['data'][$k]['bonus'] = number_format($v['bonus']);
							$r['data'][$k]['sum2'] = $obj->raw($tpl->block('numbers')->assign([
								'class' => ($v['sum2'] > 0)? 'text-success': 'text-danger',
								'win_amount' => number_format($v['sum2']),
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
			
			//sum
			$tpl->block('report-sums')->assign([
				'unique_id' => $obj->unique_id,
				'cols' => "['store1', 'store2', 'store3', 'store4', 'sum1', 'extra', 'withdraw', 'bonus', 'sum2']",
				'fix' => 1,
			])->render();
			
			$obj->render(['search' => $tmp]);
		}
		
		unset($obj);
		exit;
	}
	
	public function init($sdate, $edate){
		$rc = \Box::obj('db')->query('
		SELECT
			SUM(IF(`type_id` = 1 AND `src_id` IN (1, 2),`total`, 0)) AS store1, /* ATM 儲值  */
			SUM(IF(`type_id` = 1 AND `src_id` = 4, `total`, 0)) AS store2,      /* 7-11 儲值 */
			SUM(IF(`type_id` = 1 AND `src_id` = 3, `total`, 0)) AS store3,      /* 超商儲值 */
			SUM(IF(`type_id` = 1 AND `src_id` = 5, `total` - `extra`, 0)) AS store4, /* 信用卡儲值 */
			SUM(IF(`type_id` = 4 AND `src_id` = 1, `total`, 0)) AS bonus,       /* 紅利 */
			SUM(IF(`type_id` = 3, `total`, 0)) AS withdraw,                     /* 提領 */
			SUM(IF(`type_id` = 3, `extra`, 0)) AS extra,                        /* 手續費 */
			member_id as id
		FROM t_order
		WHERE udate >= ' . $sdate . ' AND udate <= ' . $edate . '
			AND `status_id` = 2 /* 已處理 */
			AND `type_id` != 2  /* 排除轉移單 */
		GROUP BY member_id
		;
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
			SELECT id, account, account_id, name, branch_id FROM t_member WHERE id IN (' . $l . ')
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