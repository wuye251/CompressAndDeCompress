<?php

namespace App\Http\Controllers\FileCompress;
use App\Http\Controllers\Controller;


class UnCompressController extends Controller
{

	const FILEPATH = "C:/Users/Administrator/Desktop/test.compress";

	public $format;

	public function execute()
	{

		$inputFile = self::FILEPATH;

		// $file = @fopen($inputFile, 'r');

		// if (!$file) {
			// return -1;
		// }

		// $this->format = fgets($file);


		$outputFile = $this->newUnCompressFile();

		$content = file_get_contents($inputFile);

		$arrCountInfo = $this->getCountInfo($inputFile);
		$dict = unserialize(substr($content, 8, $arrCountInfo['dictLen']));
		// $dict =  $this->getInfoToUnserialize($content, 'dictLen');
		
		$dict = array_flip($dict);

		$bin = substr($content, 8 + $arrCountInfo['dictLen']);

		print_r($bin);exit;
		$output = '';
		$key = '';
		$decodedLen = 0;
		$i = 0;
		while (isset($bin[$i]) && $decodedLen !== $arrCountInfo['contentLen']) {

			$bits = decbin(ord($bin[$i]));
			$bits = str_pad($bits, 8,'0', STR_PAD_LEFT);
			for ($j = 0; $j !== 8; $j++) {
        		// 每拼接上 1-bit，就去与字典比对是否能解码出字符
        		$key .= $bits[$j];
        		if (isset($dict[$key])) {
            		$output .= $dict[$key];
            		$key = '';
            		$decodedLen++;
            		if ($decodedLen === $arrCountInfo['contentLen']) {
                		break;
            		}
        		}
    		}
    		$i++;
		}

		$ret = file_put_contents($outputFile, $output);
		
		// fclose($file);
		// fclose($outputFile);		
		return 'success';
	}

	private function getCountInfo($content)
	{

		$arrCountInfo = unpack('VdictLen/VcontentLen', $content);
		if (empty($arrCountInfo)) {
			return -1;
		}
		return $arrCountInfo;
	}

	private function getInfoToUnserialize($content, $getKey)
	{
		$retInfo = unserialize(substr($content, 8, $content[$getKey]));

		return $retInfo;
	}

	private function newUnCompressFile()
	{
		//目录
		$savePath = dirname(self::FILEPATH);

		$inputFileName = basename(self::FILEPATH);

		//分解文件名和后缀名  取文件名
		//Array ( [0] => test [1] => txt )
		$arrFileInfo = explode('.',$inputFileName); 

		// $saveName = $arrFileInfo[0] . ".$this->format";
		$saveName = $arrFileInfo[0] . ".txt";

		//新建文件
		$saveFilePath = $savePath . "/$saveName" ;

		//若文件已存在，先删除，在新建
		//已存在，直接清空重写
		// $outputFile = fopen($saveFilePath, 'w');


		return $saveFilePath;
	}

}