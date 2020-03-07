<?php

namespace App\Http\Controllers\FileCompress;
use App\Http\Controllers\Controller;

// use App\Http\Controllers\FileCompress;

class UnCompressController extends Controller
{

	const FILEPATH = "C:/Users/Administrator/Desktop/test123.compress";

	public $format;   
	public $type;     //读取到解压文件的类型

	public function execute()
	{

		$inputFile = self::FILEPATH;

		//生成解压文件路径
		$outputFile = $this->newUnCompressFile();

		//获取压缩文件内容
		$content = file_get_contents($inputFile);

		//压缩文件中的配置内容
		$arrCountInfo = $this->getCountInfo($content);

		//读取配置信息中压缩文件类型
		$this->type = unserialize(substr($content, 12, $arrCountInfo['filetype']));
		//将之前序列化的dictLen反序列化
		$dict = unserialize(substr($content, 12 + $arrCountInfo['filetype'], $arrCountInfo['dictLen']));
// 		print_r($dict);exit;

		// $dict =  $this->getInfoToUnserialize($content, 'dictLen');

		//将编码 和 键 对换  后面读取文件内容可以通过编码快速找到
		$dict = array_flip($dict);

		//记录文件内容长度 以及 文件内容
		$bin = substr($content, 12 + $arrCountInfo['filetype'] + $arrCountInfo['dictLen']);

		//釋放content内容 防止文件内容过大  内存浪费
		unset($content);

		$this->getComressFile($bin);
			
		return 'success';
	}

	/**
	 * 将头部的文件相关描述信息解析
	 * @param  [string] $content [文件内容]
	 * @return [array]          [dictLen信息和contenLen信息]
	 */
	private function getCountInfo($content)
	{

		$arrCountInfo = unpack('Vfiletype/VdictLen/VcontentLen', $content);
		if (empty($arrCountInfo)) {
			return -1;
		}

		return $arrCountInfo;
	}

// 	private function getInfoToUnserialize($content, $getKey)
// 	{
// 		$retInfo = unserialize(substr($content, 8, $content[$getKey]));

// 		return $retInfo;
// 	}

	private function newUnCompressFile()
	{
		//目录
		$savePath = dirname(self::FILEPATH);

		$inputFileName = basename(self::FILEPATH);

		//分解文件名和后缀名  取文件名
		//Array ( [0] => test [1] => txt )
		$arrFileInfo = explode('.',$inputFileName); 

		// $saveName = $arrFileInfo[0] . ".$this->format";
		$saveName = $arrFileInfo[0] . '1' . "$this->type";

		//新建文件
		$saveFilePath = $savePath . "/$saveName" ;

		return $saveFilePath;
	}


	/**
	 * 转换解压文件
	 * @param  [string] $contend    [文件内容]
	 * @param  [int]    $contendLen [文件长度]
	 * @param  [PATH] $outputFile   [输出文件]
	 */
	private function getComressFile($contend, $contendLen, $outputFile)
	{
		$compContentLen = strlen($bin);
		
		$contentIndex = 0;
		$outputContent = '';
		//从文件内容开始遍历
		$curIndex = 0;
		$codeKey = '';

		while ($contentIndex < $compContentLen && $curIndex < $contendLen) {
			//当前字符的二进制
			$binaryCh = decbin(ord($bin[$contentIndex]));

			//凑够八个字节  不够向左侧添0  不够8个字节情况 00000001 转为了1
			$binaryCh = str_pad($binaryCh, 8,'0', STR_PAD_LEFT);

			$biteIndex = 0;

			for(; $biteIndex < 8; $biteIndex++) {
				//每增加一bite都去查找dict是否有对应键
				$codeKey .= $binaryCh[$biteIndex];
				
				//如果有对应字典  则将对应的解压字符转换并 添加在输出文件后
				if (isset($dict[$codeKey])) {
					$outputContent .= $dict[$codeKey]; 
					// print_r($outputContent);echo "<br>"; 
					$codeKey = '';
					$curIndex++;
				}
			}
			$contentIndex++;
		}

		$ret = file_put_contents($outputFile, $outputContent);
		return $ret;
	}
}

