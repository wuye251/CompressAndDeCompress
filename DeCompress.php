<?php

class DeCompress
{

	// const FILEPATH = "./吴烨.compress";
	const FILEPATH = "./mpTest.compress";
	
	public $format;   
	public $type;     //读取到解压文件的类型

	public function execute()
	{

		$inputFile = self::FILEPATH;

		//获取压缩文件内容
		$content = file_get_contents($inputFile);

		//压缩文件中的配置内容
		$arrCountInfo = $this->getCountInfo($content);

		//读取配置信息中压缩文件类型
		//12 = 3个配置信息 每个32字节 4位长度 * 3 = 12
		// $this->type = unserialize(substr($content, 12, $arrCountInfo['filetype']));
		$this->type = substr($content, 12, $arrCountInfo['filetype']);

		// //将之前序列化的dictLen反序列化
		$this->dict = unserialize(substr($content, 12 + $arrCountInfo['filetype'], $arrCountInfo['dictLen']));

		// $dict =  $this->getInfoToUnserialize($content, 'dictLen');

		//将编码 和 键 对换  后面读取文件内容可以通过编码快速找到
		$this->dict = array_flip($this->dict);

		//记录文件内容长度 以及 文件内容
		$zipContent = substr($content, 12 + $arrCountInfo['filetype'] + $arrCountInfo['dictLen']);

		//釋放content内容 防止文件内容过大  内存浪费
		unset($content);

		//生成解压文件路径
		$outputFile = $this->newUnCompressFile();

		$this->getComressFile($zipContent, $arrCountInfo['contentLen'], $outputFile);
		echo "success";
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

	private function newUnCompressFile()
	{
		//目录
		$savePath = dirname(self::FILEPATH);

		$inputFileName = basename(self::FILEPATH);

		//分解文件名和后缀名  取文件名
		//Array ( [0] => test [1] => txt )
		$arrFileInfo = explode('.',$inputFileName); 

		// $saveName = $arrFileInfo[0] . ".$this->format";
		$saveName = $arrFileInfo[0] . '1' . ".$this->type";

		//新建文件
		$saveFilePath = $savePath . "/$saveName" ;

		$deCompressFile = fopen($saveFilePath, 'w');

		return $deCompressFile;
	}


	/**
	 * 转换解压文件
	 * @param  [string] $contend    [文件内容]
	 * @param  [int]    $contentLen [文件长度]
	 * @param  [PATH] $outputFile   [输出文件]
	 */
	private function getComressFile($zipContent, $contentLen, $outputFile)
	{
		$compContentLen = strlen($zipContent);

		$contentIndex = 0;
		$outputContent = '';
		//原文件内容位置
		$curIndex = 0;
		$codeKey = '';

		while ($contentIndex < $compContentLen && $curIndex < $contentLen) {
			//当前字符的二进制
			$binaryCh = decbin(ord($zipContent[$contentIndex]));
			
			//凑够八个字节  不够向左侧添0  不够8个字节情况 00000001 转为了1
			$binaryCh = str_pad($binaryCh, 8,'0', STR_PAD_LEFT);
			print($binaryCh);echo"<br>";
			echo "codekey-->";print($codeKey);echo"<br>";
			$biteIndex = 0;

			for(; $biteIndex < 8; $biteIndex++) {
				//如果文件已经遍历完
				// if ($contentIndex >= $contentLen) break;

				//每增加一bite都去查找dict是否有对应键
				$codeKey .= "$binaryCh[$biteIndex]";
				//如果有对应字典  则将对应的解压字符转换并 添加在输出文件后
				if (isset($this->dict[$codeKey])) {
					// print($codeKey);echo"<br>";
					$outputContent .= $this->dict[$codeKey]; 
					// print_r($outputContent);echo "<br>"; 
					$codeKey = '';
					$curIndex++;
				}
			}
			$contentIndex++;
		}

		$ret = fwrite($outputFile, $outputContent);
		return $ret;
	}
}

$decompress = new DeCompress();
$decompress->execute();


