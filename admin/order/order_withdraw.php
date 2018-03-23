<?php
namespace Admin\Order;

class Withdraw{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_order',
			/*col*/
			array('id', 'branch_id', 'type_id', 'order_uid', 'cdate', 'member_id', 'total', 'extra', 'src_id', 'src_text', 'src_value', 'src_status', 'tar_id', 'tar_text', 'tar_value', 'tar_status', 'status_id', 'alert', 'remark', 'last', 'udate'),
			/*col_ch*/
			array('代碼', '分站', '類型', '訂單編號', '建立時間', '會員', '金額', '手續費', '提領目標', '提領帳號', '提領餘額', '遊戲扣點', '匯款目標', '匯款帳號', '匯款餘額', '匯款狀態', '狀態', '警告', '備註', '最後處理', '處理時間'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array(
				'',
				't_branch,name,id',
				't_order_type,alias,id',
				'',
				'',
				't_member,account,id' . ($_SESSION['user']['cross']? '': ',{"branch_id":' . $_SESSION['user']['branch_id'] . '}'),
				'',
				'',
				't_game,name,id,{"game[!]": "Wallet"}',
				'',
				'',
				't_order_status,alias,id,{"id[!]": 3}',
				't_bank,name,id',
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
				'col-md-3 col-sm-3 col-xs-6 hidden-create',
				'hidden',
				'col-md-3 hidden-sm hidden-xs func',
				'col-md-2 col-sm-3 col-xs-6 text-right',
				'hidden hidden-create',
				'col-md-3 col-sm-3 hidden-xs',
				'hidden',
				'hidden',
				'hidden hidden-create',
				'col-md-3 col-sm-3 hidden-xs hidden-create',
				'hidden hidden-create',
				'hidden',
				'hidden hidden-create',
				'hidden hidden-create',
				'hidden',
				'hidden',
				'hidden',
				'col-md-3 hidden-sm hidden-xs hidden-create',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'hidden', 'text,{"disabled": true}', 'hidden', 'autocomplete', 'text', 'text', 'select', 'hidden', 'hidden', 'select', 'select,{"disabled": true}', 'text,{"disabled": true}', 'hidden', 'hidden', 'select', 'text', 'textarea', 'hidden', 'hidden'
			),
			/*authority check*/
			array(
				$_SESSION['auth']['order_review'] ?? 0,
				$_SESSION['auth']['order_create'] ?? 0,
				$_SESSION['auth']['order_modify'] ?? 0,
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
					if($_SESSION['user']['cross']){
						// pass
					}else{
						$obj->arg['where']['AND']['branch_id'] = $_SESSION['user']['branch_id'];
					}
					$obj->arg['where']['AND']['type_id'] = 3;
					
					//custom format
					$r = $obj->getData($obj->arg);

					//retrieve member remark
					$arr_remarks = Integrate::get_member_remarks($r['data']);

					$d = [];

					$tpl = new \Yatp(__DIR__ . '/order.tpl');
					foreach($r['data'] ?? [] as $k=>$v){
						
						$html1 = $tpl->block('default_order_uid')->assign([
							'type_id' => $v['type_id'],
							'order_uid' => $v['order_uid'],
							'cdate' => date('Y-m-d H:i:s', $v['cdate']),
						])->render(false);

						$html2 = $tpl->block('withdraw_total')->assign([
							'total' => number_format($v['total']),
							'extra' => number_format($v['extra']),
							'actual' => number_format($v['total'] - $v['extra']),
							])->render(false);

						$html3 = $tpl->block('withdraw_src')->assign([
							'src_id' => $v['src_id'],
							'src_text' => $v['src_text']? '-' . $v['src_text']: '',
							'src_value' => number_format($v['src_value'], 2),
							'src_status' => $v['src_status'],
						])->render(false);

						$html4 = $tpl->block('withdraw_tar')->assign([
							'tar_id' => $v['tar_id'],
							'tar_text' => $v['tar_text'],
						])->render(false);

						//會員
						$html5 = Integrate::create_member_col($tpl, $v, $arr_remarks);
						//狀態
						$html6 = Integrate::create_status_col($tpl, $v, $arr_remarks);

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
				case 'create':
					$order = \App::make('Lib\Order')->withdraw($obj->arg['data']);
					echo json_encode($order);
					exit;
					
					break;
				case 'modify':
					$order = \App::make('Lib\Order')->withdraw($obj->arg['data']);
					echo json_encode($order);
					exit;
					
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