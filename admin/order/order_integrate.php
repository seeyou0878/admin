<?php
namespace Admin\Order;

class Integrate{
	
	function __construct(){
		$src_id_txt = '<strong>提領/轉移:</strong> <br> 1: 電子錢包 2: 體育博彩3: 歐博真人 4: 黃金俱樂部 5: 微妙電子 6: 康博電子 7: 黃金亞洲 8: 遊聯電子 9:沙龍真人 <br> <strong>儲值:</strong> <br> 1: 指定收款 2: ATM 3: 超商繳款(全家/萊爾富/OK) 4: 超商繳款(7-11) <br> <strong>紅利:</strong> <br> 1: 紅利贈點 2: 人工補點 3: 其他';

		$src_status_txt = '<strong>儲值:</strong> <br> 1: 未付款2: 已付款 <br> <strong>提領/轉移/紅利:</strong> <br> 1: 待處理 2: 已處理 3: 未完成 4: 取消';

		$tar_id_txt = '<strong>儲值/轉移/紅利:</strong> <br> 1: 電子錢包 2: 體育博彩3: 歐博真人 4: 黃金俱樂部 5: 微妙電子 6: 康博電子 7: 黃金亞洲 8: 遊聯電子 9:沙龍真人';
		

		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_order',
			/*col*/
			array('id', 'branch_id', 'type_id', 'order_uid', 'cdate', 'member_id', 'total', 'extra', 'src_id', 'src_text', 'src_value', 'src_status', 'tar_id', 'tar_text', 'tar_value', 'tar_status', 'status_id', 'alert', 'remark', 'last', 'udate'),
			/*col_ch*/
			array('代碼', '分站', '類型', '訂單編號', '建立時間', '會員', '金額', '手續費', '付款/來源資訊 ' . $src_id_txt,
			 '來源說明', '來源餘額', '來源狀態 ' . $src_status_txt, '目的資訊 ' . $tar_id_txt, '目的說明', '目的餘額', '目的狀態', '訂單狀態', '警告', '備註', '最後處理', '處理時間'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array(
				'',
				't_branch,name,id',
				't_order_type,name,id',
				'',
				'',
				't_member,account,id' . ($_SESSION['user']['cross']? '': ',{"branch_id":' . $_SESSION['user']['branch_id'] . '}'),
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				't_order_status,alias,id,{"id[!]": 3}',
				't_order_status,alias,id,{"id[!]": 3}',
				'',
				'',
				'',
				'',
			),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs': 'hidden',
				'hidden',
				'col-md-3 col-sm-3 col-xs-8 hidden-create',
				'hidden',
				'col-md-3 hidden-sm hidden-xs func',
				'col-md-2 col-sm-2 col-xs-4 text-right',
				'hidden',
				'col-md-3 col-sm-3 hidden-xs',
				'hidden',
				'hidden',
				'hidden',
				'col-md-3 col-sm-3 hidden-xs',
				'hidden hidden-create',
				'hidden',
				'hidden hidden-create',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'col-md-3 hidden-sm hidden-xs hidden-create',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'hidden', 'text,{"disabled": true}', 'hidden', 'autocomplete', 'text', 'text', 'text', 'text', 'text', 'text', 'text', 'text', 'text', 'select', 'select', 'text', 'textarea', 'hidden', 'hidden'
			),
			/*authority check*/
			array(
				$_SESSION['auth']['order_review'] ?? 0,
				0,
				$_SESSION['auth']['order_admin'] ?? 0,
				0,
			),
			/*medoo*/
			\Box::obj('db'),
			/*config*/
			array(
				'module' => array(
					array(
						'url' => _url('form_message'),
						'tag' => '站內信',
						'sql' => array('member_id' => 'member_id'),
					),
					array(
						'url' => _url('form_sms'),
						'tag' => '簡訊',
						'sql' => array('member_id' => 'member_id'),
					)
				)
			)
		);
		
		$obj->decodeJson($_POST);
		
		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
				case 'review':
					//determine member_id(searched name from 1 branch or all branch)
					$this->process_search_params($obj);
					$member_id = $obj->arg['where']['AND']['member_id'] ?? null;

					//retrive search params
					$cus = $obj->arg['where']['SEARCH_CUS'] ?? '';
					$sdate = strtotime($cus['search_sdate'] ?? '') ?? null;
					$edate = strtotime($cus['search_edate'] ?? '') ?? null;
					$status = $cus['search_status'] ?? '';
					$type = $cus['search_type'] ?? '';

					//set query search params
					if($sdate)
						$obj->arg['where']['AND']['udate[>=]'] = $sdate;
					if($edate)
						$obj->arg['where']['AND']['udate[<=]'] = $edate + 86399;
					if($status)
						$obj->arg['where']['AND']['status_id'] = $status;
					if($type)
						$obj->arg['where']['AND']['type_id'] = $type;
					if($member_id)
						$obj->arg['where']['AND']['member_id'] = $member_id;

					//get data for custom format
					$r = $obj->getData($obj->arg);

					//retrieve member remark
					$arr_remarks = self::get_member_remarks($r['data']);

					$d = [];

					$tpl = new \Yatp(__DIR__ . '/order.tpl');

					//customize format of each row
					foreach($r['data'] ?? [] as $k=>$v){
						$this->type = $v['type_id'];
						
						//訂單編號
						$html1 = $tpl->block('default_order_uid')->assign([
							'type_id' => $this->normalize('type_id', $v),
							'order_uid' => $v['order_uid'],
							'cdate' => date('Y-m-d H:i:s', $v['cdate']),
						])->render(false);

						//金額
						if($this->type == 'store' || $this->type == 'withdraw'){
							$html2 = $tpl->block('withdraw_total')->assign([
								'total' => number_format($v['total']),
								'extra' => number_format($v['extra']),
								'actual' => number_format($v['total'] - $v['extra']),
								])->render(false);
						}else{
							$html2 = number_format($v['total']);
						}

						//付款/來源資訊
						$html3 = $tpl->block($this->type . '_src')->assign([
							'src_id' => $this->normalize('src_id', $v),
							'src_text' => $this->get_src_text($this->type, $v),
							'src_status' => $this->normalize('src_status', $v),
							'src_value' => number_format($v['src_value'], 2), 
						])->render(false);

						//目的資訊
						$html4 = $tpl->block($this->type . '_tar')->assign([
							'tar_id' => $this->normalize('tar_id', $v),
							'tar_status' => $v['tar_status'],
							'tar_value' => number_format($v['tar_value'], 2),
							'tar_text' => $this->get_tar_text($this->type, $v),
						])->render(false);

						//會員
						$html5 = self::create_member_col($tpl, $v, $arr_remarks);
						//處理時間
						$html6 = self::create_status_col($tpl, $v, $arr_remarks);

						$tmp = array(
							'id' => $v['id'],
							'order_uid' => $obj->raw($html1),
							'total'  => $obj->raw($html2),
							'src_id' => $obj->raw($html3),
							'tar_id' => $obj->raw($html4),
							'member_id' => $obj->raw($html5),
							'udate' => $obj->raw($html6),
						);
						$d[] = $tmp;
					}
					
					$obj->bind($d);
					
					break;
				case 'exec': //refresh credits display via ajax request
					$this->process_search_params($obj);
					$member_id = $obj->arg['where']['AND']['member_id'] ?? null;

					if($member_id){
						$account = $obj->arg['data']['search_account'];
						$sdate = strtotime($obj->arg['data']['search_sdate'])?: '';
						$edate = strtotime($obj->arg['data']['search_edate'])?: '';
						$status = $obj->arg['data']['search_status'] ?? null;
						$type = $obj->arg['data']['search_type'] ?? null;

						if ($status == 2 || $status == null) {
							$creds = \App::make('Lib\Mix')->get_order_info($member_id, $sdate, $edate, $branch_id ?? null, $type);
						}else{
							$creds = null;
						}
						echo json_encode(['code' => 0, 'data' => $creds]);
					}else{
						echo json_encode(['code' => 1, 'data' => 'no user found']);
					}
					exit;
					break;
				default:
					break;
			}
			
			//do the work
			echo $obj->{$obj->act}($obj->arg);
		}else{
			$search = \Request::get('search');
			$tpl = new \Yatp(__DIR__ . '/order.tpl');
			$tmp = $tpl->block('search_date')->assign([
				'unique_id' => $obj->unique_id,
				'type' => 1,
				'url' => $obj->file,
				'search' => $search,
			])->render(false);
			
			$obj->render(['search' => $tmp]);
		}
		
		unset($obj);
		exit;
	}

	private function process_search_params($obj){
		$account = $obj->arg['where']['SEARCH_CUS']['search_account'] ?? $obj->arg['data']['search_account'] ?? '';

		if($_SESSION['user']['cross']){
			//admin, get member from all branches
			$member_id = \DB::table('t_member')
				->select('id')
				->where('account', $account)
				->get()->toArray() ?? [];
			$member_id = array_pluck($member_id, 'id');
		}else{
			//branch,
			$branch_id = $_SESSION['user']['branch_id'];
			$obj->arg['where']['AND']['branch_id'] = $branch_id;

			//get member from this branch
			$member_id = \DB::table('t_member')
				->select('id')
				->where('account', $account)
				->where('branch_id', $branch_id)
				->first()->id ?? 0;
		}
		if($account){
			$obj->arg['where']['AND']['member_id'] = $member_id;
		}
	}


	/*Column building methods*/

	public static function create_member_col($tpl, $v, $arr_remarks){
		$rtn = $tpl->block('member')->assign([
			'icon' => ($v['alert'])? '': 'hidden',
			'remark' => $arr_remarks[$v['id']]['remark'] ?? '',
			'member_account' => $v['member_id'],
			'member_name' => $arr_remarks[$v['id']]['name'] ?? '',
			'agent_account' => $arr_remarks[$v['id']]['agent_account'] ?? '',
			'agent_name' => $arr_remarks[$v['id']]['agent_name'] ?? '',
		])->render(false);

		return $rtn;
	}
	
	//處理時間
	public static function create_status_col($tpl, $v, $arr_status){
		
		$class = ['', 'label-primary', 'label-success', 'label-danger', 'label-default'];
		
		$rtn = $tpl->block('status')->assign([
			'class' => $class[$arr_status[$v['id']]['status_id'] ?? 0] ?? '',
			'status_id' => $v['status_id'],
			'last' => $v['last'],
			'udate' => date('Y-m-d H:i:s', $v['udate']),
		])->render(false);

		return $rtn;
	}

	public static function get_member_remarks($data){
		$member_remarks = \DB::table('t_order')
			->join('t_member', 't_order.member_id', 't_member.id')
			->join('t_account', 't_member.account_id', 't_account.id')
			->select('t_order.id', 't_order.status_id', 't_member.remark', 't_member.name', 't_account.account as agent_account', 't_account.name as agent_name')
			->whereIn('t_order.id', array_pluck($data, 'id'))
			->get();
		$arr = [];
		foreach($member_remarks as $v){
			$arr[$v->id] = [
				'name' => $v->name,
				'agent_account' => $v->agent_account,
				'agent_name' => $v->agent_name,
				'remark' => $v->remark,
				'status_id' => $v->status_id,
			];
		}
		return $arr;
	}

	private function get_src_text($type, $v){
		if($type == 'transfer' || $type == 'withdraw'){
			return $v['src_text']? '-' . $v['src_text']: '';
		}else{
			return $v['src_text'];
		}
	}
	private function get_tar_text($type, $v){
		if($type == 'transfer' || $type == 'store' || $type == 'bonus'){
			return $v['tar_text']? '-' . $v['tar_text']: '';
		}else{
			return $v['tar_text'];
		}
	}
	private function normalize($col, $v){
		if(empty($this->arr_srcs)){
			$banks = \DB::table('t_bank')->get();
			$store_srcs = \DB::table('t_order_store_src')->get();
			$games = \DB::table('t_game')->get();
			$bonus_srcs = \DB::table('t_order_bonus_src')->get();
			$paid = \DB::table('t_order_paid')->get();
			$status = \DB::table('t_order_status')->get();
			$order_type = \DB::table('t_order_type')->get();
			$srcs = ['banks' => $banks, 'store_srcs' => $store_srcs, 'games' => $games, 'bonus_srcs' => $bonus_srcs, 'paid' => $paid, 'status' => $status, 'type' => $order_type];
			$this->arr_srcs = [];
			foreach ($srcs as $k=>$src) {
				$arr = [];
				foreach ($src as $item) {
					if($k == 'banks' || $k == 'games'){
						$arr[$item->id] = $item->name;
					}else if($k == 'type'){
						$arr[$item->name] = $item->alias;
					}else{
						$arr[$item->id] = $item->alias;
					}	
				}
				$this->arr_srcs[$k] = $arr;
			}
		}

		$src = $this->get_src($col);
		return $this->arr_srcs[$src][$v[$col]] ?? '';
	}
	private function get_src($col){
		$type = $this->type;
		if ($col == 'type_id') {
			return 'type';
		}
		if ($col == 'src_status') {
			switch ($type) {
				case 'store':
					return 'paid';
				default:
					return 'status';
			}
		}

		if ($col == 'src_id') {
			switch ($type) {
				case 'store':
					return 'store_srcs';
				case 'bonus':
					return 'bonus_srcs';
			}
		}
		if($col == 'tar_id'){
			switch ($type) {
				case 'withdraw':
					return 'banks';
			}
		}

		return 'games';
	}
}