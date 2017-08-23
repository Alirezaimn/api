<?php

define('LISH_API_URL', 'http://lish.ir/api/v1');
define('TYPE_NORMAL', 1);
define('TYPE_SMART', 2);
define('TYPE_COMMERCIAL', 3);
define('TYPE_ROTATOR', 4);
define('TYPE_PIXEL', 5);


/**
 * @param string $url
 * @param string $method
 * @param array $data
 * @param array $headers
 * @param null|string $token
 * @return array
 */
function callUrl($url, $method = 'GET', $data = [], $headers = [], $token=null)
{
    $ch = curl_init();
    $headers = array_merge(['Content-Type: application/json'], $headers);
    if($token)
        $headers[]="Authorization: Bearer {$token}";
    $curlOptions = [
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_CONNECTTIMEOUT => 0,
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HEADER         => 1,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    ];
    if ($method == 'GET' && count($data)) {
        $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
    }
    $curlOptions[CURLOPT_URL] = $url;
    if ($method !== 'GET') {
        $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    curl_setopt_array($ch, $curlOptions);
    $result = curl_exec($ch);
    $err = curl_error($ch);
    $response = ['code' => null, 'header' => [], 'body' => []];
    if (!$err) {
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseHeaders = explode("\r\n", substr($result, 0, $header_size));
        $temp = array_shift($responseHeaders);
        if (preg_match("/([1-5][0-9]{2})/", $temp, $match))
            $response['code'] = (int)$match[1];
        foreach ($responseHeaders as $responseHeader) {
            $temp = explode(':', $responseHeader);
            if (count($temp) !== 2)
                continue;
            list($key, $value) = $temp;
            $response['header'][trim($key)] = trim($value);
        }
        $response['body'] = json_decode(substr($result, $header_size), true);
    }
    curl_close($ch);
    return [$err, $response];
}


/**
 * @param string $username
 * @param string $password
 * @return null|string
 */
function login($username, $password)
{
    list($err, $response) = callUrl(LISH_API_URL . '/login', 'POST', ['login' => $username, 'password' => $password]);
    if ($err || $response['code'] !== 200)
        return null;
    return $response['body']['token'];
}


/**
 * @param $target
 * @param null $token
 * @param int $urlType
 * @param null $category
 * @param null $urlGroup
 * @return null|string
 */
function shortenUrl($target, $token = null, $urlType = 1, $category = null, $urlGroup = null)
{
    $data = [
        'url'   => $target,
        'url_type' => $urlType
    ];
    if($urlGroup)
        $data['url_group_id'] = $urlGroup;
    if($category)
        $data['category'] = $category;
    list($err, $response) = callUrl(LISH_API_URL . '/shorten', 'POST', $data, [], $token);
    if($err|| $response['code'] !== 200)
        return null;
    return $response['body']['short'];
}