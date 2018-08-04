PHP解析json类

优势

	1,可兼容多种类型，多种编码的json串，如

	{'name' : tina, "age" : '14', 'sex' : 'woman'}


	2,可进行json部分解析返回，如

	[
		[
			{'name' : "tom", 'age' : 13, sex : 'man'},
			{'name' : tina, "age" : '14', 'sex' : 'woman'}
		],
		[
			{"a":"b"}
		]
	]

	只返回

	[
		{"a":"b"}
	]

劣势

	性能和速度比原始json_decode低

DEMO

		require_once 'autoload.php';
		$content = <<<EOT
		[
			{"name" : "tom", "age" : "13", "sex" : "man"},
			{"name" : "tina", "age" : "14", "sex" : "woman"}
		]
	EOT;

		$parse = new Parser();
		$results = $parse->run($content, 0);