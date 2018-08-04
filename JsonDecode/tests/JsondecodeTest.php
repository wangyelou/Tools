<?php
require_once '../../autoloader.php';
use PHPUnit\Framework\TestCase;
class JsondecodeTest extends TestCase
{
	public function testNormalJson()
	{
		$content = <<<EOT
		[
			{"name" : "tom", "age" : "13", "sex" : "man"},
			{"name" : "tina", "age" : "14", "sex" : "woman"}
		]
EOT;

		$parse = new \JsonDecode\Parser();
		$results = $parse->run($content, 0);

		$this->assertEquals($results, array(
			array('name'=>'tom', 'age'=>13, 'sex'=>'man'),
			array('name'=>'tina', 'age'=>14, 'sex'=>'woman')
		));
	}

	public function testApostropheJson()
	{
		$content = <<<EOT
		[
			{'name' : 'tom', 'age' : '13', 'sex' : 'man'},
			{'name' : 'tina', 'age' : '14', 'sex' : 'woman'}
		]
EOT;

		$parse = new \JsonDecode\Parser();
		$results = $parse->run($content, 0);

		$this->assertEquals($results, array(
			array('name'=>'tom', 'age'=>13, 'sex'=>'man'),
			array('name'=>'tina', 'age'=>14, 'sex'=>'woman')
		));
	}

	public function testConfusionJson()
	{
		$content = <<<EOT
		[
			{'name' : "tom", 'age' : 13, sex : 'man'},
			{'name' : tina, "age" : '14', 'sex' : 'woman'}
		]
EOT;

		$parse = new \JsonDecode\Parser();
		$results = $parse->run($content, 0);

		$this->assertEquals($results, array(
			array('name'=>'tom', 'age'=>13, 'sex'=>'man'),
			array('name'=>'tina', 'age'=>14, 'sex'=>'woman')
		));
	}

	public function testIndexJson()
	{
		$content = <<<EOT
		[
			[
				{'name' : "tom", 'age' : 13, sex : 'man'},
				{'name' : tina, "age" : '14', 'sex' : 'woman'}
			],
			[
				{"a":"b"}
			]
		]
EOT;

		$parse = new \JsonDecode\Parser();
		$results = $parse->run($content, 3);

		$this->assertEquals($results, array(
			array('a'=>'b'),
		));
	}

}