<?php
namespace Base\Helper;

class Helper {
	static function RouteTypeString($type = null) {
		switch ($type) {
		case 2:
			$data = '干线接管';
			break;
		case 5:
			$data = '中转站接管';
			break;
		case 10:
			$data = '终端接管';
			break;
		default:
			$data = '-';
			break;
		}
		return $data;
	}

	static function payTypeString($type = null) {
		switch ($type) {
		case '0':
			$data = '充值';
			break;
		case 1:
			$data = '消费';
			break;
		case 2:
			$data = '退款';
			break;
		case 3:
			$data = '管理操作';
			break;
		default:
			$data = '-';
			break;
		}
		return $data;
	}

	/**
	 * 环线码转换
	 */
	static function ringString($statu = null) {
		$data = '几环';
		switch ($statu) {
		case '0':
			$data = '无数据';
			break;
		case 5:
			$data = '五环内';
			break;
		case 6:
			$data = '五环至六环';
			break;
		case 7:
			$data = '六环外';
			break;
		default:
			$data = '北京市';
			break;
		}
		return $data;
	}

	/**
	 * 状态码转换
	 */
	static function statuString($statu = null) {
		$data = '待领用';
		switch ($statu) {
		case '-1':
			$data = "待支付";
			break;
		case '0':
			$data = '待取件';
			break;
		case 1:
			$data = '已指派取件';
			break;
		case 10:
			$data = '运送中';
			break;
		case 98:
			$data = '取消中';
			break;
		case 99:
			$data = '已取消';
			break;
		case 100:
			$data = '已签收';
			break;
		case 999:
			$data = '异常件';
			break;
		case 'search':
			$data = '搜索快件';
			break;
		default:
			$data = '所有快件';
			break;
		}
		return $data;
	}

	/**
	 * 获取微信用户tag
	 */
	static function checkWechatTag($name = null) {
		$wechat = app('wechat');
		$tag = $wechat->user_tag;

		// 		$tag->delete(102);
		$tags = $tag->lists();
		$tagId = false;
		foreach ($tags->tags as $_t) {
			if ($_t['name'] == $name) {
				$tagId = $_t['id'];
				break;
			}
		}

		if (!$tagId) {
			$newTag = $tag->create($name);
			$tagId = $newTag['tag']['id'];
		}
		return $tagId;
	}

	static function getUniqTrans($express_id) {
		// $today = new \Carbon\Carbon('today');
		$_exnum = date('ymd') . rand(100, 999) . $express_id;
		$key = \Base\Models\ExpressTransaction::where('transaction_no', $_exnum)->count();
		if ($key > 0) {
			$_exnum = Helper::getUniqExnum($express_id);
			return $_exnum;
		} else {
			return $_exnum;
		}
	}
	/**
	 * 获取唯一的快递单编号
	 */
	static function getUniqExnum($mobile) {
		// $today = new \Carbon\Carbon('today');
		// $_exnum = date('md') . substr($mobile, -4) . rand(100, 999);
		$_exnum = date('md') . substr($mobile, -4) . rand(10000, 99999);
		$key = \Base\Models\Express::where('number', $_exnum)->count();
		if ($key > 0) {
			$_exnum = Helper::getUniqExnum($mobile);
			return $_exnum;
		} else {
			return $_exnum;
		}
	}

	static function getUniqDDid($area = null, $num = 1, $length = 5) {
		$maxNum = str_pad(1, $length + 1, 0);
		$arr = \Base\Models\Express::whereRaw('length(`ddid`) <= ' . $length)->pluck('ddid')->toArray();
		// 能否用  date('d')%2 == 1 max('ddid')  return ddid+1
		// 		  date('d')%2 == 0 min('ddid')  return ddid-1
		if ((count($arr) / $maxNum) > 0.8) {
			// 加入预警通知 ?
			if (0.9 * $maxNum - count($arr) > $num) {
				return false;
			}
		}
		$uniqarr = [];
		while (count($uniqarr) < $num) {
			$a = str_pad(mt_rand(1, $maxNum - 1), $length, 0, STR_PAD_LEFT);
			if (!in_array($a, $arr)) {
				$arr[] = $a;
				$uniqarr[] = $a;
			}
		}
		return $uniqarr;
	}

	static function get_invite_code($length = 4) {
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$code = '';
		for ($i = 0; $i < $length; $i++) {
			$code .= $chars[mt_rand(0, strlen($chars) - 1)];
		}
		$check = \Base\Models\User::where('invite_code', $code)->count();
		if ($check > 0) {
			$code = Helper::get_invite_code();
		} else {
			return $code;
		}
	}

	static function covertGPS($lng, $lat) {
		$client = new \GuzzleHttp\Client();
		$location = $lng . ',' . $lat;

		$key = config('amap.key', 'bfdb61a0259970baca9a68b9525b8faa');
		$apiURL = config('amap.url', 'http://restapi.amap.com/v3') . '/assistant/coordinate/convert?key=' . $key . '&coordsys=gps&locations=' . urlencode($location);

		$res = $client->request('GET', $apiURL);

		$obj = json_decode($res->getBody());
		$data = null;
		if ($obj->status == 1 and $obj->infocode == 10000) {
			list($data['lng'], $data['lat']) = explode(',', $obj->locations);
		}
		return $data;

	}
	/**
	 * 获取坐标
	 */
	static function geoDecode($address, $city = '010') {
		// if (strpos($address,'北京') === false) {
		// 	$address = '北京市' . $address;
		// }
		$client = new \GuzzleHttp\Client();
		$key = config('amap.key', 'bfdb61a0259970baca9a68b9525b8faa');
		$query = [
			'key' => $key,
			'city' => $city,
			'address' => $address,
		];
		if (is_array($address)) {
			$query['address'] = implode('|', $address);
			$query['batch'] = true;
		}
		$params = http_build_query($query);
		$apiURL = config('amap.url', 'http://restapi.amap.com/v3') . '/geocode/geo?' . $params;
		$res = $client->request('GET', $apiURL);
		$obj = json_decode($res->getBody());
		$data = null;
		if ($obj->status == 1 and $obj->count != 0) {
			if (is_array($address)) {
				foreach ($address as $key => $value) {
					$data[$key]['address'] = $obj->geocodes[$key]->formatted_address;
					$data[$key]['location'] = $obj->geocodes[$key]->location;
					$data[$key]['district'] = $obj->geocodes[$key]->province . $obj->geocodes[$key]->city;
					list($data[$key]['lng'], $data[$key]['lat']) = explode(',', $data[$key]['location']);
					$data[$key]['ring'] = self::getRing($data[$key]['lng'], $data[$key]['lat']);

				}
			} else {
				$data['address'] = $obj->geocodes[0]->formatted_address;
				$data['location'] = $obj->geocodes[0]->location;
				$data['district'] = $obj->geocodes[0]->province . $obj->geocodes[0]->city;
				list($data['lng'], $data['lat']) = explode(',', $data['location']);
			}
		}
		return $data;
	}
	/**
	 * 获取坐标及环线
	 */
	static function geoDecodeWithRing($address, $city = '010') {
		$data = self::geoDecode($address, $city);
		$data['ring'] = self::getRing($data['lng'], $data['lat']);
		return $data;
	}
	/**
	 * 获取坐标
	 */
	static function geoDecodeBatchWithRing($addresses, $city = '010') {
		$collect = collect($addresses)->chunk(10)->toArray();
		// 高德接口 每次最多10个
		$return = [];
		foreach ($collect as $address) {
			$return += self::geoDecode($address, $city);
		}
		return collect($return)->collapse()->toArray();
	}
	/**
	 * 获取地理位置
	 */
	static function geoRecode($lng, $lat) {
		$client = new \GuzzleHttp\Client();
		$location = $lng . ',' . $lat;
		$key = config('amap.key', 'bfdb61a0259970baca9a68b9525b8faa');
		$apiURL = config('amap.url', 'http://restapi.amap.com/v3') . '/geocode/regeo?key=' . $key . '&location=' . urlencode($location);
		$res = $client->request('GET', $apiURL);

		$obj = json_decode($res->getBody());
		$data = null;
		if ($obj->status == 1 and $obj->infocode == 10000) {
			$data['address'] = $obj->regeocode->formatted_address;
		}
		return $data;
	}

	/**
	 * 计算距离
	 */
	static function getDistance($origins, $destination) {
		$client = new \GuzzleHttp\Client();
		$key = config('amap.key', 'bfdb61a0259970baca9a68b9525b8faa');
		$apiURL = config('amap.url', 'http://restapi.amap.com/v3') . '/distance?key=' . $key . '&origins=' . $origins . '&destination=' . $destination;
		$res = $client->request('GET', $apiURL);

		$obj = json_decode($res->getBody());
		$res = [];
		if ($obj->status == 1 and $obj->infocode == 10000) {
			foreach ($obj->results as $r) {
				$tmp['distance'] = round($r->distance / 1000, 1);
				$tmp['duration'] = round($r->duration / 60, 1);
				$res[] = $tmp;
			}
		}

		return $res;
	}

	/**
	 * 计算距离
	 */
	static function getPlaceText($keyword, $city = '010') {
		$client = new \GuzzleHttp\Client();
		$key = config('amap.key', 'bfdb61a0259970baca9a68b9525b8faa');
		$apiURL = config('amap.url', 'http://restapi.amap.com/v3') . '/place/text?key=' . $key . '&extensions=base&offset=10&page=1&keywords=' . $keywords;
		$res = $client->request('GET', $apiURL);
		return $res;
	}

	/**
	 * 计算环线关系
	 */
	static function getRing($lng, $lat) {
		$path_5 = [[116.38856, 40.022289], [116.336375, 40.02334], [116.276294, 40.011245], [116.267711, 40.004145], [116.247455, 40.001515], [116.223079, 39.993625], [116.213123, 39.94679], [116.211406, 39.88728], [116.207973, 39.876479], [116.219646, 39.856979], [116.237155, 39.833519], [116.248485, 39.829037], [116.265308, 39.806622], [116.28007, 39.773646], [116.308223, 39.782089], [116.370708, 39.775493], [116.379291, 39.758076], [116.41843, 39.766521], [116.436282, 39.788949], [116.461688, 39.791059], [116.481601, 39.810578], [116.513873, 39.822708], [116.549922, 39.851971], [116.542712, 39.938894], [116.514903, 39.98284], [116.508208, 39.990863], [116.501514, 39.997702], [116.483318, 40.014401], [116.451914, 40.017301], [116.437151, 40.021771]];
		$path_6 = [[116.486064, 40.153226], [116.428386, 40.157424], [116.391307, 40.158474], [116.303416, 40.174215], [116.177074, 40.161622], [116.155101, 40.135379], [116.137248, 40.059744], [116.108409, 40.03241], [116.110984, 40.008484], [116.092616, 39.992836], [116.101199, 39.966396], [116.115619, 39.949028], [116.118194, 39.935208], [116.154071, 39.892549], [116.138622, 39.861063], [116.116134, 39.836023], [116.119567, 39.82139], [116.115791, 39.784464], [116.102229, 39.770611], [116.096564, 39.709364], [116.112357, 39.69893], [116.22531, 39.695496], [116.264621, 39.690477], [116.304103, 39.692722], [116.364356, 39.707647], [116.39268, 39.721116], [116.489669, 39.731018], [116.552669, 39.746198], [116.605712, 39.760847], [116.628028, 39.792906], [116.704417, 39.867783], [116.712142, 39.902294], [116.708366, 39.923888], [116.703559, 39.954555], [116.710254, 39.986523], [116.670943, 40.020974], [116.625282, 40.08943], [116.62099, 40.138923], [116.52443, 40.146928], [116.485549, 40.155128]];

		$map5 = new \Base\Helper\PointInPolygon();
		$map5->setPolygon($path_5);
		$ring5 = $map5->checkPoints([[$lng, $lat]]);
		$map6 = new \Base\Helper\PointInPolygon();
		$map6->setPolygon($path_6);
		$ring6 = $map6->checkPoints([[$lng, $lat]]);
		if ($ring6[0] == false) {
			$ring = 7;
		} else {
			if ($ring5[0] == false) {
				$ring = 6;
			} else {
				$ring = 5;
			}
		}
		return $ring;
	}

	/**
	 * 计算金额
	 */
	static function getAmount($shop, $ring, $distance = null, $product_id = 1) {

		// 等待优化
		if ($shop->user && $shop->user->channel_id) {
			$channel_id = $shop->user->channel_id;
		} else {
			$channel_id = 0;
		}

		$amount = 30;

		//基础价格 筛选环线
		$product_price = \Base\Models\ProductPrice::where('product_id', $product_id)->where('end_ring', $ring)->first();

		if (!is_null($product_price)) {
			$channel_price = \Base\Models\ChannelPrice::where('product_price_id', $product_price->id)
				->where('channel_id', $channel_id)->first();

			if (!is_null($channel_price)) {
				//渠道优惠
				$amount = $product_price->price - abs($channel_price->amount);
			} else {
				$amount = $product_price->price;
			}
		}
		$amount = $amount > 0 ? $amount : 0;

		return $amount;
	}

	/**
	 * 获取快件预览信息
	 */
	static function getExpressPreview($shop, $address, $sendaddress = null) {
		$helper = new Helper;
		$lnglat = $helper->geoDecodeWithRing($address);
		$sendaddress = $sendaddress ? $helper->geoDecodeWithRing($sendaddress) : json_decode(json_encode($shop), 1);
		$_ori = $sendaddress['lng'] . ',' . $sendaddress['lat'];
		$_des = $lnglat['lng'] . ',' . $lnglat['lat'];
		$distance = $helper->getDistance($_ori, $_des);
		$amount = $helper->getAmount($shop, $lnglat['ring'], isset($distance[0]) ? $distance[0] : 5);

		$data['lnglat'] = $lnglat;
		$data['dis'] = isset($distance[0]['distance']) ? $distance[0]['distance'] : 5;
		$data['ring'] = $lnglat['ring'];
		$data['amount'] = $amount;
		return $data;
	}

	//权限
	// 4、2、1 模式 读取，编辑，删除
	static function userExpressPermission(\Base\Models\Express $express, $user) {
		// ->getHeader('authorization')
		// 无法获得header 所以 必须传user
		// $user = $user ? $user : user();
		// 准备弃用 直接使用同名函数
		$permission = 0;
		if (in_array($express->state, [0, 1])) {
			$permission = 7;
		} else {
			$permission = 4;
		}
		return $permission;
	}

	static function hoursRange($lower = 0, $upper = 86400, $step = 1800, $format = '') {
		$times = array();
		if (empty($format)) {
			$format = 'H:i';
		}
		foreach (range($lower, $upper, $step) as $increment) {
			$increment = gmdate('H:i', $increment);
			list($hour, $minutes) = explode(':', $increment);
			$date = new \DateTime($hour . ':' . $minutes);
			$times[(string) $increment] = $date->format($format);
		}
		return $times;
	}

	static function gerArea($lng, $lat) {
		$areas = \Base\Models\Area::where('is_active', 1)->get();
		foreach ($areas as $a) {
			$points = json_decode($a->points);
			$area_map = new \Base\Helper\PointInPolygon();
			$area_map->setPolygon($points);
			$check = $area_map->checkPoints([[$lng, $lat]]);
			unset($points, $area_map);
			if ($check[0]) {
				return $a;
			}
			unset($a, $check);
		}
		return null;

	}
	static function gerAreaByLayer($lng, $lat, $layer_id = 1) {
		$areas = \Base\Models\Area::where('is_active', 1)->where('layer_id', $layer_id)->get();
		foreach ($areas as $a) {
			$points = json_decode($a->points);
			$area_map = new \Base\Helper\PointInPolygon();
			$area_map->setPolygon($points);
			$check = $area_map->checkPoints([[$lng, $lat]]);
			unset($points, $area_map);
			if ($check[0]) {
				return ['area' => $a, 'layer_id' => $layer_id];
			}
			unset($a, $check);
		}
		$nid = \Base\Models\Layer::find($layer_id)->rollback_layer_id;
		if ($nid) {
			return self::gerAreaByLayer($lng, $lat, $nid);
		} else {
			return null;
		}
	}
	static function geoAddress($keywords, $cityCode = '010') {
		$client = new \GuzzleHttp\Client(['expect' => false]);
		$key = config('amap.key', 'bfdb61a0259970baca9a68b9525b8faa');
		$apiURL = config('amap.url', 'http://restapi.amap.com/v3') . '/place/text?key=' . $key . '&extensions=base&children=1&citylimit=true&offset=10&page=1&city=' . $cityCode . '&keywords=' . $keywords;
		$res = $client->request('GET', $apiURL);
		$obj = json_decode($res->getBody());
		$data = [
			'address' => null,
			'lng' => null,
			'lat' => null,
		];
		if ($obj->status == 1 and $obj->count != 0) {
			$data['address'] = $obj->pois[0]->pname . $obj->pois[0]->cityname . $obj->pois[0]->adname . $obj->pois[0]->address . $obj->pois[0]->name;
			$location = $obj->pois[0]->location;
			list($data['lng'], $data['lat']) = explode(',', $location);
		}
		return $data;
	}
	static function geoAddressWithRing($keywords, $cityCode = '010') {
		$data = self::geoAddress($keywords);
		$data['ring'] = self::getRing($data['lng'], $data['lat']);
		return $data;
	}
}

?>
