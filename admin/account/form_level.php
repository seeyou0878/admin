<?php
namespace Admin;

class Level{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_level',
			/*col*/
			array('id', 'name', 'level', 'auth'),
			/*col_ch*/
			array('代碼', '稱號', '階層', '權限'),
			/*empty check*/
			array(0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', '', '', 't_auth,alias,id'),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-2 col-sm-2 col-xs-6',
				'hidden',
				'col-md-10 col-sm-10 col-xs-6',
			),
			/*select/radiobox/checkbox/text/password/textarea/autocomplete/datepicker */
			array(
				'hidden',
				'text',
				'hidden',
				'checkbox',
			),
			/*authority check*/
			array(
				$_SESSION['auth']['account_admin'] ?? 0,
				0,
				$_SESSION['auth']['account_admin'] ?? 0,
				0,
			),
			/*medoo*/
			\Box::obj('db')
		);
		
		$arr = $obj->decodeJson($_POST);

		if(!empty($obj->act)){
			//additional settings
			switch($obj->act){
				case 'review':
					break;
					
				case 'create':
					
					break;
					
				case 'modify':
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