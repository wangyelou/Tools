<?php

/**
 * @author wangyelou
 * @date 2017年2月23日
 * @desc 解析json字符串为token
 */
namespace JsonDecode;
class Parser
{	
	
	public function run($content, $arrIndex)
	{
		//获取token栈
		$stack = $this->parseJson($content, $arrIndex);
		
		//解析token栈
		$json = new Json();
		$json->run($stack);
		
		//解析数据树
		$data = NULL;
		$json->parseTrees($json->trees, $data);
		
		return $data;
	}
	
	/**
	 * 解析json
	 * @param string $content
	 * @param int $arrIndex 数组([)索引
	 * @return array
	 */
	private function parseJson($content, $arrIndex = 0)
	{
		$sign = array('{'=>'}', '['=>']');
		$lastSign = false;
		$stack = array();
		$json = '';
		$arrCount = 0;
		$flag = false;

		for ($i=0; true; $i++) {
	
			if (isset($content[$i])) {
				$str = $content[$i];
			} else {
				break;
			}
			
			//入栈
			if (array_key_exists($str, $sign) && $lastSign == '') {
				if ($str == '[') $arrCount += 1;
				if ($arrCount >= $arrIndex) {
					array_unshift($stack, $str);
					$flag = true;
				}
			}
			//出栈
			elseif (in_array($str, $sign) && $flag && $lastSign == '') {
				$pop = array_shift($stack);
				if ($sign[$pop] != $str)
					return false;
	
			}
	
			//json结束
			if ($flag) {
				$this->getToken($str, $lastSign, isset($content[$i+1]) ? $content[$i+1] : NULL);
				if (count($stack) <= 0) {
					break;
				}
            }
			
            //判断下段内容是否是字符串
            if (
                ($str == '"' || $str == '\'') 
                && (
                    in_array($this->findPre($content, $i), array(',', '{', '[', ':')) 
                    || in_array($this->findNext($content, $i), array(',', '}', ']', ':'))
                    )
                )
            {
                if ($lastSign == $str && (($content[$i-1] != '\\') || ($content[$i-1] == '\\' && $content[$i-2] == '\\'))) 
                    $lastSign = '';
                elseif (empty($lastSign))
                    $lastSign = $str;
            }
	
	
		}
		
		return $this->stack;
	}

	//找到前面内容不为空的值
	private function findPre(&$content, $i)
	{
		while(true) {
			$ord = ord($content[$i-1]);
			if ($ord == 13 || $ord == 10 || $ord == 32 || $ord == 9 || $ord == 128 || $ord == 227)
				$i --;
			else 
				return $content[$i-1];
		}
	}

	//找到后面内容不为空的值
	private function findNext(&$content, $i)
	{
		while(true) {
			$ord = ord($content[$i+1]);
			if ($ord == 13 || $ord == 10 || $ord == 32 || $ord == 9 || $ord == 128 || $ord == 227)
				$i ++;
			else {
				return $content[$i+1];
			}
		}
	}
	
	public $stack = array();
	private $next = NULL;
	private $strings = NULL;
	private $lastSign = NULL;
	/**
	 * 获取token
	 * @param string $str 当前字符
	 * @param string $lastSign 上一个特殊符号
	 * @param string $nextStr 下一个字符
	 * @return boolean
	 */
	private function getToken($str, $lastSign, $nextStr)
	{
		//删除符号之间的换行
		$ord = ord($str);
		if (($ord == 13 || $ord == 10 || $ord == 32 || $ord == 9 || $ord == 128 || $ord == 227) && !empty($this->lastSign)) {
			return false;
		}

		if (($this->next && !in_array($str, $this->next)) || $lastSign != '') {
			$this->strings .= $str;
			$this->next = array(',', ']', '}', ':');
			$this->lastSign = '';
		} else {
		
			switch ($str) {
				case '[' :
					if ($this->strings != NULL) {
						$this->stack[] = new Token('STRING', $this->strings);
						$this->strings = NULL;
					}
					
					$this->stack[] = new Token('START_ARR', '[');
					$this->next = array('[', '{', ']');
					$this->lastSign = '[';
					break;
				case ']' : 					
					if ($this->strings != NULL) {
						$this->stack[] = new Token('STRING', $this->strings);
						$this->strings = NULL;
					}
					
					$this->stack[] = new Token('END_ARR', ']');
					$this->next = array(']', '}', ':', ',');
					$this->lastSign = ']';
					break;
				case '{' :		
					if ($this->strings != NULL) {
						$this->stack[] = new Token('STRING', $this->strings);
						$this->strings = NULL;
					}
					
					$this->stack[] = new Token('START_OBJ', '{');
					$this->next = array('[', '{', '}');
					$this->lastSign = '{';
					break;
				case '}' :					
					if ($this->strings != NULL) {
						$this->stack[] = new Token('STRING', $this->strings);
						$this->strings = NULL;
					}
					
					$this->stack[] = new Token('END_OBJ', '}');
					$this->next = array(']', '}', ':', ',');
					$this->lastSign = '}';
					break;
				case ',' :
                    //[a,b,]
                    if ($nextStr == ']' || $nextStr == '}')
                        return false;
					
					if ($this->strings != NULL) {
						$this->stack[] = new Token('STRING', $this->strings);
						$this->strings = NULL;
					} 
					
					$this->stack[] = new Token('COMMA', ',');
					$this->next = array('[', '{');
					$this->lastSign = ',';
					break;
				case ':' :
					if ($this->strings != NULL) {
						$this->stack[] = new Token('STRING', $this->strings);
						$this->strings = NULL;
					} 
					
					$this->stack[] = new Token('COLON', ':');
					$this->next = array('[', '{');
					$this->lastSign = ':';
					break;
			}
		}
		
		
	}
	
}
