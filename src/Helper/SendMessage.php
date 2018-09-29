<?php
namespace Base\Helper;
use Base\Models\AccountPayment;
use Base\Models\AdminMessage;
use Base\Models\DriverMessage;
use Base\Models\Express;
use Base\Models\ExpressRoute;
use Base\Models\User;
use Base\Models\UserMessage;

// use PhpAmqpLib\Connection\AMQPStreamConnection;
// use PhpAmqpLib\Message\AMQPMessage;

class SendMessage {

	// type 1 基本通知
	// type 2 通知并且可以打开到 通知 页面 target_id = 通知内容ID
	// type 3 通知并且可以打开到 详情 页面 target_id = express_id

	// 约定 新闻的接口 get api/notice 查看通知内容具体的接口 api/notice/show?id=xxx
	// 返回的内容暂定 id,title,content,state,created_at state == 10 已读 【< 10 未读】
	static function sendToDriverNewRoute(ExpressRoute $expressRoute) {
		//司机新任务
		$expressRoute->load('toDriver');
		$express = $expressRoute->express;

		$to = $expressRoute->toDriver->push_id;
		if ($to) {
			// 推送不需要APP名称
			$content = "你有新的快件任务(" . $express->est_rec_area . $express->ddid . ")，请及时查看。";
			$extras = [
				'target_id' => $express->id,
				'type' => 3,
			];
			$content = json_encode(compact('content', 'to', 'extras'));
			$title = '司机新任务指派';
			$is_app = true;
			$driver_id = $expressRoute->to_driver_id;
			$state = 0;
			$data = compact('title', 'driver_id', 'content', 'is_app', 'state');
			DriverMessage::create($data);
		} else {
			// 没有激活APP的时候发短信?
			$to = $expressRoute->toDriver->mobile;
			if ($to) {
				$content = config('sms.name') . "你有新的快件任务(" . $express->est_rec_area . $express->ddid . ")，请及时登录app查看。";
				$content = json_encode(compact('content', 'to'));
				$title = '司机新任务指派';
				$is_sms = true;
				$driver_id = $expressRoute->to_driver_id;
				$state = 0;
				$data = compact('title', 'driver_id', 'content', 'is_sms', 'state');
				DriverMessage::create($data);
			}
		}

	}

	static function sendToSendOrder(User $user, $is_send_sms = 1) {
		//发给用户
		// $amqs = Amq::getInstance();
		// var_dump($amqs);
		$to = $user->mobile;
		if ($to && $is_send_sms) {
			$content = config('sms.name') . "尊敬的客户，您的订单我们已经收到。叮咚传送员将尽快与您取得联系，请保持手机畅通。";
			$content = json_encode(compact('content', 'to'));
			$title = '快件发件成功客户提示';
			$is_sms = true;
			$user_id = $user->id;
			$state = 0;
			$data = compact('title', 'user_id', 'content', 'is_sms', 'state');
			// $amq = [
			// 	'type' => 'dm',
			// 	'data' => $data,
			// ];
			// Amq::getInstance()->sendMsg($amq);
			UserMessage::create($data);
		}
	}

	static function sendToSend(ExpressRoute $expressRoute) {
		$expressRoute->load('toDriver');
		$to = $expressRoute->express->send_mobile;
		$is_send_sms = $expressRoute->express->is_send_sms;
		if ($to && $is_send_sms) {
			$content = config('sms.name') . "您好，您的订单“电话 " . $expressRoute->express->rec_mobile . "，地址 " . $expressRoute->express->rec_address . "”已由传送员 " . $expressRoute->toDriver->name . " " . $expressRoute->toDriver->mobile . " 送达。";
			$content = json_encode(compact('content', 'to'));
			$title = '快件:[' . $expressRoute->express->id . '] 送达客户提示';
			$is_sms = true;
			$user_id = $expressRoute->express->user_id;
			$state = 0;
			$data = compact('title', 'user_id', 'content', 'is_sms', 'state');
			UserMessage::create($data);
		}
	}
	static function sendToRec(Express $express,$delay=0) {
		if ($express->number && $express->is_sms) {
			// $load('express');
			$url = url($express->number);
			// $url = url('wechatmessageview/' . $express->number);

			// 短网址文档 http://open.weibo.com/wiki/2/short_url/shorten
			// GET
			// https://api.weibo.com/2/short_url/shorten.json?source=2500546949&url_longurl(=12345)6
			// return
			// {"urls":[{"result":true,"url_short":"http://t.cn/RmhWAC4","url_long":url("123456","object_type":"","type":0,"object_id":""}])}
			$delivery = self::deliveryTime($express);
			$content = config('sms.name') . "您有一票来自<" . $express->send_name . ">的快件正在派送，配送员 " . $express->expressLatest->toDriver->name . "（" . $express->expressLatest->toDriver->mobile . "）, 可点击 " . $url . " 查看运送状态。";
			$to = trim($express->rec_mobile);
			$content = json_encode(compact('content', 'to'));
			$title = '快件:[' . $express->id . '] 取件提示';
			$is_sms = true;
			$user_id = 0;
			$state = 0;

			$send_after = NULL;
			if($delay > 0){
				$send_after = \Carbon\Carbon::now()->copy()->addMinutes($delay);
			}

			$data = compact('title', 'user_id', 'content', 'is_sms', 'state','send_after');
			// 应该有个customermessage 但是 也可以归纳到user
			UserMessage::create($data);
		}
	}
	static function sendToAdminSingle(Express $express, $openids = null) {
		if ($express->number) {
			$title = '单件下单:[' . $express->id . '] 管理员提示';
			$is_wechat = true;
			$admin_id = 0;
			// 可以把ding_notice和admin_id关联
			$state = 0;

			# 有人单件下单 给 设置的管理员 发送模版消息

			$openids = $openids ?? config('dingdong.ding_notice');
			foreach ($openids as $to) {
				$touser = $to;
				$template_id = config('dingdong.ding_new_tpl');
				$url = url('wechatmessageview/' . $express->number);
				// route($express->est_rec_area.$express->ddid)
				$color = '#FF0000';
				$data = [
					"first" => "有新的快件。\n",
					"keyword1" => $express->send_name ? $express->send_name : ($express->shop ? $express->shop->name : '--'),
					"keyword2" => $express->send_mobile ? $express->send_mobile : ($express->shop ? $express->shop->send_mobile : '--'),
					"keyword3" => $express->send_address ? $express->send_address : ($express->shop ? $express->shop->send_address : '--'),
					"remark" => "【叮咚号】" . $express->est_rec_area . $express->ddid . "\n【收件人】" . $express->rec_name . " (" . $express->rec_mobile . ")\n【地址】" . $express->rec_address . "\n【发件时间】" . $express->created_at . "\n\n", //【建议】$addMessage",
				];
				$content = compact('touser', 'template_id', 'url', 'data');
				$content = json_encode(compact('content', 'to'));
				$data = compact('title', 'admin_id', 'content', 'is_wechat', 'state');
				AdminMessage::create($data);
			}
		}
	}
	// send_name
	// send_mobile
	// send_address
	// count
	static function sendToAdminBatch(Array $sendInfo = [], $openids = null) {
		if (!$sendInfo) {
			$sendInfo['count'] = '未知';
		}
		$title = '批量下单:[' . $sendInfo['count'] . ' 件] 管理员提示';
		$is_wechat = true;
		$admin_id = 0;
		// 可以把ding_notice和admin_id关联
		$state = 0;

		$time = \Carbon\Carbon::now()->toDateTimeString();
		# 批量下单 给 设置的管理员 发送模版消息
		$openids = $openids ?? config('dingdong.ding_notice');
		foreach ($openids as $to) {
			$touser = $to;
			$template_id = config('dingdong.ding_new_tpl');
			$url = '';
			$data = array(
				"first" => "有新的快件。\n",
				"keyword1" => $sendInfo['send_name'] ?? '--',
				"keyword2" => $sendInfo['send_mobile'] ?? '--',
				"keyword3" => $sendInfo['send_address'] ?? '--',
				"remark" => "\n批量发件，共计【" . $sendInfo['count'] . "】件\n【发件时间】" . $time . "\n", //【建议】$addMessage",
			);
			$content = compact('touser', 'template_id', 'url', 'data');
			$content = json_encode(compact('content', 'to'));
			$data = compact('title', 'admin_id', 'content', 'is_wechat', 'state');
			AdminMessage::create($data);
		}
	}

	static function sendToCharge(AccountPayment $payment, $openids = null) {
		# 充值成功 给自己 以及 给设置的管理员 发送模版消息
		// App\HttpController\Component\wechat\noticePayment 已经完成了此功能 不再处理
		$user = $payment->user;
		$paytype = '充值'; //;\Base\Helper\Helper::payTypeString($payment->paytype);
		// 0 充值
		$is_wechat = 1;
		$state = 0;
		$admin_id = 0;
		$title = '用户[' . $user->id . ']充值:[' . abs($payment->amount) . ' ] 提示';
		if ($user and $user->openid and $payment->state == 1) {
			// 不再发送
			// $config = config('wechat');
			// $wechat = Wechatbase::officialAccount($config);
			// $notice = $wechat->template_message;
			// 不发了 不发了 老是出问题。
			$userId = $user->openid;
			$template_id = config('dingdong.ding_payment_tpl');
			$url = '';
			$color = '#FF0000';
			$data = array(
				"first" => "您的账户余额发生变动。\n",
				"keyword1" => $payment->updated_at->format('Y-m-d H:i:s'),
				"keyword2" => $payment->amount > 0 ? '收入' : '支出',
				"keyword3" => abs($payment->amount),
				"keyword4" => $user->shop ? rtrim(rtrim($user->shop->balance, '0'), '.') : '-',
				"remark" => "\n变动类型「 $paytype 」。账户总额以系统最终显示余额为准。",
			);

			$first = "<" . ($user->shop ? $user->shop->name : '-') . "> 的账户余额发生变动。\n";
			$data_to_admin = array(
				"first" => $first,
				"keyword1" => $payment->updated_at->format('Y-m-d H:i:s'),
				"keyword2" => '收入',
				"keyword3" => abs($payment->amount),
				"keyword4" => $user->shop ? rtrim(rtrim($user->shop->balance, '0'), '.') : '-',
				"remark" => "\n变动类型「 $paytype 」。账户总额以系统最终显示余额为准。",
			);
			$send = [
				'touser' => $userId,
				'template_id' => $template_id,
				'url' => $url,
				'color' => $color,
				'data' => $data,
			];
			$user_id = $user->id;
			$adddata = compact('title', 'user_id', 'is_wechat', 'state');
			$adddata['content'] = json_encode(['to' => $userId, 'content' => $send]);
			// $result = $notice->send($send);
			// if (isset($result['errcode']) && $result['errcode'] != 0) {
			// 	$adddata['state'] = 0;
			// }
			UserMessage::create($adddata);
			$configUsers = $openids ?? config('dingdong.ding_notice');
			foreach ($configUsers as $openid) {
				if ($openid != null) {
					$send = [
						'touser' => $openid,
						'template_id' => $template_id,
						'url' => $url,
						'color' => $color,
						'data' => $data_to_admin,
					];
					$adddata = compact('title', 'admin_id', 'is_wechat', 'state');
					$adddata['content'] = json_encode(['to' => $openid, 'content' => $send]);
					// $result = $notice->send($send);
					// if (isset($result['errcode']) && $result['errcode'] != 0) {
					// 	$adddata['state'] = 0;
					// }
					AdminMessage::create($adddata);
				}
			}
		}
	}
	static function sendToDriverCancle(Express $express, $reason = null) {
		$express->load('expressOneRoute.toDriver');
		try {
			$driver_id = $express->expressOneRoute->toDriver->id;
			$to = $express->expressOneRoute->toDriver->push_id;
			$state = 0;
			if ($to) {
				$title = '快件取消:[' . $express->est_rec_area . $express->ddid . '] (内码:' . $express->id . ')';
				$content = config('sms.name') . "快件 " . $express->est_rec_area . $express->ddid . " 已被取消，取件地址 " . $express->send_address . "，请留意。";
				$extras = [
					'target_id' => $express->id,
					'type' => 3,
				];
				$content = json_encode(compact('content', 'to', 'extras'));
				$is_app = true;
				$data = compact('title', 'driver_id', 'content', 'is_app', 'state');
				DriverMessage::create($data);
			} else {
				$to = $express->expressOneRoute->toDriver->mobile;
				if ($to) {
					$title = '快件取消:[' . $express->est_rec_area . $express->ddid . '] (内码:' . $express->id . ')';
					// 司机更熟悉这个id
					$is_sms = true;
					$content = json_encode([
						"to" => $to,
						"content" => config('sms.name') . "快件 " . $express->est_rec_area . $express->ddid . " 已被取消，取件地址 " . $express->send_address . "，请留意。",
					]);
					$data = compact('title', 'driver_id', 'is_sms', 'state', 'content');
					DriverMessage::create($data);
				}
			}
		} catch (\Exception $e) {
		}
	}
	static function sendToAdminCancle(Express $express, $reason, $openids = null) {
		# 取消快件待审核 模版消息
		$openids = $openids ?? config('dingdong.ding_notice');

		$template_id = config('dingdong.ding_cancel_tpl');
		$admin_id = 0;
		$state = 0;
		$is_wechat = 1;
		$url = '';
		$color = '#FF0000';
		$shopname = $express->shop ? $express->shop->name : '--';

		if ($express->state == 98) {
			$title = '[管理]有新的订单被取消,等待审核。';
			$remark = "\n操作时间：" . date('Y-m-d H:i:s') . "\n操作账户: " . $shopname . "\n\n请前往后台审核。";
			// 待审核
		} else {
			$title = '[管理]有新的订单已被取消。';
			$remark = "\n操作时间：" . date('Y-m-d H:i:s') . "\n被操作账户: " . $shopname . "\n\n";
			// 已通过
		}
		foreach ($openids as $to) {
			$data = [
				"first" => $title . "\n",
				"keyword1" => $express ? $express->est_rec_area . $express->ddid : '--',
				"keyword2" => $reason,
				"remark" => $remark,
			];
			$send = [
				'touser' => $to,
				'template_id' => $template_id,
				'url' => $url,
				'color' => $color,
				'data' => $data,
			];
			$adddata = compact('title', 'admin_id', 'is_wechat', 'state');
			$adddata['content'] = json_encode(['to' => $to, 'content' => $send]);
			AdminMessage::create($adddata);
		}
	}
	static function sendToUserCancle(Express $express, $reason = null) {
		if ($express->send_mobile && $express->is_send_sms) {
			$title = '快件取消:[' . $express->id . ']';
			$user_id = $express->id;
			$is_web = true;
			$state = 0;
			// 默认发送
			$to = $express->send_mobile;
			$content = json_encode([
				"to" => $to,
				"content" => config('sms.name') . "快件 " . $express->est_rec_area . $express->ddid . " 已被取消，请留意。",
			]);
			$data = compact('title', 'user_id', 'is_web', 'state', 'content');
			UserMessage::create($data);
		}
	}
	static function deliveryTime($express) {
		if ($express->delivery_before && $express->delivery_after) {
			if ($express->delivery_before == $express->delivery_after) {
				return substr($express->delivery_after, 2, 14);
			} elseif (substr($express->delivery_before, 0, 10) == substr($express->delivery_after, 0, 10)) {
				return substr($express->delivery_after, 2, 14) . ' - ' . substr($express->delivery_before, 11, 5);
			} else {
				return substr($express->delivery_after, 2, 16) . ' - ' . substr($express->delivery_before, 0, 16);
			}
		} else {
			return '当日';
		}
	}
	static function sendToUserPick(Express $express, $drivermobile = '-') {
		if ($express->send_mobile && $express->is_pickup_sms) {
			$title = '快件取件:[' . $express->id . ']';
			$user_id = $express->user_id;
			$is_sms = true;
			$state = 0;
			// 默认发送
			$to = $express->send_mobile;
			$name = $express->shop->name ? $express->shop->name : "-";
			$content = json_encode([
				"to" => $to,
				"content" => config('sms.name') . "尊敬的客户，您的“" . $name . "”快件已下单成功，快件叮咚码为“" . $express->est_rec_area . $express->ddid . "”，请在取货司机到达时与司机核对叮咚码。取货司机已出发，联系电话：“" . $drivermobile . "”。祝您生活愉快！",
				// 【叮咚传送】尊敬的客户，您的“, #accountname#”快件已下单成功，快件叮咚码为“#ddid#” “#drivermobile#”。祝您生活愉快！
			]);
			$data = compact('title', 'user_id', 'is_sms', 'state', 'content');
			UserMessage::create($data);
		}
	}
}

?>
