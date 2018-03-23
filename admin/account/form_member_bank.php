<?php
namespace Admin;

class MemberBank{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_member_bank',
			/*col*/
			array('id', 'member_id', 'bank_id', 'account_no', 'account_name', 'pic', 'status_id'),
			/*col_ch*/
			array('代碼', '會員', '銀行名稱', '帳戶號碼', '戶名', '照片', '狀態'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_member,account,id', 't_bank,name,id', '', '', '', 't_status,alias,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'hidden',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-2 col-sm-2 hidden-xs',
				'col-md-2 col-sm-2 col-xs-4',
				'col-md-2 col-sm-2 hidden-xs',
				'col-md-2 col-sm-2 hidden-xs',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array('hidden', 'hidden', 'autocomplete', 'text', 'text', 'uploadfile,{"max": 15}', 'select'),
			/*authority check*/
			array(
				$_SESSION['auth']['account_review'] ?? 0,
				$_SESSION['auth']['account_create'] ?? 0,
				$_SESSION['auth']['account_modify'] ?? 0,
				$_SESSION['auth']['account_delete'] ?? 0,
			),
			/*medoo*/
			\Box::obj('db')
		);
		
		$obj->decodeJson($_POST);
		
		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
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