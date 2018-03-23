<?php
namespace Admin;

class Bulletin{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_bulletin',
			/*col*/
			array('id', 'branch_id', 'cdate', 'title', 'content', 'status_id', 'udate'),
			/*col_ch*/
			array('代碼', '分站', '建立日期', '標題', '內容', '狀態', '修改日期'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', '', '', '', 't_status,alias,id', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs': 'hidden',
				'col-md-2 col-sm-2 hidden-xs hidden-create',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-2 col-sm-2 col-xs-4',
				'hidden'
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'datepicker,{"disabled": true}', 'text', 'editor', 'select', 'hidden'
			),
			/*authority check*/
			array(
				$_SESSION['auth']['style_admin'] ?? 0,
				$_SESSION['auth']['style_admin'] ?? 0,
				$_SESSION['auth']['style_admin'] ?? 0,
				$_SESSION['auth']['style_admin'] ?? 0,
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
					$obj->arg['data']['cdate'] = time();
					$obj->arg['data']['udate'] = time();
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