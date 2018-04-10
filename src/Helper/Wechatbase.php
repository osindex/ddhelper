<?php

namespace Base\Helper;
use EasyWeChat\Factory;
use Symfony\Component\Cache\Simple\RedisCache;

class Wechatbase {
	protected static $instance;
	protected $application;

	static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	public function __construct($config = null) {
		if (empty($config)) {
			$config = conf('wechat');
		}
		$application = Factory::officialAccount($config);
		// $application->request->setFactory(\Base\Helper\Request::createFromGlobals());
		$this->application = $application;
		$redis = new \Redis();
		$redis->connect(config('redis.host'), config('redis.port'));
		$redisInstance = new RedisCache($redis, config('redis.prefix_sym'), config('redis.expire'));
		$this->application['cache'] = $redisInstance;

	}

	public function getApplication() {
		return $this->application;
	}

	static function officialAccount($config = null) {
		if (empty($config)) {
			$config = conf('wechat');
		}
		$application = Factory::officialAccount($config);
		\Base\Helper\Request::clearRequestFactory();
		// $application->request->setFactory(\Base\Helper\Request::createFromGlobals());
		// $application['cache'] = Cache::getInstance();
		$redis = new \Redis();
		$redis->connect(config('redis.host'), config('redis.port'));
		$redisInstance = new RedisCache($redis, config('redis.prefix_sym'), config('redis.expire'));
		$application['cache'] = $redisInstance;
		// $accessToken = $application->access_token;
		// $token = $accessToken->getToken(); // token 字符串
		// var_dump($token);
		return $application;
	}
	static function payment($config = null) {
		if (empty($config)) {
			$config = conf('wechat.payment');
		}
		$application = Factory::payment($config);
		\Base\Helper\Request::clearRequestFactory();
		$redis = new \Redis();
		$redis->connect(config('redis.host'), config('redis.port'));
		$redisInstance = new RedisCache($redis, config('redis.prefix_sym'), config('redis.expire'));
		$application['cache'] = $redisInstance;
		// $accessToken = $application->access_token;
		// $token = $accessToken->getToken(); // token 字符串
		// var_dump($token);
		return $application;
	}
}