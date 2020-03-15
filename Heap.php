<?php

class Heap {

	private $arr;
	private $len; // 数据元素个数存储

	public function execute($arr)
	{
		$this->arr = $arr;
		$this->len = count($this->arr);
		if ($this->len <= 0) return null;

		return $this->HeapSort();
	}

	//堆排
	public function HeapSort()
	{
		$this->AdjustUp($this->arr);
		$retArr = [];

		$index = $this->len;

		while ($index > 0) {
			$index--;

			$this->heapNodeSwap(0, $index);

			$retArr[] = array_pop($this->arr);

			$this->AdjustDown($this->arr, 0);
		}

		return $retArr;
	}
	
	//建堆
	public function AdjustUp()
	{
		//构建堆		
		$endNode = $this->len-1;
		$parent  = intval(($endNode-1) / 2);
		$indexNode = $parent;

		while ($indexNode >= 0) {
			$this->AdjustDown($this->arr, $indexNode);
			$indexNode--;
		}
			// print_r($this->arr);exit;

		return $this->arr;
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

		if ($len > $leftChilden && $tree[$leftChilden]['count'] < $tree[$min]['count']) {
			$min = $leftChilden;
		}
		if ($len > $rightChilden && $tree[$rightChilden]['count'] < $tree[$min]['count']) {
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

// $test = new Heap();
// return $test->execute($arr);
