<?php
/**
 *  @author : Michael Russell
 *  @version : v2.0
 *
 *  @return : boolean
 *
 *  @desc : Class performs raycasting on polygon to find if points exist within the boundries provided.
 *    This uses a rqycasting algorithm for determining if the point lies withing the boundries of a polygon.
 *    Link provided explains what this process is and how it works.
 *
 *  https://rosettacode.org/wiki/Ray-casting_algorithm
 *  https://github.com/veracity4life/point-in-poly
 */
namespace Base\Helper;

class PointInPolygon {
	public $polygon;
	private $checkResult;
	//  @param: array( array( lat, lng ), array( y, x ));
	// protected function __construct($poly = null)
	// {
	//     if ($poly != null) {
	//         $this->polygon = $poly;
	//     }
	// }
	//  @param: array( array( lat, lng ), array( y, x ));
	public function setPolygon($array) {
		$this->polygon = $array;
		if (!is_null($this->polygon)) {
			array_push($this->polygon, $array[0]);
			$this->checkResult = false;
		}
	}
	//  @param: array( array( lat, lng ), array( y, x ));
	public function checkPoints($points) {
		$inPoly = array();
		$polyVertices = count($this->polygon);
		if ($polyVertices < 5) {
			return $this->checkRectangle($points);
		}
		foreach ($points as $key => $point) {
			$inPoly[$key] = false;
			$intersections = 0;
			for ($i = 1; $i < $polyVertices; $i += 1) {
				$v1 = $this->polygon[$i - 1];
				$v2 = $this->polygon[$i];
				//  Check if point is within min/max of vertices and determine amount of intersections
				if ($point[1] > min($v1[1], $v2[1])
					&& $point[1] <= max($v1[1], $v2[1])
					&& $point[0] <= max($v1[0], $v2[0])
					&& $v1[1] != $v2[1]
				) {
					$lngInters = ($point[1] - $v1[1]) * ($v2[0] - $v1[0]) / ($v2[1] - $v1[1]) + $v1[0];
					if ($v1[0] == $v2[0] || $point[0] <= $lngInters) {
						$intersections += 1;
					}
				}
			}
			//  Intersections must be an odd number in order for the point to reside inside the polygon
			if ($intersections % 2 != 0) {
				$inPoly[$key] = true;
			}
		}
		$this->checkResult = $inPoly;
		return $this->checkResult;
	}
	public function getCheckResult() {
		return $this->checkResult;
	}
	//  Checking for a 4 sided polygon and executing simpler logic to determine if the point exists within the polygon
	//  @param: array( array( lat, lng ), array( y, x ));
	public function checkRectangle($points) {
		$inPoly = array();
		$polyVertices = count($this->polygon);
		foreach ($points as $key => $point) {
			$HighLow = array('latHigh' => $point[0], 'latLow' => $point[0], 'longHigh' => $point[1], 'longLow' => $point[1]);
			$inPoly[$key] = false;
			for ($i = 0; $i < $polyVertices; $i += 1) {
				$v = $this->polygon[$i];
				$HighLow['latHigh'] = ($HighLow['latHigh'] < $v[0]) ? $v[0] : $HighLow['latHigh'];
				$HighLow['latLow'] = ($HighLow['latLow'] > $v[0]) ? $v[0] : $HighLow['latLow'];
				$HighLow['longHigh'] = ($HighLow['longHigh'] < $v[1]) ? $v[1] : $HighLow['longHigh'];
				$HighLow['longLow'] = ($HighLow['longLow'] > $v[1]) ? $v[1] : $HighLow['longLow'];
			}
			if ($point[0] > $HighLow['latLow'] && $point[0] < $HighLow['latHigh'] && $point[1] > $HighLow['longLow'] && $point[1] < $HighLow['longHigh']) {
				$inPoly[$key] = !$inPoly[$key];
			}
		}
		$this->checkResult = $inPoly;
		return $inPoly;
	}
}
