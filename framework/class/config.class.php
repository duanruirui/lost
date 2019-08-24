<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
class Config {

	private static $instance = null;
	private $config;
	private $setting;
	private function __construct() {
		global $_W;
		$this->config = $_W['config'];
		$this->setting = $_W['setting'];
	}

	public static function instance() {
		if(is_null(self::$instance)) {
			self::$instance = new Config();
		}
		return self::$instance;
	}

	/**
	 * 是否支持缩略图
	 * @return mixed|null
	 *
	 * @since version
	 */
	public function supportThumb() {
		return $this->get('upload.image.thumb', 0);
	}
	/**
	 * 图片最大上传大小
	 * @since version
	 */
	public function imageMaxSize() {
		return intval($this->get('upload.image.limit', 5120));
	}

	/**
	 * 图片文件允许的扩展
	 * @return mixed|null
	 * @since version
	 */
	public function imageExtension() {
		return $this->get('upload.image.extentions', null);
	}

	/**
	 *  是否是允许的图片扩展
	 * @param $ext
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function allowImageExt($ext) {
		$exts = $this->imageExtension();
		return in_array($ext, $exts);
	}

	/**
	 * 音视频文件大小
	 * @return mixed|null
	 *
	 * @since version
	 */
	public function audioMaxSize() {
		return intval($this->get('upload.audio.limit', 5120));
	}

	/**
	 * 音视频文件扩展名
	 * @return mixed|null
	 *
	 * @since version
	 */
	public function audioExtension() {
		return $this->get('upload.image.extentions', null);
	}

	/**
	 * 获取文件上传driver
	 * @return string
	 *
	 * @since version
	 */
	public function fileDriver() {
		$remoteType = $this->get('remote.type',0);
		$result = 'local';
		switch ($remoteType) {
			case '1' : $result = 'ftp';break;
			case '2' : $result = 'oss'; break;
			case '3' : $result = 'cos4';
						if($this->get('remote.cos.local')) {
							$result = 'cos4';
						}
						break;
			case '4' : $result = 'qiniu';break;
		}
		return $result;
	}

	/**
	 *  上传资源路径
	 * @return mixed|null
	 */
	public function attchmentDir() {
		return $this->config('upload.attachdir', ATTACHMENT_ROOT);
	}


	public function get($key, $default = null) {
		$result = null;
		if(($result = $this->setting($key, null))) {
			return $result;
		}
		return $this->config($key, $default);
	}

	public function config($key, $default = null) {
		return self::getKey($this->config, $key, $default);
	}

	public function setting($key, $default = null) {
		return self::getKey($this->setting, $key, $default);
	}

	/**
	 *  本地图片 大小限制 后缀名限制
	 * @param $type
	 *
	 * @return array
	 *
	 * @since version
	 */
	public function localLimit($type) {
		if(!in_array($type, array('image', 'voice', 'video'))) {
			throw new InvalidArgumentException('无效的type参数');
		}
		$limit = $this->localLimitData();
		return $limit[$type];
	}
	/**
	 *  微信素材 limit
	 * @param string $type
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public function wechatLimit($type = 'image') {
		if(!in_array($type, array('image', 'voice', 'video'))) {
			throw new InvalidArgumentException('无效的type参数');
		}
		$wechatLimit = $this->wechatLimit($type);
		return $wechatLimit[$type];
	}

	private function localLimitData() {
		return array(
			'image'=> array(
				'ext'=>$this->imageExtension(),
				'size'=>$this->imageMaxSize(),
			),
			'voice' => array(
				'ext' => $this->audioExtension(),
				'size' => $this->audioMaxSize(),
			),
			'video' => array(
				'ext' => $this->audioExtension(),
				'size' => $this->audioMaxSize(),
			),
		);
	}

	private function wechatLimitData() {
		$limit = array(
			'image' => array(
				'ext' => array('bmp', 'png', 'jpeg', 'jpg', 'gif'),
				'size' => 2048 * 1024,
				'max' => 5000,
				'errmsg' => '永久图片只支持bmp/png/jpeg/jpg/gif格式,大小不超过为2M',
			),
			'voice' => array(
				'ext' => array('amr', 'mp3', 'wma', 'wav', 'amr'),
				'size' => 5120 * 1024,
				'max' => 1000,
				'errmsg' => '永久语音只支持mp3/wma/wav/amr格式,大小不超过为5M,长度不超过60秒',
			),
			'video' => array(
				'ext' => array('rm', 'rmvb', 'wmv', 'avi', 'mpg', 'mpeg', 'mp4'),
				'size' => 10240 * 1024 * 2,
				'max' => 1000,
				'errmsg' => '永久视频只支持rm/rmvb/wmv/avi/mpg/mpeg/mp4格式,大小不超过为20M',
			)
		);
		return $limit;
	}

	private static function getKey($array, $key, $default = null)
	{
		if (! is_array($array)) {
			return $default;
		}

		if (is_null($key)) {
			return $array;
		}

		if (array_key_exists($key, $array)) {
			return $array[$key];
		}

		if (strpos($key, '.') === false) {
			return isset($array[$key]) ? $array[$key] : $default;
		}

		foreach (explode('.', $key) as $segment) {
			if (is_array($array) && array_key_exists($segment, $array)) {
				$array = $array[$segment];
			} else {
				return $default;
			}
		}

		return $array;
	}
}