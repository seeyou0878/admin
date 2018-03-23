<?php
namespace Admin;

class CreditCard{
	function __construct(){
		new \Admin\Payment(2, get_class($this));
	}
}

class AeroPay{
	function __construct(){
		new \Admin\Payment(3, get_class($this));
	}
}

class Payment{
	
	function __construct($type=1, $class=null){
		
		$config = [
			1 => ['_merchant_id,廠商編號' => '', '_app_code' => '', '_hash_key' => '', '_hash_iv' => ''],
			2 => ['_url,API 網址' => '', '_merchant_id,廠商編號' => '', '_valid_key' => '', '_hash_key' => '', '_hash_iv' => '', '_percent,手續費(%)' => ''],
			3 => ['_merchant_id,廠商編號' => ''],
		];
		
		$obj = new \Yapa(
			/*file*/
			_url($class?:get_class($this)),
			/*table*/
			't_payment',
			/*col*/
			array('id', 'branch_id', 'title', 'type', 'config', 'status_id', 'cdate', 'udate'),
			/*col_ch*/
			array('代碼', '分站', '標題', '類型', '設定', '狀態', '新增日期', '修改日期'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', '', '', '', 't_status,alias,id', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs': 'hidden',
				'col-md-3 col-sm-3 col-xs-4',
				'hidden',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-2 col-sm-2 col-xs-4',
				'col-md-2 col-sm-2 col-xs-4 hidden-xs hidden-create',
				'col-md-2 col-sm-2 col-xs-4 hidden-xs hidden-create',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'text', 'hidden', 'json', 'select',
				'datepicker,{"format": "Y-m-d H:i:s", "disabled": true}',
				'datepicker,{"format": "Y-m-d H:i:s", "disabled": true}',
			),
			/*authority check*/
			array(
				$_SESSION['auth']['setting_review'] ?? 0,
				$_SESSION['auth']['setting_create'] ?? 0,
				$_SESSION['auth']['setting_modify'] ?? 0,
				$_SESSION['auth']['setting_delete'] ?? 0,
			),
			/*medoo*/
			\Box::obj('db'),
			/*config*/
			array(
				'preset' => array(
					'config' => $config[$type] ?? [],
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
					$obj->arg['where']['AND']['type'] = $type;
					break;
				case 'create':
					$obj->arg['data']['cdate'] = time();
					$obj->arg['data']['type'] = $type;
				case 'modify':
					if($_SESSION['user']['cross']){
						// pass
					}else{
						$obj->arg['data']['branch_id'] = $_SESSION['user']['branch_id'];
					}
					$obj->arg['data']['udate'] = time();
					$obj->arg['data']['type'] = $type;
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