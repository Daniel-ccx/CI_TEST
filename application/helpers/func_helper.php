<?php
/**
 * @author changxi<faithsmine@gmail.com>
 * @version 2014-07-21 16:50:50
 * @description 自定义函数
 **/

/**
 * 验证是否是整数
 * @param  int   $int
 * @return bool  如果不是整数则返回false 否则返回true
 */
function is_int_num($int)
{
    if(!preg_match('/^[0-9]*[0-9][0-9]*$/', $int))
        return false;
    else
        return true;
}

/**
 * 检测是否全是英文
 * @param  $str
 * @return bool  是英文则返回true 否则返回false
 */
function check_en($str)
{
    $pattern = "/[^a-z]/";

    if (preg_match($pattern, $str))
        return false;
    else
        return true;
}

/**
 * 检测是否含有中文
 * @param  $str
 * @return bool  有则返回true 否则返回false
 */
function check_chinese($str)
{
    $pattern = "/[\x{4e00}-\x{9fa5}]/u";

    if(preg_match($pattern, $str))
        return false;
    else
        return true;
}

/**
 * 数组转换xml
 * @param  array   $array
 * @param  string  $encoding
 * @return string  $xml
 */
function array_to_xml($array, $encoding = 'gb2312')
{
    $xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
    $xml.= array2_to_xml($array, 0, $encoding);

    return $xml;
}

function array2_to_xml($array, $deep = 0,$encoding = 'gb2312')
{
    $xml = '';

    $deepstr = ($deep < 2) ? '' : $deep;

    $deep++;

    foreach($array as $key => $val)
    {
        is_numeric($key) && $key = "item{$deepstr} id=\"$key\"";

        $xml.= "<$key>";
        $xml.= is_array($val) ? array2_to_xml($val, $deep, $encoding) : cdata($val, $key, $encoding);

        list($key,) = explode(' ',$key);

        $xml.= "</$key>";
    }

    return $xml;
}

/**
 * 检测是否是手机号
 * @param  string  $str
 * @return bool
 */
function checkMob($str)
{
    $n = preg_match_all("/(?:13\d{9}|15[0|1|2|3|5|6|7|8|9]\d{8}|18[0|2|3|5|6|7|8|9]\d{8}|14[5|7]\d{8})/", $str, $array);

    /**
     * 接下来的正则表达式("/131,132,133,135,136,139开头随后跟着任意的8为数字 '|'(或者的意思)
     * 151,152,153,156,158.159开头的跟着任意的8为数字
     * 或者是188开头的再跟着任意的8为数字,匹配其中的任意一组就通过了
     * /")
     */
    if(count($array[0]))
    {
        return true;
    }

    return false;
}

/**
 * 正则检测是否是电话
 * @param  string  $telephone
 * @return bool
 */
function checkTel($str)
{
    if(preg_match("/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i", $str) || preg_match("/([2-9][0-9]{6,7}[\-]?[0-9]?)/i", $str))
    {
        return true;
    }
    return false;
}

/**
 * 输出JSON
 * @param  array   $json_arr
 * @param  string  $callback  兼容jsonp 
 * @return void
 */
function outputJson($json_arr, $callback = '')
{
	header('Content-Type: text/json; charset=UTF-8');
    //此处不兼容IE低板本
    //header('Content-type: application/json; charset=UTF-8');
	
    if(!empty($callback))
    {
        $output = $callback.'('.json_encode($json_arr).')';
    }
    else
    {
        $output = json_encode($json_arr);
    }

    die($output);
}

/**
 * 是否为AJAX请求
 * @param  void
 * @return boolean
 */
function isXmlHttpRequest()
{
	//Try to get it from the $_SERVER array first
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']))
	{
		return $_SERVER['HTTP_X_REQUESTED_WITH'];
	}
	
	//This seems to be the only way to get the Authorization header on
	//Apache
	if(function_exists('apache_request_headers'))
	{
		$headers = apache_request_headers();
		
		if(!empty($headers['X_REQUESTED_WITH']))
		{
			return $headers['X_REQUESTED_WITH'];
		}
	}
	
	return false;
}

/**
 * 是否为flash请求
 * @param  void
 * @return bool
 */
function isFlashRequest()
{
	//Try to get it from the $_SERVER array first
	if(!empty($_SERVER['HTTP_USER_AGENT']))
	{
		return (strstr($_SERVER['HTTP_USER_AGENT'], ' flash')) ? true : false;
	}
	
	//This seems to be the only way to get the Authorization header on
	//Apache
	if(function_exists('apache_request_headers'))
	{
		$headers = apache_request_headers();
	
		if(!empty($headers['USER_AGENT']))
		{
			return (strstr($headers['USER_AGENT'], ' flash')) ? true : false;
		}
	}
	
	return false;
}

/**
 * 检测字符串长度
 * @param  string  $str
 * @return int
 */
function strlen_utf8($str)
{
    $i      = 0;
    $count  = 0;
    $len    = strlen($str);

    while($i < $len)
    {
        $chr = ord($str[$i]);

        $count++;

        $i++;

        if($i >= $len)
        {
            break;
        }

        if($chr & 0x80)
        {
            $chr <<= 1;

            while ($chr & 0x80)
            {
                $i++;

                $chr <<= 1;
            }
        }
    }

    return $count;
}

/**
 * 按字符进行截取
 *【说明】
 * 汉字为2个字符，数字、字母为一个字符，默认为utf-8
 * gbk半个汉字字符会乱码(待调整)
 * @param  string  $string
 * @param  int     $length
 * @param  int     $dot
 * @param  string  $charset
 * @return string
 */
function cutstr($string, $length, $dot = '',$charset = 'utf-8')
{
    if(strlen($string) <= $length)
    {
        return $string;
    }

    $string = str_replace(array('&', '”', '<', '>'), array('&', '”', '<', '>'), $string);
    $strcut = '';

    if(strtolower($charset) == 'utf-8')
    {
        $n = $tn = $noc = 0;

        while($n < strlen($string))
        {
            $t = ord($string[$n]);

            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126))
            {
                $tn = 1; $n++; $noc++;
            }
            elseif(194 <= $t && $t <= 223)
            {
                $tn = 2; $n += 2; $noc += 2;
            }
            elseif(224 <= $t && $t < 239)
            {
                $tn = 3; $n += 3; $noc += 2;
            }
            elseif(240 <= $t && $t <= 247)
            {
                $tn = 4; $n += 4; $noc += 2;
            }
            elseif(248 <= $t && $t <= 251)
            {
                $tn = 5; $n += 5; $noc += 2;
            }
            elseif($t == 252 || $t == 253)
            {
                $tn = 6; $n += 6; $noc += 2;
            }
            else
            {
                $n++;
            }

            if($noc >= $length)
            {
                break;
            }
        }

        if($noc > $length)
        {
            $n -= $tn;
        }

        $strcut = substr($string, 0, $n);
    }
    else
    {
        for($i = 0; $i < $length; $i++)
        {
            $strcut.= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
        }
    }

    $strcut = str_replace(array('&', '”', '<', '>'), array('&', '”', '<', '>'), $strcut);

    return $strcut.$dot;
}

/**
 * 按UNICODE编码截取字符串前$length个字符
 * @param  string  $str
 * @param  int     $length
 */
function cn_substr($string, $length)
{
    if($length == 0)
    {
        return '';
    }

    $newlength = 0;

    if(strlen($string) > $length)
    {
        for($i = 0; $i < $length; $i++)
        {
            $a = @base_convert(ord($string{$newlength}), 10, 2); 

            $newlength++;

            $a = substr('00000000' . $a, -8);

            if(substr($a, 0, 1) == 0)
            {
                continue;
            }
            elseif(substr($a, 0, 3) == 110)
            {
                $newlength ++;
            }
            elseif(substr($a, 0, 4) == 1110)
            {
                $newlength += 2;
            }
            elseif(substr($a, 0, 5) == 11110)
            {
                $newlength += 3;
            }
            elseif(substr($a, 0, 6) == 111110)
            {
                $newlength += 4;
            }
            elseif(substr($a, 0, 7) == 1111110)
            {
                $newlength += 5;
            }
            else
            {
                $newlength ++;
            }
        }

        return substr($string, 0, $newlength);
    }
    else
    {
        return $string;
    }
}

/**
 * 提交GET请求，curl方法
 * @param  string  $url      请求url地址
 * @param  mixed   $data     GET数据,数组或类似id=1&k1=v1
 * @param  array   $header   头信息
 * @param  int     $timeout  超时时间
 * @param  int     $port     端口号
 * @return array   $result   请求结果,
 *                           如果出错,返回结果为array('error'=>'','result'=>''),
 *                           未出错，返回结果为array('result'=>''),
 */
function curl_get($url, $data = array(), $header = array(), $timeout = 15, $port = 80)
{
    $ch = curl_init();

    if (!empty($data))
    {
        $data = is_array($data) ? http_build_query($data) : $data;

        $url.= (strpos($url,'?')?  '&': "?") . $data;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_POST, 0);
    //curl_setopt($ch, CURLOPT_PORT, $port);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1); //是否抓取跳转后的页面

    $result = array();

    $result['result'] = curl_exec($ch);

    if(0 != curl_errno($ch))
    {
        $result['error']  = "Error:\n" . curl_error($ch);
    }

    curl_close($ch);

    return $result;
}

/**
 * 提交POST请求，curl方法
 * @param  string  $url      请求url地址
 * @param  mixed   $data     POST数据,数组或类似id=1&k1=v1
 * @param  array   $header   头信息
 * @param  int     $timeout  超时时间
 * @param  int     $port     端口号
 * @return string  $result   请求结果,
 *                           如果出错,返回结果为array('error'=>'','result'=>''),
 *                           未出错，返回结果为array('result'=>''),
 */
function curl_post($url, $data = array(), $header = array(), $timeout = 15, $port = 80)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    //curl_setopt($ch, CURLOPT_PORT, $port);
    !empty ($header) && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = array();

    $result['result'] = curl_exec($ch);

    if(0 != curl_errno($ch))
    {
        $result['error']  = "Error:\n" . curl_error($ch);

    }

    curl_close($ch);

    return $result;
}

/**
 * CURL put 上传文件
 * @param  <type>  $url         请求url
 * @param  <type>  $file        文件位置
 * @param  <type>  $filehandle  文件resource
 * @param  <type>  $header      请求头
 * @param  <type>  $timeout     请求超时限制
 * @param  <type>  $port        请求端口
 * @return string
 */
function curl_put($url, $file,$filehandle, $header = array(), $timeout = 5, $port = 80)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    !empty ($header) && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_PUT, 1);
    curl_setopt($ch, CURLOPT_INFILE, $filehandle);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));

    $result = array();

    $result['result'] = curl_exec($ch);

    if(0 != curl_errno($ch))
    {
        $result['error']  = "Error:\n" . curl_error($ch);
    }

    curl_close($ch);

    return $result;
}

/**
 * 将数组中长整型转化为string
 * @param  $data  array  多维数组
 * @return void
 */
function intToString(&$data)
{
    if(is_array($data))
    {
        foreach($data as $field => &$val)
        {
            intToString($val);
        }
    }
    else
    {
        if(is_numeric($data))
        {
            $data .= '';
        }
    }
}

/**
 * 提取中文
 * @param  string  $str  待提取字符串
 * @return string  $str  只含有中文的字符串
 */
function get_chinese($str)
{
	$str = preg_replace("/[^a-z,A-Z,0-9\x{4e00}-\x{9fa5}]/iu",'',$str);

	return $str;
}

/**
 * 检查Unicode编码
 *【说明】
 * 十六进制和十进制Unicode编码检查
 * @author chenkai@leju.com
 * @param  string  $str  待验证字符串
 * @return array   $result     
 */
function check_unicode($str)
{
	$state 	= 0;
	$bit 	= 0;

	$unicode_16 = preg_match("/&#x([0-9,a-z,A-Z]{1,5});/", $str);

	if ($unicode_16)
	{
		$state 	= 1;
		$bit 	= 16;
		$return = array('state' => $state, 'bit' => $bit);

		return $return;
	}

	$unicode_10 = preg_match("/&#([0-9,a-z,A-Z]{1,5});/", $str);

	if($unicode_10)
	{
		$state = 1;
		$bit = 10;
	}

	$return = array('state' => $state, 'bit' => $bit);

	return $return;

}
