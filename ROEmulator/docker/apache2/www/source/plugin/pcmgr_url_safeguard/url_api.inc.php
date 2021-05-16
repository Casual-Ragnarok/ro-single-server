<?php
	$gj_config = array(
		'url_server' => 'http://openapi.guanjia.qq.com/fcgi-bin/urlquery?appid=10003&query_type=1&host=' . $_SERVER["HTTP_HOST"],
		'appid' => 10003,
		'appkey' => '006556a253d9f4ac540c53a4734fbfc2'
	);

	main($gj_config);

	function main($gj_config){
		$data = explode('|', $_POST['content']);
		array_remove_empty($data);
		$url_num = count($data);

		$post_data = '';
		foreach($data as $info){
			$tmp = explode('^', $info);
			if(empty($tmp[0])) continue;

			$post_data .= 'urlinfo=' . urlencode(base64_encode('url=' . urlencode($tmp[0]) . '&urlSeq=' . $tmp[1])) . '&';
		}

		echo 'gj_plugin_function.gettype_callback_a(' . check_url($gj_config, $post_data, $url_num) . ')';
	}

	function get_appsig($config, $data){
		$data_len = strlen($data);
		$str_md5 = md5(pack('I', $config['appid']) . $config['appkey'] . pack('I', $data_len));
		$buffer1 = $str_md5 . pack('I', $data_len);
		$buffer2 = pack('I', $config['appid']) . $config['appkey'];
		$sig = $buffer1 ^ $buffer2;
		return urlencode(base64_encode($sig));
	}

	function check_url($config, $data, $url_num){
		$server = $config['url_server'] . '&appsig=' . get_appsig($config, $data);
		return connect($server, $data, $url_num);
	}

	function connect($server, $data, $url_num){
		$context = array(
	        'http'=>array(
	            'method'=> 'POST',
	            'header'=> 'Content-Type: application/x-www-form-urlencoded'."\r\n",
	            'content'=> $data
	        )
	    );
		$stream_context = stream_context_create($context);
		$ret = file_get_contents($server . '&urlNum=' . $url_num, FALSE, $stream_context);
		return $ret;
	}

	function array_remove_empty(& $arr, $trim = true){
	    foreach ($arr as $key => $value) {
	        if (is_array($value)) {
	            array_remove_empty($arr[$key]);
	        } else {
	        $value = trim($value);
	            if ($value == '') {
	                unset($arr[$key]);
	            } elseif ($trim) {
	                $arr[$key] = $value;
	            }
	        }
	    }
	}
?>