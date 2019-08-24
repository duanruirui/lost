<?php

defined('IN_IA') or exit('Access Denied');
/**
 * @package
 * @subpackage
 * @since v1.6.3
 * @copyright   A copyright
 * @license     we7
 */

/**
 *
$event = EventDispather::instance();
$event->add('onLogin', function ($args){
	print_r($args); // 加积分
	return false; // 其他监听器不再处理
});

$event->add('onLogin', function ($args){
    //记录登录日志
	print_r($args);
});
 */
class EventDispather {
	
	private $listeners = array();

	private $sys_prefix = 'we7_';//系统事件前缀

	private static $instance = null;

	private function __construct() {
	}

	public static function instance() {
		if(is_null(self::$instance)) {
			self::$instance = new EventDispather();
		}
		return self::$instance;
	}


	public function add($event, $callable) {
		if(empty($event) || !is_string($event)) {
			throw new InvalidArgumentException('事件名称必须是字符串');
		}
		$this->listeners[$event][] = $this->createListener($callable);
	}

	private function createListener($callable) {
		if(is_callable($callable)) {
			return $callable;
		}
		if(is_string($callable) && !class_exists($callable)) {
			throw new InvalidArgumentException('监听参数不正确');
		}
	}

	/**
	 * @param $event
	 * @param array $payLoad
	 * @param bool $halt 是否只要一个处理成功 就不再处理
	 *
	 * @return array|mixed|null
	 *
	 * @since version
	 */
	public function fire($event, $payLoad, $halt = false) {
		if(!is_array($payLoad)) {
			$payLoad = array($payLoad);
		}
		$listeners = $this->getListeners($event);
		$responses = array();
		foreach ($listeners as $listener) {
			$response = $this->callListener($listener, $payLoad);
			// 是否只要一个处理成功就不再处理
			if($halt && !is_null($response)) {
				return $response;
			}
			// 停止触发接下来的事件监听器
			if($response === false) {
				break;
			}

			$responses[] = $response;
		}
		return $halt ? null : $responses;
	}

	/**
	 *  执行监听器
	 * @param $listener
	 * @param $payLoad
	 *
	 * @return mixed|null
	 *
	 * @since version
	 */
	private function callListener($listener, $payLoad) {
		$result = null;
		if(is_callable($listener)) {
			$result = call_user_func_array($listener, $payLoad);
			return $result;
		}
		if(is_string($listener)) {
			$result = $this->callStringListener($listener, $payLoad);
			return $result;
		}
		return null;
	}

	/**
	 * 执行类监听
	 * @param $listener
	 * @param array $payLoad
	 *
	 * @return null
	 *
	 * @since version
	 */
	private function callStringListener($listener, array $payLoad) {
		if(class_exists($listener)) {
			$instance = new $listener;
			if(method_exists($instance,'handle')) {
				return $instance->handle($payLoad);
			}
		}
		return null;
	}

	private function getListeners($event) {
		return isset($this->listeners[$event]) ? $this->listeners[$event] : null;
	}
}