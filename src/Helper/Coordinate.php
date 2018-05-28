<?php
namespace Base\Helper;
class Coordinate {
	public $x = 0;
	public $y = 0;
	/**
	 * Coordinate constructor.
	 * @param $lon float 经度
	 * @param $lat float 纬度
	 */
	public function __construct($lon, $lat) {
		$this->x = number_format($lon, 6);
		$this->y = number_format($lat, 6);
	}
	public function __toString() {
		return $this->x . ',' . $this->y;
	}
}