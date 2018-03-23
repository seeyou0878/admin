<?php
namespace Admin;

class Style{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_style',
			/*col*/
			array('id', 'name', 'library'),
			/*col_ch*/
			array('代碼', '名稱', '系統識別字'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				'col-md-4 col-sm-4 col-xs-4',
				'col-md-4 col-sm-4 col-xs-4',
				'col-md-4 col-sm-4 col-xs-4',
			),
			/*select/radiobox/checkbox/text/textarea/autocomplete/datepicker */
			array(
				'hidden', 'text', 'text'),
			/*authority check*/
			array(
				$_SESSION['auth']['style_admin'] ?? 0,
				$_SESSION['auth']['style_admin'] ?? 0,
				$_SESSION['auth']['style_admin'] ?? 0,
				0,
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