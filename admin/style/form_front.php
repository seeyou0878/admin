<?php
namespace Admin;

class Front{
	
	function __construct(){
		
		$obj = new \Yapa(
			/*file*/
			_url(get_class($this)),
			/*table*/
			't_account',
			/*col*/
			array('id', 'branch_id', 'account', 'account_id', 'level_id', 'name', 'style_id', 'title', 'description', 'keyword', 'color', 'foot', 'qrcode_icon', 'qrcode', 'chat', 'script', 'logo', 'mlogo', 'banner', 'index', 'sub'),
			/*col_ch*/
			array('代碼', '分站', '帳號', '下線', '階層', '姓名', '版型', '網站標題', '敘述', '關鍵字', '背景色', '頁尾文字', 'QR區按鈕圖', 'QR_Code', '客服網址', '外掛', '網站LOGO', '手機版LOGO', '廣告輪播', '首頁背景圖', '子頁背景圖'),
			/*empty check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*exist(duplicate) check*/
			array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
			/*chain(join) check (table, content, id)*/
			array('', 't_branch,name,id', '', 't_account,account,id', 't_level,name,id', '', 't_style,name,id', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
			/*show bootstrap grid class*/
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'col-md-1 col-sm-1 hidden-xs':'hidden',
				'col-md-3 col-sm-3 col-xs-3',
				'col-md-1 col-sm-1 col-xs-1',
				'hidden',
				'hidden',
				'col-md-1 col-sm-1 hidden-xs',
				'col-md-2 col-sm-2 col-xs-4',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
				'col-md-2 col-sm-2 hidden-xs',
				'hidden',
				'hidden',
				'hidden',
				'hidden',
			),
			/*select/radiobox/checkbox/text/password/textarea/autocomplete/datepicker */
			array(
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'hidden',
				($_SESSION['user']['cross'])? 'select': 'hidden',
				'hidden',
				'hidden', 'select', 'text', 'text', 'text', 'colorpicker', 'text', 'uploadfile', 'uploadfile,{"max": 15}', 'text', 'textarea', 'uploadfile', 'uploadfile', 'uploadfile,{"max": 15}', 'uploadfile', 'uploadfile'),
			/*authority check*/
			array(
				$_SESSION['auth']['style_review'] ?? 0,
				0,
				$_SESSION['auth']['style_modify'] ?? 0,
				0,
			),
			/*medoo*/
			\Box::obj('db'),
			/*config*/
			array(
				'perpage' => 0,
				'root' => ($_SESSION['user']['cross'])? 0: $_SESSION['user']['root'],
			)
		);
		
		$arr = $obj->decodeJson($_POST);

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
						$tpl = new \Yatp(__DIR__ . '/report.tpl');
						foreach($r['data'] as $k=>$v){
							$r['data'][$k]['account'] = $obj->raw($v['account'] . '(' . $v['name'] . ')<br>' . ($v['level_id']?: '會員'));
						}
						return $r;
					});
					exit;
					break;
				case 'create':
					if($_SESSION['user']['cross']){
						// pass
					}else{
						$obj->arg['data']['branch_id'] = $_SESSION['user']['branch_id'];
					}
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