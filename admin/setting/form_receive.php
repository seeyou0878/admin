<?php
namespace Admin;

class Receive{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_receive',
			/*col*/
			array('id', 'branch_id', 'bank_id', 'account_no', 'title'),
			/*col_ch*/
			array('代碼', '分站', '銀行名稱', '帳號', '戶名'),
			/*empty check*/
			array(0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', 't_bank,name,id', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])?  'col-md-1 col-sm-1 hidden-xs':'hidden',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-2 col-sm-2 hidden-xs',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])?  'select':'hidden',
				'select', 'text', 'text'),
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
					if($_SESSION['user']['cross']){
						// pass
					}else{
						$obj->arg['data']['branch_id'] = $_SESSION['user']['branch_id'];
					}
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