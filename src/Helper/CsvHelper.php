<?php

namespace Base\Helper;

class CsvHelper {
	/**
	 * [putCsv description]
	 * @param  array  $head     ['id'=>'序号','name'=>'标题','relation.name'=>'二级标题']
	 * @param  [type] $data     laravel $sql句柄
	 * @param  string $mark     [description]
	 * @param  string $fileName [description]
	 * @return [type]           [description]
	 */
	static function putCsv(array $head, $data, $mark = 'dml', $limit = 100000, array $desc = []) {
		set_time_limit(0);
		$sqlCount = $data->count();

		// 10W 级别
		// 每次只从数据库取100000条以防变量缓存太大
		// 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
		$limit = 100000;
		// buffer计数器
		$cnt = 0;
		$fileNameArr = [];
		// 逐行取出数据，不浪费内存
		for ($i = 0; $i < ceil($sqlCount / $limit); $i++) {
			$fp = fopen($mark . '_' . $i . '.csv', 'w'); //生成临时文件
			//     chmod('attack_ip_info_' . $i . '.csv',777);//修改可执行权限
			$fileNameArr[] = $mark . '_' . $i . '.csv';
			// 将数据通过fputcsv写到文件句柄
			fputcsv($fp, array_merge($head, $desc));
			$dataArr = $data->offset($i * $limit)->limit($limit)->get()->toArray();
			foreach ($dataArr as $a) {
				$cnt++;
				if ($limit <= $cnt) {
					//刷新一下输出buffer，防止由于数据过多造成问题
					if (ob_get_level() > 0) {
						ob_flush();
						flush();
						$cnt = 0;
					}
				}
				$temp = [];
				foreach ($head as $key => $value) {
					$k = explode('.', $key);
					if (count($k) > 1) {
						$temp[] = $a[$k[0]][$k[1]];
					} else {
						$temp[] = $a[$key] ?? null;
					}
				}
				fputcsv($fp, $temp);
				// fputcsv($fp, array_only($a, array_keys($head)));
			}
			fclose($fp); //每生成一个文件关闭
		}
		//进行多个文件压缩
		$zip = new \ZipArchive();
		$filename = $mark . ".zip";
		$zip->open($filename, \ZipArchive::CREATE); //打开压缩包
		foreach ($fileNameArr as $file) {
			$zip->addFile($file, basename($file)); //向压缩包中添加文件
		}
		$zip->close(); //关闭压缩包
		foreach ($fileNameArr as $file) {
			unlink($file); //删除csv临时文件
		}
		$baseFileName = basename($filename);
		$OssFileName = 'zip/' . date('Y-m-d') . '/' . date('H_i_s') . mt_rand(0, 9999) . '_' . $baseFileName;
		// 上传到阿里云
		// \Base\Helper\OSS::privateUpload('ddchuansong', $OssFileName, $filename, ['ContentType' => 'application/zip']);
		// @unlink($filename);
		return ossUrl($OssFileName);
	}
}