<?php
namespace Admin;

class Event{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_event',
			/*col*/
			array('id', 'branch_id', 'cdate', 'icon', 'content', 'status_id'),
			/*col_ch*/
			array('代碼', '分站', '建立日期', '圖示', '內容', '狀態'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', '', '', '', 't_status,alias,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs': 'hidden',
				'col-md-2 col-sm-2 col-xs-4 hidden-create',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-3 hidden-sm hidden-xs',
				'col-md-2 col-sm-2 col-xs-4',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'datepicker,{"disabled": true}', 'uploadfile', 'editor', 'select'),
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