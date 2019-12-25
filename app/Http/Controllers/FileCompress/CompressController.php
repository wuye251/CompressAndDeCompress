<?php

namespace App\Http\Controllers\FileCompress;
use App\Http\Controllers\Controller;

class CompressController extends Controller
{
	//测试文件路径
	const FILEPATH = "C:/Users/Administrator/Desktop/test.txt";
	
	public $countArr = array();
	public $dict;

	//入口函数
	public function execute() 
	{

		$file = @fopen(self::FILEPATH, 'r');

		//判断文件是否存在
		if (!$file) {
			return -1;
		}

		
		$compressFile = $this->newCompressFile();

		//文件存在 首先统计文件内容字符个数
		$this->charCount($file);
		//将统计的字符进行排序
		$sortArr = $this->quickSort($this->countArr);
		//构建哈夫曼树
		$huffmanTree = $this->createHuffmanTree($sortArr);

		$this->dict = array();
		//哈夫曼编码
		$this->codeHuffman(current($huffmanTree),'', $this->dict);

		fclose($file);
		$file = @fopen(self::FILEPATH, 'r');
		if (!$file) {
			return -1;
		}
		//压缩文件
		if (-1 === $this->compressFile($file, $compressFile, $sortArr)) {
			return -1;
		}
		return 'ok,compress finish';


		fclose($file);
		fclose($compressFile);
	}

	//遍历文件，统计各个字符个数
	private function charCount($file)
	{

		//遍历整个文件内容，每个字符读取
		while (!feof($file)) {
			//按字符读取文件
			$ch = fgetc($file);


			if (!isset($this->countArr[$ch])) {
				$this->countArr[$ch]['count'] = 1;
				$this->countArr[$ch]['ch'] = $ch;
			} else {
				$this->countArr[$ch]['count'] += 1;
			}
		}

	}

	//快排将统计的字符出现次数排序
	private function quickSort($sortArr)
	{

		$len = count($sortArr);

		if ($len <= 1) return $sortArr;

		$keyArr = array_keys($sortArr);

		$flag = $sortArr[$keyArr[0]];
		
		$leftArr = array();
		$rightArr = array();

		foreach ($sortArr as $index =>  $sortArrVal) {
			if ($index === $keyArr[0]) continue;

			if ($flag['count'] > $sortArrVal['count']) {
				$leftArr[] = $sortArrVal;
				continue;
			}

			//flag <= sortArrVao['count']
			$rightArr[] = $sortArr[$index];
			continue;
		}

		// print_r($leftArr);

		$leftArr  = $this->quickSort($leftArr);
		$rightArr = $this->quickSort($rightArr);

		$retArr = array_merge($leftArr, array($flag), $rightArr);

		return $retArr;

	}

	//哈弗曼树构建
	private function createHuffmanTree($countArr)
	{
		$huffmanTree = $countArr;
		$len = count($huffmanTree);
		//内容为空 或 为1
		if ($len <= 1)
		{
			return $countArr;
		}

		$curLen = $len;

		//第一次pop两个数组 所以应该是 < curLen -1
		for ($index = 0; $index < $curLen - 1 ; $index++) {
			//将统计的节点进行count数排序
			//可以将快排排序删除
			//复杂度  usort和快排比较
			// usort($treeNode, function($node1, $node2){
			// 	if ($node1['count'] === $node2['count']) {
			// 		return 1;
			// 	}
			// 	return $node1['count'] <=> $node2['count'] ? 1 : -1;
			// });
			
			$node1 = array_shift($huffmanTree);
			$node2 = array_shift($huffmanTree);
			// echo "<br>" . "node1----^";
			// print_r($node1['count']);
			// echo "<br>" . "node2----^";
			// print_r($node2['count']);

			$huffmanTree[] = [
				'count' => $node1['count'] + $node2['count'],
				'left'  => $node1,
				'right' => $node2,
			];

			$huffmanTree = $this->quickSort($huffmanTree);
		}

		return $huffmanTree;
	}

	//哈夫曼编码
	private function codeHuffman($indexNode, $code='', &$dict)
	{
		if (isset($indexNode['ch'])) {
			$dict[$indexNode['ch']]['code'] = $code; 
		} else {
			$this->codeHuffman($indexNode['left'], $code.'0', $dict);
			$this->codeHuffman($indexNode['right'], $code.'1', $dict);		
		}
	}

	private function compressFile($inputFile, $outputFile, &$countArr)
	{
		$stringDict = serialize($this->dict);
		$stringCountCh = serialize($countArr);

		$header = pack('VV', strlen($stringDict), strlen($stringCountCh));

		fwrite($outputFile, $header);

		fwrite($outputFile, $stringDict);

		$buff = '';
		while (!feof($inputFile)) {
			$ch = fgetc($inputFile);
			if (!isset($this->dict[$ch])) {
				return -1;
			}
			$buff .= $this->dict[$ch]['code'];
			while (isset($buff[7])) {

				$char = bindec(substr($buff, 0, 8));
				fwrite($outputFile, $char);

				$buff = substr($buff, 8);
			}

		}

		//剩余buff中还有内容表示 不够8bite  需要凑够并插入
		if (!empty($buff)) {
			$char = bindec(str_pad($buff, 8, '0'));
			fwrite($outputFile, $char);
		}		
		return 'success';
	}

	private function newCompressFile()
	{
		//目录
		$savePath = dirname(self::FILEPATH);

		$inputFileName = basename(self::FILEPATH);

		//分解文件名和后缀名  取文件名
		//Array ( [0] => test [1] => txt )
		$arrFileInfo=explode('.',$inputFileName); 

		$saveName = $arrFileInfo[0] . '.compress';

		//新建文件
		$saveFilePath = $savePath . '/' . $saveName;

		//若文件已存在，先删除，在新建
		//已存在，直接清空重写

		$compressFile = fopen($saveFilePath, 'w');


		return $compressFile;
	}
}
