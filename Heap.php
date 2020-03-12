<?php

class Heap {

	private $arr;
	private $len; // 数据元素个数存储
	private $countInfo;

	public function execute($arr)
	{
		$this->arr = $arr;
		$this->len = count($this->arr);
		if ($this->len <= 0) return null;
		$this->arr = [16, 25,7,32,6,9];
		$this->len = count($this->arr);
		return $this->HeapSort();
	}

	//堆排
	public function HeapSort()
	{
		$this->AdjustUp($this->arr);
		$retArr = [];

		$index = $this->len;
		while ($index-- >= 0) {
			$this->heapNodeSwap(0, $index);

			$retArr[] = array_pop($this->arr);

			$this->AdjustDown($this->arr, 0);
			
		}
		print_r($retArr);exit;
		return $retArr;
	}
	
	//建堆
	public function AdjustUp()
	{
		//传入数组格式为：q => count => 1 
		//					  ch    => q
		//首先转换为默认键 
		$countInfo = [];
		foreach ($this->arr as $ch => $countChInfo) {
			$this->countInfo[] = $countChInfo;
		}

		//构建堆		
		$endNode = $this->len-1;
		$parent  = intval(($endNode-1) / 2);
		$indexNode = $parent;

		while ($indexNode >= 0) {
			$this->AdjustDown($this->countInfo, $indexNode);
			$indexNode--;
		}

		return $this->countInfo;
	}

	//堆向下调整
	public function AdjustDown(&$tree, $indexRoot)
	{
		$len = count($tree);
		if ($len <= $indexRoot) return $tree;

		$parent = $indexRoot;
		$leftChilden  = $parent*2+1;
		$rightChilden = $parent*2+2;
		$min = $parent;

		if ($len > $leftChilden && $tree[$leftChilden] < $tree[$min]) {
			$min = $leftChilden;
		}
		if ($len > $rightChilden && $tree[$rightChilden] < $tree[$min]) {
			$min = $rightChilden;
		}
		if ($min != $parent) {
			$this->heapNodeSwap($parent, $min);
		} 

		$this->AdjustDown($tree, $leftChilden);
		$this->AdjustDown($tree, $rightChilden);
	}

	private function heapNodeSwap($val1, $val2)
	{
		$temp = $this->arr[$val1];
		$this->arr[$val1] = $this->arr[$val2];
		$this->arr[$val2] = $temp;
	}

}