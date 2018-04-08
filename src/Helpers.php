<?php
if (!function_exists('now')) {
	/**
	 * Create a collection from the given value.
	 *
	 * @param  mixed  $value
	 * @return \Illuminate\Support\Collection
	 */
	function now($value = null) {
		return date('Y-m-d H:i:s');
	}
}
function bfb($val, $dot) {
	$str = round($val, $dot + 2);
	return sprintf("%." . ($dot) . "f", $str * 100) . '%';
}
function object_to_array($obj) {
	// dd($obj);
	$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
	$arr = [];
	foreach ($_arr as $key => $val) {
		$val = (is_array($val)) || is_object($val) ? object_to_array($val) : $val;
		$arr[$key] = $val;
	}
	// dd($arr);
	return $arr;
}
function array_to_object($arr) {
	if (gettype($arr) != 'array') {
		return;
	}
	foreach ($arr as $k => $v) {
		if (gettype($v) == 'array' || getType($v) == 'object') {
			$arr[$k] = (object) array_to_object($v);
		}
	}

	return (object) $arr;
}
function array_to_string($arr) {
	return is_string($arr) ? $arr : json_encode($arr);
}
/**
 * 返回给定字符串中已知两个字符中间的值[getNeedBetween description]
 * @param  [type] $kwd   [description]
 * @param  [type] $mark1 [description]
 * @param  [type] $mark2 [description]
 * @return [type]        [description]
 */
function getNeedBetween($kwd, $mark1, $mark2 = '') {
	$kw = $kwd;
	if ($mark1 === null) {
		$startnum = 1;
	} else {
		$st = stripos($kw, $mark1);
		$startnum = $st + strlen($mark1);
	}
	if ($mark2 === '') {
		return substr($kw, $startnum);
	}
	$ed = stripos($kw, $mark2);
	if (($st === false || $ed === false) || $st >= $ed) {
		return 0;
	}
	if (($ed + 1) === strlen($kwd)) {
		$endnum = $ed - $st - 1 - strlen($mark2);
	} else {
		$endnum = $ed - $st - 1;
	}
	$kw = substr($kw, $startnum, $endnum);
	return $kw;
}
//将XML转为array
function xmlToArray($xml) {
	//禁止引用外部xml实体
	libxml_disable_entity_loader(true);
	$values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	return $values;
}
function base64EncodeImageDate($image_data) {
	return $base64_image = 'data:image/png;base64,' . chunk_split(base64_encode($image_data));
}
function base64EncodeImage($image_file) {
	$base64_image = '';
	$image_info = getimagesize($image_file);
	$image_data = fread(fopen($image_file, 'r'), filesize($image_file));
	$base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
	return $base64_image;
}
function array_only_null(array $array, array $keys, $default = null) {
	$return = [];
	foreach ($keys as $key) {
		$return[$key] = array_get($array, $key, $default);
	}
	return $return;
}
function time2int($str, $tag = ':') {
	return str_replace($tag, '', $str);
}