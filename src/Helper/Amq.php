<?php
namespace Base\Helper;

use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Spl\SplArray;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Amq {

	protected $conf;
	protected $connection;
	protected $channel;
	protected $channelId;

	use Singleton;

	public function __construct($config = null) {
		$this->conf = new SplArray($config);
		$this->connection = new AMQPStreamConnection($this->conf['host'], $this->conf['port'], $this->conf['user'], $this->conf['password']);
		$this->channel = $this->connection->channel();

	}
	public function setChannel($channelId = 'base') {
		$this->channelId = $channelId;
		//发送方其实不需要设置队列， 不过对于持久化有关，建议执行该行
		$this->channel->queue_declare($this->channelId, false, true, false, false);
	}
	public function sendMsg($content) {
		if (!is_string($content)) {
			$content = json_encode($content);
		}
		// var_dump($content);
		$msg = new AMQPMessage($content, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
		// 此处的channelId 才是发布的关键
		$res = $this->channel->basic_publish($msg, '', $this->channelId);
		// var_dump($res);
	}
	public function close() {
		$this->channel->close();
		$this->connection->close();
	}
}