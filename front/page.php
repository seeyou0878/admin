<?php

namespace Front;

class Page
{
    function __construct()
    {
		// security
		$style = preg_replace('/[\/\.\\\\]/', '', \Config::get('branch.web-style'));
		$this->path = 'views/' . $style . '/';
		
		$this->banner = \Config::get('branch.web-banner');
		$this->banner[0]['active'] = 'active';
		/*$this->banner = [
			['src' => 'upload/f611902aeaad471b1698600396a2a52c', 'active' => 'active'],
			['src' => 'upload/5fccc9fd6231e10a40b070cc11d4c97b'],
			...
		];*/
		
		$this->event = \Config::get('branch.web-event');
		/*$this->event = [
			['src' => 'upload/73fcbc774df9492d481cc641100523ff'],
			['src' => 'upload/9b698c87d9557dc2a0ce33e66c186f47'],
			...
		];*/
		
		$this->bulletin = \Config::get('branch.web-bulletin');
		/*$this->bulletin = [
			['title' => '各遊戲館【維護時間公告】', 'content' => '親愛的會員您好：以下是各遊戲館的系統維護時間～'],
			['title' => '【遊戲說明】電子遊戲登入問題', 'content' => '本公司所提供【3D電子、康博電子】由於目前使用UNITY3D遊戲引擎開發製作，'],
			...
		];*/
		
		$this->background = \Config::get('branch.background-image');
		/*$this->background = [
			'upload/ab8e2f3b8314055a310debe71bb92026',
			'upload/892c4eb43898a470d31dfd38879f6b4a',
		];*/

		$this->qrcode = \Config::get('branch.web-qrcode');
		$this->qrcode_icon = \Config::get('branch.web-qrcode-ico');
		
		$this->game = \Config::get('branch.user-game');
		$this->wgame = \Config::get('branch.user-wgame');
		$this->payment = \Config::get('branch.user-payment');
		/*$this->game = [
			['id' => 1, 'game' => '體育博彩', 'library' => 'supersport'],
			['id' => 1, 'game' => '微妙電子', 'library' => 'microsova'],
			['id' => 1, 'game' => '康博電子', 'library' => 'comebets'],
			['id' => 1, 'game' => '歐博真人', 'library' => 'allbet'],
			['id' => 1, 'game' => '黃金亞洲', 'library' => 'goldenasia'],
			['id' => 1, 'game' => '遊聯電子', 'library' => 'globalgaming'],
			['id' => 1, 'game' => '沙龍真人', 'library' => 'salon'],
		];*/

		$this->base = [
			// web
			'web-title'         => \Config::get('branch.web-title'),//網站標題
			'web-description'   => \Config::get('branch.web-description'),
			'web-keywords'      => \Config::get('branch.web-keywords'),
			'web-logo'          => \Config::get('branch.web-logo'),
			'web-mlogo'         => \Config::get('branch.web-mlogo'),
			'web-foot'          => \Config::get('branch.web-foot'),
			'web-script'        => \Config::get('branch.web-script'),
			'web-qrcode-ico'   	=> \Config::get('branch.web-qrcode-ico'),
			'web-qrcode'      	=> \Config::get('branch.qrcode'),
			// background
			'background-color'  => \Config::get('branch.background-color'),
			'background-image'  => $this->background[0],
			// url
			'url-live'          => \Config::get('branch.url-live'),
			'url-chat'          => \Config::get('branch.url-chat'),
			'url-teamviewer'    => \Config::get('branch.url-teamviewer'),
			// user
			'user-auth'         => \Config::get('branch.user-auth'),
			'user-name'         => e(\Config::get('branch.user-name')),
			'user-account'      => \Config::get('branch.user-account'),
			'user-phone'        => e(\Config::get('branch.user-phone')),
			'user-valid'        => (\Config::get('branch.user-valid') == 2)? '<span id="valid" class="label label-success">已驗證</span>': '<span id="valid" class="label label-danger">待驗證</span>',
			'user-wallet'       => \Config::get('branch.user-wallet'),
			'user-bank'         => \Config::get('branch.user-bank'),
			'user-bank-account' => e(\Config::get('branch.user-bank-account')),
			'user-bank-name'    => e(\Config::get('branch.user-bank-name')),
			'user-unread'       => \Config::get('branch.user-unread'),
			// auth
			'auth'              => \Session::get('auth')? '': 'hidden',
			'unauth'            => \Session::get('auth')? 'hidden': '',
		];
		
		$this->page_title = [
			''                => '',
			'register'        => '免費註冊',
			'event'           => '優惠活動',
			'bulletin'        => '系統公告',
			'live'            => '真人現場',
			'egame'           => '電子遊藝',
			'sport'           => '體育賽事',
			'bingo'           => '賓果彩球',
			'joinus'          => '合作加盟',
			'teach'           => '遊戲教學',
			'store'           => '會員專區',
			'transfer'        => '會員專區',
			'withdraw'        => '會員專區',
			'record_store'    => '會員專區',
			'record_transfer' => '會員專區',
			'record_withdraw' => '會員專區',
			'send'            => '會員專區',
			'message'         => '會員專區',
			'member'          => '會員專區',
			'bank'            => '會員專區',
			'verify'          => '會員專區',
			'policy'          => '使用條款',
			'login'           => '登入',
		];
    }
	
	public function wrap($html = '', $idx = 1){
		
		$tpl = new \Yatp($this->path . 'other.html');
		$chk = strlen($html->block('noframe')->assign(['noframe'=>'noframe'])->render(false)) > 1;
		
		$set = array_merge(
			$this->base,
			array(
				'banner-item' => $tpl->block('banner-item')->nest($this->banner),
				'event-item' => $tpl->block('event-item')->nest($this->event),
				'bulletin-item' => $tpl->block('bulletin-item')->nest($this->bulletin),
				'bulletin-item2' => $tpl->block('bulletin-item2')->nest($this->bulletin),
				'bulletin-item3' => $tpl->block('bulletin-item3')->nest($this->bulletin),
				'qrcode-item' => $tpl->block('qrcode-item')->nest($this->qrcode),
				//'page-title' => $this->page_title[$action] ?? '',
			),
			($chk? ['frame' => $html]: ['html' => $html])
		);
		
		$set['background-image'] = $this->background[$idx];
		
		return $tpl->assign($set)->render(false);
	}
	
	public function getTpl($action){
		return new \Yatp((file_exists($this->path . $action . '.html')? $this->path . $action . '.html': 'views/_common/' . $action . '.html'));
	}
	
	public function index(){
		
		$tpl = $this->getTpl(__FUNCTION__);
		$tpl->assign($this->base + array(
			'banner-item' => $tpl->block('banner-item')->nest($this->banner),
			'event-item' => $tpl->block('event-item')->nest($this->event),
			'bulletin-item' => $tpl->block('bulletin-item')->nest($this->bulletin),
			'bulletin-item2' => $tpl->block('bulletin-item2')->nest($this->bulletin),
			'bulletin-item3' => $tpl->block('bulletin-item3')->nest($this->bulletin),
			'qrcode-item' => $tpl->block('qrcode-item')->nest($this->qrcode),
		));
		
		return $this->wrap($tpl, 0);
	}
	
	public function store(){
		
		$tpl = $this->getTpl(__FUNCTION__);
		$tpl->assign($this->base + array(
			'wgame-item1' => $tpl->block('wgame-item1')->nest($this->wgame),
			'wgame-item2' => $tpl->block('wgame-item2')->nest($this->wgame),
			'wgame-item3' => $tpl->block('wgame-item3')->nest($this->wgame),
			'wgame-item4' => $tpl->block('wgame-item4')->nest($this->wgame),
			'wgame-item5' => $tpl->block('wgame-item5')->nest($this->wgame),
			'payment-item' => $tpl->block('payment-item')->nest($this->payment),
			'payment-item2' => $tpl->block('payment-item2')->nest($this->payment),
		));
		
		return $this->wrap($tpl);
	}
	
	public function transfer(){
		
		$tpl = $this->getTpl(__FUNCTION__);
		$tpl->assign($this->base + array(
			'game-item'  => $tpl->block('game-item')->nest($this->game),
			'game-item2'  => $tpl->block('game-item2')->nest($this->game),
			'wgame-item1' => $tpl->block('wgame-item1')->nest($this->wgame),
			'wgame-item2' => $tpl->block('wgame-item2')->nest($this->wgame),
			'wgame-item3' => $tpl->block('wgame-item3')->nest($this->wgame),
			'wgame-item4' => $tpl->block('wgame-item4')->nest($this->wgame),
			'wgame-item5' => $tpl->block('wgame-item5')->nest($this->wgame),
		));
		
		return $this->wrap($tpl);
	}
	
	public function withdraw(){
		
		$tpl = $this->getTpl(__FUNCTION__);
		$tpl->assign($this->base + array(
			'game-item'  => $tpl->block('game-item')->nest($this->game),
			'game-item2'  => $tpl->block('game-item2')->nest($this->game),
			'wgame-item1' => $tpl->block('wgame-item1')->nest($this->wgame),
			'wgame-item2' => $tpl->block('wgame-item2')->nest($this->wgame),
			'wgame-item3' => $tpl->block('wgame-item3')->nest($this->wgame),
			'wgame-item4' => $tpl->block('wgame-item4')->nest($this->wgame),
			'wgame-item5' => $tpl->block('wgame-item5')->nest($this->wgame),
			'user-bank' => $this->base['user-bank'],
			'user-bank-account' => $this->base['user-bank-account'],
			'user-bank-name' => $this->base['user-bank-name'],
		));
		
		return $this->wrap($tpl);
	}
	
	public function login(){
		
		$tpl = $this->getTpl(__FUNCTION__);
		$tpl->assign(array(
			'url-chat' => $this->base['url-chat'],
		));
		
		return $this->wrap($tpl);
	}
	
	public function logout(){
		// logout
		\Session::forget('auth');
		return redirect('/');
	}
	
	public function captcha(){
		// captcha
		return \App::make('Mews\Captcha\Captcha')->create();
	}
	
	public function member(){
		
		$tpl = $this->getTpl(__FUNCTION__);
		$tpl->assign($this->base + array(
			'user-name' => $this->base['user-name'],
			'user-account' => $this->base['user-account'],
			'user-phone' => $this->base['user-phone'],
			'user-valid' => $this->base['user-valid'],
			'user-wallet' => $this->base['user-wallet'],
		));
		
		return $this->wrap($tpl);
	}
	
	public function verify(){
		
		$tpl = $this->getTpl(__FUNCTION__);
		$tpl->assign($this->base + array(
			'user-phone' => $this->base['user-phone'],
		));
		
		return $this->wrap($tpl);
	}
	
	public function sport(){
		$tpl = $this->getTpl(__FUNCTION__);
		return $this->wrap($tpl);
	}
	
	public function live(){
		$tpl = $this->getTpl(__FUNCTION__);
		return $this->wrap($tpl);
	}
	
	public function egame(){
		$tpl = $this->getTpl(__FUNCTION__);
		return $this->wrap($tpl);
	}
	
	public function bingo(){
		$tpl = $this->getTpl(__FUNCTION__);
		return $this->wrap($tpl);
	}
	
	public function bulletin(){
		$tpl = $this->getTpl(__FUNCTION__);
		
		$arr = [];
		
		$data = \DB::table('t_bulletin')->where(['branch_id' => \Config::get('branch.branch_id'), 'status_id' => 1])->orderBy('id', 'DESC')->simplePaginate(10);
		$page = $data->links();
		
		foreach($data as $v){
			$arr[] = [
				'date' => date('Y-m-d', $v->cdate),
				'title' => $v->title,
				'content' => $v->content
			];
		}
		
		$tpl->assign(array(
			'bulletin-item' => $tpl->block('bulletin-item')->nest($arr),
			'page' => $page,
		));
		
		return $this->wrap($tpl);
	}
	
	public function event(){
		$tpl = $this->getTpl(__FUNCTION__);
		
		$arr = [];
		
		$data = \DB::table('t_event')->where(['branch_id' => \Config::get('branch.branch_id'), 'status_id' => 1])->orderBy('id', 'DESC')->simplePaginate(10);
		$page = $data->links();
		
		foreach($data as $v){
			$arr[] = [
				'url' => json_decode($v->icon, true)[0]['url'] ?? '',
				'content' => $v->content
			];
		}
		
		$tpl->assign(array(
			'event-item' => $tpl->block('event-item')->nest($arr),
			'page' => $page,
		));
		
		return $this->wrap($tpl);
	}
	
	public function bank(){
		$tpl = $this->getTpl(__FUNCTION__);
		
		$arr = [];
		
		$data = \DB::table('t_bank')
			->orderBy('code', 'ASC')
			->get()->toArray();
		
		//dd($data);
		foreach($data as $v){
			$arr[] = [
				'id' => $v->id,
				'bank' => '(' . $v->code . ')' . $v->name,
			];
		}
		
		$tpl->assign($this->base + array(
			'bank-item' => $tpl->block('bank-item')->nest($arr),
		));
		
		return $this->wrap($tpl);
	}
	
	public function message(){
		$tpl = $this->getTpl(__FUNCTION__);
		
		$arr = [];
		
		// message
		$data = \DB::table('t_message')
			->where('member_id', \Session::get('auth'))
			->orderBy('id', 'DESC')
			->simplePaginate(10);
		$page = $data->links();
		
		$id = [];
		foreach($data as $v){
			$id[] = $v->id;
			
			$arr[$v->id] = [
				'msg-read' => '/ajax/set_read?id=' . $v->id,
				'msg-date' => date('Y-m-d H:i:s', $v->cdate),
				'msg-title' => e($v->title),
				'msg-content' => e($v->content),
				'msg-new' => $v->read == 2? '新訊息': '',
			];
		}
		
		// reply
		$reply = [];
		$data2 = \DB::table('t_reply')
			->whereIn('message_id', $id)
			->orderBy('id', 'DESC')
			->get()->toArray();
			
		foreach($data2 as $v){
			$reply[$v->message_id][] = [
				'reply-date' => date('Y-m-d H:i:s', $v->cdate),
				'reply-content' => e($v->content),
			];
		}
		
		foreach($data as $v){
			$tmp = $reply[$v->id] ?? [];
			$arr[$v->id]['reply-item'] = $tpl->block('reply-item')->nest($tmp);
			$arr[$v->id]['msg-count'] = count($tmp)? '(' . count($tmp) . '則回覆)': '';
		}
		
		$tpl->assign($this->base + array(
			'message-item' => $tpl->block('message-item')->nest($arr),
			'page' => $page,
		));
		
		return $this->wrap($tpl);
	}
	
	public function send(){
		$tpl = $this->getTpl(__FUNCTION__);
		$tpl->assign($this->base);
		return $this->wrap($tpl);
	}
	
	public function record_store(){
		$tpl = $this->getTpl(__FUNCTION__);
		
		$arr = [];
		
		$data = \DB::table('t_order')
			->select('t_order.*', 't_game.name as tar_id', 't_order_status.alias as status_id')
			->leftJoin('t_game', 't_game.id', '=', 't_order.tar_id')
			->join('t_order_status', 't_order_status.id', '=', 't_order.status_id')
			->where('type_id', 1)
			->where('member_id', \Session::get('auth'))
			->orderBy('id', 'DESC')
			->simplePaginate(10);
		$page = $data->links();
		//dd($data);
		foreach($data as $v){
			$arr[] = [
				'date' => date('Y-m-d H:i:s', $v->cdate),
				'code' => $v->src_text,
				'target' => ($v->tar_id)? $v->tar_id . '-' . $v->tar_text: '電子錢包',
				'status' => $v->status_id,
				'total' => number_format($v->total, 2),
			];
		}
		
		$tpl->assign($this->base + array(
			'record-item' => $tpl->block('record-item')->nest($arr),
			'page' => $page,
		));
		
		return $this->wrap($tpl);
	}
	
	public function record_transfer(){
		$tpl = $this->getTpl(__FUNCTION__);
		
		$arr = [];
		
		$data = \DB::table('t_order')
			->select('t_order.*', 't_game.name as tar_id', 't_game2.name as src_id', 't_order_status.alias as status_id')
			->leftJoin('t_game', 't_game.id', '=', 't_order.tar_id')
			->leftJoin('t_game as t_game2', 't_game2.id', '=', 't_order.src_id')
			->join('t_order_status', 't_order_status.id', '=', 't_order.status_id')
			->where('type_id', 2)
			->where('member_id', \Session::get('auth'))
			->orderBy('id', 'DESC')
			->simplePaginate(10);
		$page = $data->links();
		//dd($data);
		foreach($data as $v){
			$arr[] = [
				'date' => date('Y-m-d H:i:s', $v->cdate),
				'source' => ($v->src_id)? $v->src_id . '-' . $v->src_text: '電子錢包',
				'target' => $v->tar_id . '-' . $v->tar_text,
				'status' => $v->status_id,
				'total' => number_format($v->total, 2),
			];
		}
		
		$tpl->assign($this->base + array(
			'record-item' => $tpl->block('record-item')->nest($arr),
			'page' => $page,
		));
		
		return $this->wrap($tpl);
	}
	
	public function record_withdraw(){
		$tpl = $this->getTpl(__FUNCTION__);
		
		$arr = [];
		
		$data = \DB::table('t_order')
			->select('t_order.*', 't_game.name as src_id', 't_order_status.alias as status_id')
			->leftJoin('t_game', 't_game.id', '=', 't_order.src_id')
			->join('t_order_status', 't_order_status.id', '=', 't_order.status_id')
			->where('type_id', 3)
			->where('member_id', \Session::get('auth'))
			->orderBy('id', 'DESC')
			->simplePaginate(10);
		$page = $data->links();
		//dd($data);
		foreach($data as $v){
			$arr[] = [
				'date' => date('Y-m-d H:i:s', $v->cdate),
				'source' => $v->src_id . '-' . $v->src_text,
				'target' => $v->tar_text,
				'status' => $v->status_id,
				'total' => number_format($v->total, 2),
			];
		}
		
		$tpl->assign($this->base + array(
			'record-item' => $tpl->block('record-item')->nest($arr),
			'page' => $page,
		));
		
		return $this->wrap($tpl);
	}
	
	public function joinus(){
		$tpl = $this->getTpl(__FUNCTION__);
		return $this->wrap($tpl);
	}
	
	public function register(){
		$tpl = $this->getTpl(__FUNCTION__);
		
		$arr = [];
		
		$data = \DB::table('t_member_contact')->get();
		foreach($data as $v){
			$arr[] = [
				'id' => $v->id,
				'name' => $v->alias,
			];
		}
		
		$tpl->assign([
			'contact-item' => $tpl->block('contact-item')->nest($arr),
			'contact-item2' => $tpl->block('contact-item2')->nest($arr),
		]);
		
		return $this->wrap($tpl);
	}
	
	public function policy(){
		$tpl = $this->getTpl(__FUNCTION__);
		return $this->wrap($tpl);
	}
	
	public function teach(){
		$tpl = $this->getTpl(__FUNCTION__);
		return $this->wrap($tpl);
	}
}
