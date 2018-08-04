<?php 
/**
 * @author wangyelou
 * @date 2017年2月23日
 * @desc 解析token
 */
namespace JsonDecode;
class Json
{

	public $trees = array();
	private $last = NULL;
	private $next = NULL;
	private $isArray = true;
	
	public function run($stack)
	{
		foreach ($stack as $element) {
		
			switch ($element->type) {
				case 'START_ARR' :
					if (!$flag = $this->check('START_ARR')) return '1json格式错误';
					$this->startArr();
					break;
				case 'END_ARR' :
					if (!$flag = $this->check('END_ARR')) return '2json格式错误';
					$this->endArr();
					break;
				case 'START_OBJ' :
					if (!$flag = $this->check('START_OBJ')) return '3json格式错误';
					$this->startObj();
					break;
				case 'END_OBJ' :
					if (!$flag = $this->check('END_OBJ')) return '4json格式错误';
					$this->endObj();
					break;
				case 'STRING' :
					if (!$flag = $this->check('STRING')) return '5json格式错误';
					$this->string($element->value);
					break;
				case 'COMMA' :
					if (!$flag = $this->check('COMMA')) return '6json格式错误';
					$this->next = array('START_ARR', 'START_OBJ', 'STRING');
					break;
				case 'COLON' :
					if (!$flag = $this->check('COLON')) return '7json格式错误';
					$this->next = array('START_ARR', 'START_OBJ', 'STRING');
					break;
			}
		
		}
		
				
	}
	
	private function check($type)
	{
		if (!empty($this->next) && !in_array($type, $this->next)) {
			return false;
		}
		return true;		
	}
	
	private function string($val)
	{
		$arr = array('type'=>'STRING', 'val'=>$val);
		$this->last['childs'][] = &$arr;
		unset($arr);
		$this->next = array('COMMA', 'COLON', 'END_OBJ', 'END_ARR');
	}
	
	//对象开始
	private function startObj()
	{
		if ($this->last == NULL) {
			$this->last = &$this->trees;
		} else {
			$arr = array();
			$this->last['childs'][] = &$arr;
			$last = &$this->last;
			$this->last = &$arr;
			$this->last['last'] = &$last;
			unset($arr, $last);
		}
		$this->last['type'] = 'OBJ';
		
		$this->next = array('STRING', 'END_OBJ');
	}
	
	//对象结束
	private function endObj()
	{
		$arr = &$this->last['last'];
		unset($this->last['last']);
		$this->last = &$arr;
		unset($arr);
		$this->next = array('COMMA', 'END_OBJ', 'END_ARR');
	}

	//数组开始
	private function startArr()
	{
		if ($this->last == NULL) {
			$this->last = &$this->trees;
		} else {
			$arr = array();
			$this->last['childs'][] = &$arr;
			$last = &$this->last;
			$this->last = &$arr;
			$this->last['last'] = &$last;
			unset($arr, $last);
		}
		$this->last['type'] = 'ARR';
		$this->next = array('START_ARR', 'START_OBJ', 'STRING', 'END_ARR');
	}
	
	//数组结束
	private function endArr()
	{
		$arr = &$this->last['last'];
		unset($this->last['last']);
		$this->last = &$arr;
		unset($arr);
		$this->next = array('COMMA', 'END_OBJ', 'END_ARR');
	}
	
	
	//解析树	
	public function parseTrees($trees, &$data, $flag = false)
	{
		switch ($trees['type']) {
			case 'ARR' :
				if ($data === NULL) {
					$data = array();
					$a = &$data;
				} else {
					if ($flag) {
						if ($this->isArray) {
							$data[$flag] = array();
							$a = &$data[$flag];
						} else {
							$data->$flag = array();
							$a = &$data->$flag;
						}
					} else {
						$key = count($data);
						$data[$key] = array();
						$a = &$data[$key];
					}
				}
				foreach ($trees['childs'] as $child) {
					$this->parseTrees($child, $a, false);
				}
				break;
			case 'OBJ' :
				if ($data === NULL) {
					if ($this->isArray)
						$data = array();
					else 
						$data = new stdClass();
					$a = &$data;
				} else {
					if ($flag) {
						if ($this->isArray) {
							$data[$flag] = array();
							$a = &$data[$flag];
						} else {
							$data->$flag = array();
							$a = &$data->$flag;
						}
					} else {
						$key = count($data);
						if ($this->isArray) $data[$key] = array();
						else $data[$key] = new stdClass();
						$a = &$data[$key];
					}
				}
				
				$flag = true;
                if (!empty($trees['childs'])) {
                    foreach ($trees['childs'] as $key => $child) {
                        $this->parseTrees($child, $a, $flag);
                        
                        if ($flag === true) 
                            $flag = stripcslashes(preg_replace("/(^[\"\' \t\n\r]+)|([\"\' \t\n\r]+$)/is", '', $child['val']));
                        else 
                            $flag = true;
                        
                    }
                }
				break;
			case 'STRING' :
                $val = $trees['val'];
                if (strpos($val, '\u') !== false)
                    $val = current(json_decode('["'.trim($val, '"').'"]'));
                
				//对象处理
				if ($flag) {
					$val = stripcslashes(preg_replace("/(^[\"\' \t\n\r]+)|([\"\' \t\n\r]+$)/is", '', $val));				
					if ($this->isArray) {
						if ($flag === true) {
							$data[$val] = NULL;
						} else {
							if (strpos($val, '\u') !== false)
							     $val = current(json_decode('["'.$val.'"]'));
							$data[$flag] = $val;
						}
					} else {
						if ($flag === true)
							$data->$val = NULL;
						else
							$data->$flag = $val;
					}
					
				}
				//数组处理
				else {
					$data[] = stripcslashes(preg_replace("/(^[\"\' \t\n\r]+)|([\"\' \t\n\r]+$)/is", '', $val));
				}
				
				break;			
		}
		
		
	}
	
	
}







