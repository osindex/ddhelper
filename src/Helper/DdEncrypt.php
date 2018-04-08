<?php
namespace Base\Helper;

class DdEncrypt {
	/**
	 * @param  string
	 * @return array[ 0 => 'companyCode', 1 => 'key']
	 */
	static function getOpenKey($value = '') {
		return explode('_', $value, 2);
	}
	/*
		 * Generate a signature.
		 *
		 * @param array  $attributes
		 * @param string $secret
		 * @param string $api_secret_name
		 * @param string $strto | strtoupper,strtolower
		 *
		 * @return string
	*/
	static function generate_sign(array $attributes, $secret, $api_secret_name = 'api_secret', $strto = 'strtoupper') {
		$encryptMethod = $attributes['encryptMethod'] ?? 'md5';
		if (!in_array($encryptMethod, ['md5', 'sha1'])) {
			throw new Exception("encryptMethod Error!", 1);
		}
		$strto = $attributes['strto'] ?? 'strtoupper';
		if (!in_array($strto, ['strtolower', 'strtoupper'])) {
			throw new Exception("strto Error!", 1);
		}
		unset($attributes['encryptMethod']);
		unset($attributes['strto']);
		$attributes[$api_secret_name] = $secret;
		ksort($attributes);
		// var_dump($attributes);
		// var_dump(http_build_query($attributes));
		return call_user_func_array($strto, [call_user_func_array($encryptMethod, [urldecode(http_build_query($attributes))])]);
	}
	/**
	 *
	 * 字符串连接加密
	 *
	 * @param  array 需要加密的数组
	 * @param  string 密钥
	 * @param  string 和密钥连接的字符串
	 * @param  string 大小写
	 * @return string 加密后的结果
	 */
	static function dot_generate_sign(array $attributes, $secret, $api_secret_name = 'rqtime', $strto = 'strtolower') {
		$encryptMethod = $attributes['encryptMethod'] ?? 'md5';
		if (!in_array($encryptMethod, ['md5', 'sha1'])) {
			throw new Exception("encryptMethod Error!", 1);
		}
		return call_user_func_array($strto, [call_user_func_array($encryptMethod, [$attributes[$api_secret_name] . $secret])]);
	}

}

?>
