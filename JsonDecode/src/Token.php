<?php
/**
 * @author wangyelou
 * @date 2017年2月23日
 * @desc token栈
 */
namespace JsonDecode;
class Token
{
	public $type;
	public $value;

	public function __construct($type, $value)
	{
		$this->type = $type;
		$this->value = $value;
	}
}

