<?php
namespace Admin;

class Ban{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_ban',
			/*col*/
			array('id', 'branch_id', 'type_id', 'target', 'cdate', 'udate', 'last'),
			/*col_ch*/
			array('代碼', '分站', '類型', '目標,銀行帳戶格式: 700-123456789', '建立日期', '修改日期', '最後修改'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', 't_ban_type,alias,id', '', '', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs': 'hidden',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-3 col-sm-3 hidden-xs hidden-create',
				'hidden hidden-create',
				'col-md-3 col-sm-3 hidden-xs hidden-create',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'select', 
				'text', 
				'datepicker,{"format": "Y-m-d H:i:s", "disabled": true}', 
				'datepicker,{"format": "Y-m-d H:i:s", "disabled": true}', 
				'text,{"disabled": true}',
			),
			/*authority check*/
			array(
				$_SESSION['auth']['setting_review'] ?? 0,
				$_SESSION['auth']['setting_create'] ?? 0,
				$_SESSION['auth']['setting_modify'] ?? 0,
				$_SESSION['auth']['setting_delete'] ?? 0,
			),
			/*medoo*/
			\Box::obj('db')
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
					break;
				case 'create':
					$obj->arg['data']['cdate'] = time();
				case 'modify':
					if($_SESSION['user']['cross']){
						// pass
					}else{
						$obj->arg['data']['branch_id'] = $_SESSION['user']['branch_id'];
					}
					//type is bank & not in format like 700-123456789
					if($obj->arg['data']['type_id'] == 3 && \App::make('\Lib\Invalid')->bank_acc_format($obj->arg['data']['target'])){
						echo json_encode(['code' => 1, 'text' => '銀行帳戶格式: 700-123456789']);
						exit;
					}

					$obj->arg['data']['udate'] = time();
					$obj->arg['data']['last'] = $_SESSION['user']['account'] ?? '';
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