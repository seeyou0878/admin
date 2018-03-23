<?php
namespace Admin\Log;

class Login{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_log_login',
			/*col*/
			array('id', 'branch_id', 'cdate', 'account', 'ip', 'loc', 'title', 'status_id'),
			/*col_ch*/
			array('代碼', '分站', '時間', '帳號', 'IP', '國別', '內容', '狀態'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', '', '', '', '', '', 't_status,c2,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs': 'hidden',
				'col-md-3 col-sm-3 col-xs-4',
				'col-md-2 col-sm-2 col-xs-4 func',
				'col-md-3 col-sm-3 hidden-xs func',
				'col-md-1 col-sm-1 hidden-xs',
				'col-md-6 col-sm-6 hidden-xs',
				'col-md-1 col-sm-1 col-xs-4',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'datepicker,{"format": "Y-m-d H:i:s"}', 'text', 'text', 'text', 'text', 'select'),
			/*authority check*/
			array(
				$_SESSION['auth']['account_member'] ?? 0,
				0,
				0,
				0,
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
					
					echo $obj->{$obj->act}($obj->arg, function($r) use ($obj){
						foreach($r['data'] as $k=>$v){
							$r['data'][$k]['ip'] = $obj->raw('<a bind-search="' . $v['ip'] . '">' . $v['ip'] . '</a>');
							$r['data'][$k]['account'] = $obj->raw('<a bind-search="' . $v['account'] . '">' . $v['account'] . '</a>');
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
			$tpl = new \Yatp(__DIR__ . '/account.tpl');
			$tpl->block('search-ip')->render();
			$obj->render();
		}
		
		unset($obj);
		exit;
	}
}