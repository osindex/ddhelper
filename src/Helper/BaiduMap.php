<?php
namespace Base\Helper;

class BaiduMap {
	static function entityAdd($entity_name, $entity_desc = null, ...$columnKey) {
		$ak = Setting::get('yingyan_ak', 'LevfnaqxGwFpx9OuAawPmQX5hNnZUuav');
		$client = new \GuzzleHttp\Client(['expect' => false]);
		$apiURL = 'http://yingyan.baidu.com/api/v3/entity/add';
		$body = compact('ak', 'entity_name', 'entity_desc', $columnKey);
		try {
			$res = $client->request('POST', $apiURL, $body);
		} catch (\GuzzleHttp\Exception\RequestException $e) {
			return 0;
		}
		$obj = json_decode($res->getBody());
		if ($obj->status == 0 && $obj->message == '成功') {
			return 1;
		} else {
			return 0;
		}
		// {
		//     "status": 0,
		//     "message": "成功"
		// }
	}
	static function trackAddpoints($service_id, Array $points) {
		$ak = Setting::get('yingyan_ak', 'LevfnaqxGwFpx9OuAawPmQX5hNnZUuav');
		$apiURL = 'http://yingyan.baidu.com/api/v3/track/addpoints';
		$client = new \GuzzleHttp\Client(['expect' => false]);
		// wgs84：GPS 坐标
		// gcj02：国测局加密坐标
		// bd09ll：百度经纬度坐标 百度默认
		// $point = [
		// 	"entity_name" => "请声明终端编号",
		// 	"loc_time" => "请传入UNIX时间",
		// 	"latitude" => "请传入LAT参数",
		// 	"longitude" => "请传入LNG参数",
		// 	"coord_type_input" => "请声明坐标系",
		// 	"speed" => null,
		// 	"direction" => null,
		// 	"height" => null,
		// 	"radius" => null,
		// 	"object_name" => null,
		// 	"city" => null,
		// 	"province" => null,
		// ];
		// 放到接口前检查吧
		$body = [
			'ak' => $ak,
			'service_id' => $service_id,
			'point_list' => $points];
		try {
			$res = $client->request('POST', $apiURL, $body);
		} catch (\GuzzleHttp\Exception\RequestException $e) {
			return 0;
		}
		$obj = json_decode($res->getBody());
		if ($obj->status == 0 && $obj->message == '成功') {
			return $obj->success_num;
		} else {
			return 0;
		}
		// "status": 0,
		//   "message": "成功"
		//   "success_num": 1,
		//   "fail_info": {
		//     "param_error": [
		//       {
		//         "entity_name": 37286234,
		//         "loc_time": 123,
		//         "latitude": 23.34,
		//         "longitude": 134.43,
		//         "coord_type_input": "wgs84",
		//         "speed":27.23,
		//         "direction":178,
		//         "height":173.3,
		//         "radius":32,
		//         "object_name":"12836",
		//         "city": "guangzhou",
		//         "province": "guangdong",
		//         "error":"entity_name 类型不匹配"
		//       }
		//     ],
		//     "internal_error": []
		//   }
	}
}

?>
