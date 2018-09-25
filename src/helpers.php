<?php
if (!function_exists('app')) {
	/**
	 * 获取Di容器
	 *
	 * @param  string  $abstract
	 * @return mixed|EasySwoole\Core\Component\Di
	 */
	function app($abstract = null) {
		if (is_null($abstract)) {
			return EasySwoole\Core\Component\Di::getInstance();
		}
		return EasySwoole\Core\Component\Di::getInstance()->get($abstract);
	}
}
if (!function_exists('url')) {
	/**
	 * 设置全网址
	 * @param string $path 路径
	 * @return string
	 */
	function url($path = '', $urlType = 'url') {
		// url
		// driverUrl
		// shoperUrl
		// managerUrl
		// apiUrl
		return config('app.' . $urlType) . '/' . $path;
	}
}

if (!function_exists('method_field')) {
	/**
	 * Generate a form field to spoof the HTTP verb used by forms.
	 *
	 * @param  string  $method
	 * @return \Illuminate\Support\HtmlString
	 */
	function method_field($method) {
		return new HtmlString('<input type="hidden" name="_method" value="' . $method . '">');
	}
}
if (!function_exists('user')) {
	/**
	 * Generate a form field to spoof the HTTP verb used by forms.
	 *
	 * @param  string  $method
	 * @return \Illuminate\Support\HtmlString
	 */
	function user($header) {
		$jwtConf = config('jwt');
		$tokenBase = head($header);
		$token = str_replace($jwtConf['bearer'], '', $tokenBase);
		$info = jwtdecode($token);
		if (!$info) {
			return null;
		} else {
			return getUser($info['sub']);
		}
	}
}
if (!function_exists('collect')) {
	/**
	 * Create a collection from the given value.
	 *
	 * @param  mixed  $value
	 * @return \Illuminate\Support\Collection
	 */
	function collect($value = null) {
		return new \Illuminate\Support\Collection($value);
	}
}
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
function getUser($id, $exp = null) {
	try {
		return Base\Models\User::find($id)->$exp ?? Base\Models\User::find($id);
	} catch (\Exception $e) {
		return null;
	}
}
function getDriver($id, $exp = null) {
	try {
		return Base\Models\Driver::find($id)->$exp ?? Base\Models\Driver::find($id);
	} catch (\Exception $e) {
		return null;
	}
}
function getAdmin($id, $exp = null) {
	try {
		return Base\Models\Admin::find($id)->$exp ?? Base\Models\Admin::find($id);
	} catch (\Exception $e) {
		return null;
	}
}
if (!function_exists('config')) {
	/**
	 * Generate a form field to spoof the HTTP verb used by forms.
	 *
	 * @param  string  $method
	 * @return \Illuminate\Support\HtmlString
	 */
	function config($param = null, $nulldata = null) {
		return \EasySwoole\Config::getInstance()->getConf($param) ?? $nulldata;
	}
}
function ossUrl($expend = '') {
	return config('oss.url', 'https://statics.ddchuansong.com/') . $expend;
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

function jwtencode($id) {
	$time = time();
	$jwtconf = config('jwt');
	$token = array(
		"exp" => $time + $jwtconf['exp'],
		"iat" => $time + $jwtconf['iat'],
		"nbf" => $time + $jwtconf['nbf'],
		"sub" => $id,
	);
	$token = \Firebase\JWT\JWT::encode($token, $jwtconf['key']);
	return $token;
}
function jwtdecode(String $token, $force = false) {
	$jwtconf = config('jwt');
	try {
		$info = \Firebase\JWT\JWT::decode($token, $jwtconf['key'], array('HS256'));
		// var_dump($info);
		$decoded_array = (array) $info;
		return $decoded_array;
	} catch (\Firebase\JWT\ExpiredException $e) {
		// at this point the jwt was validated ok
		if ($force) {
			list($headb64, $bodyb64, $cryptob64) = explode('.', $token);
			$payload = base64_decode($bodyb64);
			return $payload;
		} else {
			return 'Token已过期!';
		}
	} catch (\Exception $e) {
		return 'Token已过期!';
	}
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
/**
 * [makeQrcode description]
 * @param  [type] $options 文本信息,宽高,false图片base64信息
 * @return [type]          [description]
 */
function makeQrcode(...$options) {
	// var_dump($options);
	$url = $options[0];
	$size = $options[1] ?? 250;
	$steam = $options[2] ?? false;
	$qrSteam = new Endroid\QrCode\QrCode($url);
	$qrSteam->setSize($size);
	if ($steam) {
		header('Content-Type: ' . $qrSteam->getContentType());
		return $qrSteam->writeString();
	} else {
		$tmpname = md5($url);
		$tempfile = tempnam('tmp', $tmpname);
		$qrSteam->writeFile($tempfile);
		return base64EncodeImage($tempfile);
		@unlink($tempfile);
	}
}
function base64EncodeImage($image_file) {
	$base64_image = '';
	$image_info = getimagesize($image_file);
	$image_data = fread(fopen($image_file, 'r'), filesize($image_file));
	$base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
	return $base64_image;
}
function userExpressPermission($state = 0) {
	$permission = 0;
	if (in_array((int) $state, [0, 1])) {
		$permission = 7;
	} else {
		$permission = 4;
	}
	return $permission;
}
function array_only_null(array $array, array $keys, $default = null) {
	$return = [];
	foreach ($keys as $key) {
		$return[$key] = $array[$key] ?? $default;
	}
	return $return;
}
function time2int($str, $tag = ':') {
	return str_replace($tag, '', $str);
}
// $relationTable [table,id,relation_id]
function sortFunc($sort, $eloquent, $field = ['id', 'created_at'], $relationTable = null) {
	if (strpos($sort, '-') === 0) {
		$sortF = 'desc';
	} else {
		$sortF = 'asc';
	}
	$sortParam = trim($sort, '-');
	if (!in_array($sortParam, $field)) {
		$sortParam = 'id';
	}
	if ($relationTable) {
		$table = $eloquent->getModel()->getTable();
		return $eloquent->select($table . '.*', \Illuminate\Database\Capsule\Manager::raw('(SELECT ' . $sortParam . ' FROM ' . $relationTable[0] . ' WHERE ' . $table . '.' . $relationTable[2] . ' = ' . $relationTable[0] . '.' . $relationTable[1] . ' ) as sort'))->orderBy('sort', $sortF);
	} else {
		return $eloquent->orderBy($sortParam, $sortF);
	}
}
function opType($case = 11) {
	switch ($case) {
	case '11':
		return '取件 - 派送';
		break;
	case '12':
		return '取件 - 中转';
		break;
	case '21':
		return '中转 - 派送';
		break;
	case '22':
		return '中转';
		break;
	default:
		return '中转';
		break;
	}
}
function diff_params($validata, $rec, $all = false) {
	$result = array_diff_key($validata, $rec);
	$return = null;
	foreach ($result as $value) {
		if ($value) {
			$return .= $value . '\r\n';
			if ($all) {
				break;
			}
		}
	}
	return rtrim($return, '\r\n');
}
//PHP stdClass Object转array
function object_array($array) {
	if (is_object($array)) {
		$array = json_decode(json_encode($array), 1);
	}
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			$array[$key] = object_array($value);
		}
	}
	return $array;
}