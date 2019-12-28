<?php

namespace App\Http\Controllers\FileCompress;
use App\Http\Controllers\Controller;

class CompressController extends Controller
{
	//测试文件路径
	const FILEPATH = "C:/Users/Administrator/Desktop/test.txt";
	
	public $content;
	public $countArr = array();
	public $dict = array();
	public $format;


	//入口函数
	public function execute() 
	{

		$content = file_get_contents(self::FILEPATH);
		//判断文件是否存在
		if (!$content) {
			return -1;
		}
		//创建压缩文件 并且获取压缩前文件属性 format
		$compressFile = $this->newCompressFile();
		
		//文件存在 首先统计文件内容字符个数
		$this->charCount($content);

		//将统计的字符进行排序
		$sortArr = $this->quickSort($this->countArr);
		

		//构建哈夫曼树
		$huffmanTree = $this->createHuffmanTree($sortArr);

		//哈夫曼编码
		$this->codeHuffman(current($huffmanTree),'', $this->dict);

		//压缩文件
		if (-1 === $this->compressFile($content, $compressFile, $sortArr)) {
			return -1;
		}
		return 'ok,compress finish';


		fclose($file);
		fclose($compressFile);
	}

	//遍历文件，统计各个字符个数
	private function charCount($content)
	{

		$len = strlen($content);

		//遍历整个文件内容，每个字符读取
		$index = 0;
		while ($len) {
			//按字符读取文件
			$ch = $content[$index];

			if (!isset($this->countArr[$ch])) {
				$this->countArr[$ch]['count'] = 1;
				$this->countArr[$ch]['ch'] = $ch;
			} else {
				$this->countArr[$ch]['count'] += 1;
			}

			$index++;
			$len--;
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
			$dict[$indexNode['ch']] = $code; 
		} else {
			$this->codeHuffman($indexNode['left'], $code.'0', $dict);
			$this->codeHuffman($indexNode['right'], $code.'1', $dict);		
		}
	}

	private function compressFile($content, $outputFile, &$countArr)
	{

		$stringDict    = serialize($this->dict);

		$header = pack('VVV', strlen($this->format), strlen($stringDict), strlen($content));

		// 字典长度+文件内容长度   写入
		fwrite($outputFile, $header);
		// //  序列化字典写入
		fwrite($outputFile, $stringDict);


		//文件内容编码写入
		$len = strlen($content);
		$buff = '';
		$index = 0;
		while ($len) {
			$ch = $content[$index];
			//对应字符字典中没有找到  则直接报错 
			if (!isset($this->dict[$ch])) {
				return -1;
			}

			$buff .= $this->dict[$ch];
			//第八个字节已存在
			while (isset($buff[7])) {
				//将对应的二进制转为十进制存储
				$char = bindec(substr($buff, 0, 8));
				//写入
				fwrite($outputFile, chr($char));
				//截断八位
				$buff = substr($buff, 8);
			}

			$index++;
			$len--;
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
		
		//获取压缩的文件类型
		$this->format = $arrFileInfo[1];

		//新建文件路径以及名称
		$saveFilePath = $savePath . '/' . $saveName;

		//创文件 并准备写入
		$compressFile = fopen($saveFilePath, 'w');

		// $comptreeFile = fputs($compressFile, "$this->format\n");

		return $compressFile;
	}
}
