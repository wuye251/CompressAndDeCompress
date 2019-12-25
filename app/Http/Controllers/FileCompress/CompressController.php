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

		//文件存在 首先统计文件内容字符个数
		$this->charCount($file);
		//将统计的字符进行排序
		$this->countArr = $this->quickSort($this->countArr);
		//构建哈夫曼树
		$huffmanTree = $this->createHuffmanTree($this->countArr);

		$this->dict = array();
		$this->codeHuffman(current($huffmanTree),'', $this->dict);
		print_r($this->dict);exit;
		//哈夫曼编码
		
		fclose($file);
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
	private function createHuffmanTree(&$countArr)
	{
		$huffmanTree = $this->countArr;
		$len = count($huffmanTree);
		//内容为空 或 为1
		if ($len <= 1)
		{
			return $this->countArr;
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

			print_r($huffmanTree);
			echo "<br>";
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
}
