<?php
/**

 */


class Curl
{
    private static $ip = null;
    // 存储相应的CURL对象, 以url为key
    private static $oCurls = [];
    // 超时时间
    private static $timeOut = 10;
    // 设置的请求来源的地址
    private static $referer = null;
    // 设置HOST, 可以设置多个
    private static $hosts = [];
    // 代理
    private static $proxy = null;
    // 请求头
    private static $headers = [];
    // 请求需要设置的Cookie
    private static $cookies = [];
    // userAgant
    private static $userAgant = 'Mozilla/5.0';
    // 每次curl请求之后的信息
    private static $curlInfo = [];

    /**
     * 设置请求的来源页面 比如: http://www.baidu.com
     **/
    public static function setReferer($referer)
    {
        self::$referer = $referer;
    }

    /**
     * 设置请求的超时时间
     **/
    public static function setTimeOut($timeOut = 10)
    {
        if ($timeOut > 0) {
            self::$timeOut = $timeOut;
        }
    }

    /**
     * 设置请求的Host 是一个数组，可以实现随机用哪个Host去请求
     **/
    public static function setHost($host = [])
    {
        if ($host) {
            self::$hosts = $host;
        }
    }

    /**
     * 设置是否使用代理
     **/
    public static function setProxy($flag = false)
    {
        self::$proxy = $flag;
    }

    /**
     * 设置请求头，不允许设置Host，在请求时会根据请求自动设置Host
     * 每次设置一个请求头
     **/
    public static function setHeaders($head)
    {
        // 例子如下:
        // $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
        // $headers[] = 'Accept-Encoding: gzip, deflate';
        // $headers[] = 'Accept-Language: en-US,en;q=0.5';
        // $headers[] = 'Cache-Control: no-cache';
        // $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        self::$headers[] = $head;
    }

    /**
     * 设置请求的Cookie  k => v 的方式
     **/
    public static function setCookie($key, $val)
    {
        self::$cookies[] = $key . '=' . $val;
    }

    /**
     * 设置UserAgant
     **/
    public static function setUserAgent($userAgant)
    {
        self::$userAgant = $userAgant;
    }

    /**
     * 设置IP，用于在请求头上写上自己想要的IP，方便 NG或者程序获取IP
     **/
    public static function setIp($ip)
    {
        self::$ip = $ip;
    }

    /**
     * 关闭语柄，并存储的Curl链接数据
     **/
    protected static function closeCurl($url, $oCurl)
    {
        curl_close($oCurl);
        unset(self::$oCurls[md5($url)]);
    }

    /**
     * 清空所有的句柄
     **/
    public static function clearCurl()
    {
        if (self::$oCurls) {
            foreach (self::$oCurls as $_oCurl) {
                curl_close($_oCurl);
            }
        }
        self::$oCurls = [];
        self::$curlInfo = [];
        self::$headers = [];
        self::$cookies = [];
        self::$hosts = [];
    }

    public static function getLastInfo()
    {
        return self::$curlInfo;
    }

    protected static function init($url)
    {
        $k = md5($url);/*{{{*/
        if (!isset(self::$oCurls[$k]) || !is_resource(self::$oCurls[$k])) {
            self::$oCurls[$k] = curl_init();
            curl_setopt(self::$oCurls[$k], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt(self::$oCurls[$k], CURLOPT_CONNECTTIMEOUT, self::$timeOut);
            curl_setopt(self::$oCurls[$k], CURLOPT_HEADER, false);
            curl_setopt(self::$oCurls[$k], CURLOPT_USERAGENT, self::$userAgant);
            curl_setopt(self::$oCurls[$k], CURLOPT_TIMEOUT, self::$timeOut + 5);

            // 如果是HTTPS协议 暂时不用
            //if(stripos($url,"https://")!==FALSE){
            //curl_setopt(self::$oCurls[$k], CURLOPT_SSL_VERIFYPEER, FALSE);
            //curl_setopt(self::$oCurls[$k], CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt(self::$oCurls[$k], CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
            //}

            if (self::$referer) {
                curl_setopt(self::$oCurls[$k], CURLOPT_REFERER, self::$referer);
            }

            if (self::$cookies) {
                curl_setopt(self::$oCurls[$k], CURLOPT_COOKIE, implode(';', self::$cookies));
            }

            if (self::$ip) {
                self::$headers = array_merge(array('CLIENT-IP:' . self::$ip, 'X-FORWARDED-FOR:' . self::$ip),
                    self::$headers);
            }

            // 默认跟踪页面上的Location的重定向
            curl_setopt(self::$oCurls[$k], CURLOPT_FOLLOWLOCATION, true);
        }
        return self::$oCurls[$k];/*}}}*/
    }

    /**
     * POST请求数据
     **/
    public static function httpPost($url, $param, $closeCurl = true)
    {
        // 1、初始化/*{{{*/
        $oCurl = self::init($url);
        // 2、如果设置了HOST 就随机获取一个host的去处理
        if (self::$hosts) {
            // 解析当前的URL, 存储当前的HOST
            $parseUrl = parse_url($url);
            $currHost = $parseUrl['host'];
            // 随机那一个如果设置好的HOST数组中的HOST
            $key = rand(0, count(self::$hosts) - 1);
            $randHost = self::$hosts[$key];
            $url = str_replace($currHost, $randHost, $url);
            // 重新设置header请求头
            self::$headers = array_merge(array('Host:' . $currHost), self::$headers);
        }

        if (self::$headers) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, self::$headers);
        }

        if (self::$proxy) {
            curl_setopt($oCurl, CURLOPT_PROXY, $url);
            curl_setopt($oCurl, CURLOPT_USERAGENT, $url);
        }

        // 3、封装需要请求的数据
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }

        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);

        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        self::$curlInfo = ['curl_info' => $aStatus, 'content' => $sContent];

        if ($closeCurl === true) {
            self::closeCurl($url, $oCurl);
        }

        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return ['http_code' => $aStatus['http_code'], 'err' => $sContent];
        }/*}}}*/
    }

    /**
     * GET请求数据
     **/
    public static function httpGet($url, $closeCurl = true)
    {
        // 1、初始化/*{{{*/
        $oCurl = self::init($url);

        // 2、如果设置了HOST 就随机获取一个host的去处理
        if (self::$hosts) {
            // 解析当前的URL, 存储当前的HOST
            $parseUrl = parse_url($url);
            $currHost = $parseUrl['host'];
            // 随机那一个如果设置好的HOST数组中的HOST
            $key = rand(0, count(self::$hosts) - 1);
            $randHost = self::$hosts[$key];
            $url = str_replace($currHost, $randHost, $url);
            // 重新设置header请求头
            self::$headers = array_merge(array('Host:' . $currHost), self::$headers);
        }

        if (self::$headers) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, self::$headers);
        }

        if (self::$proxy) {
            curl_setopt($oCurl, CURLOPT_PROXY, $url);
            curl_setopt($oCurl, CURLOPT_USERAGENT, $url);
        }

        curl_setopt($oCurl, CURLOPT_URL, $url);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        self::$curlInfo = ['curl_info' => $aStatus, 'content' => $sContent];

        if ($closeCurl === true) {
            self::closeCurl($url, $oCurl);
        }

        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return ['http_code' => $aStatus['http_code'], 'err' => $sContent];
        }/*}}}*/
    }

    public static function get_client_ip() 
    {
        $client_ip = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : '';
        if (empty($client_ip)) {
            $client_ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
            if (empty($client_ip)) {
                $client_ip = isset($_SERVER['HTTP_X_FORWARDED_FOR2']) ? $_SERVER['HTTP_X_FORWARDED_FOR2'] : '';
                if (empty($client_ip)) {
                    $client_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                }
            }
        }
        \preg_match("/[\d\.]{7,15}/", $client_ip, $onlineip);
        $client_ip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        return $client_ip;
    }
}

class DNSPod
{
    protected $host = "https://dnsapi.cn";
    protected $loginToken = '';
    protected $logFile;


    public function __construct()
    {
        $this->logFile = dirname(__FILE__) . '/dnspod_api.log';
    }

    protected function commonParam()
    {
        return [
            'login_token' => '',
            'format' => 'json',
            'lang' => 'cn'
        ];
    }

    public function doPost($uri, $data)
    {
        $data = array_merge($data, $this->commonParam());
        $url = $this->host . $uri;
        $logContent = "REQUEST:url:{$url},data:" . json_encode($data);
        $this->log($logContent);
        return $this->format(Curl::httpPost($url, $data));
    }

    public function doGet($uri)
    {
        $url = $this->host . $uri . http_build_query($this->commonParam());
        $logContent = "REQUEST:url:{$url}";
        $this->log($logContent);
        return $this->format(Curl::httpGet($url));
    }

    private function format($response)
    {
        if (is_string($response)) {
            $response = json_decode($response, true);
        }
        $logContent = "RESPONSE:data:" . json_encode($response);
        $this->log($logContent);
        return $response;
    }

    protected function log($content)
    {
        if ($this->logFile) {
            $fd = fopen($this->logFile, 'a+');
            fwrite($fd, $content."\n");
            fclose($fd);
        }
    }

    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
    }
}

/**
 * Class DNSPodRecordApi
 * @apiDoc https://www.dnspod.
 * cn/docs/records.html
 */
class DNSPodRecordApi extends DNSPod
{
    //private $domain = '3kwan.com';//只允许操作此域名   可读取

    private  $domain =null;//获取域名


    public function setDomain($domain_type) {
        if(empty($this->domain) && $domain_type != ''){
            $this->domain = $domain_type;
        }

    }

    /**
     * 添加记录
     * @param string $recordType
     * @param string $recordLine
     * @param string $value
     * @param int $mx
     * @param string $subDomain
     * @param string $status
     * @param null $ttl
     * @param null $weight
     * @return array
     */
    public function add(
        $recordType,
        $recordLine,
        $value,
        $mx,
        $subDomain='@',
        $status='enable',
        $ttl=null,
        $weight=null
    ) {
        $uri = '/Record.Create';
        $data = [
            'domain' => $this->domain,
            'sub_domain' => $subDomain,
            'record_type' => $recordType,
            'record_line' => $recordLine,
            'value' => $value,
            'mx' => $mx,
            'status' => $status,
        ];
        if($ttl !== null){
            $data['ttl'] = $ttl;
        }
        if($weight !== null){
            $data['weight'] = $weight;
        }
        return $this->doPost($uri, $data);
    }

    /**
     * 修改记录
     * @param int $recordId
     * @param string $recordType
     * @param string $recordLine
     * @param string $value
     * @param int $mx
     * @param string $subDomain
     * @param string $status
     * @param null $ttl
     * @param null $weight
     * @return array
     */
    public function update(
        $recordId,
        $recordType,
        $recordLine,
        $value,
        $mx,
        $subDomain='@',
        $status='enable',
        $ttl=null,
        $weight=null
    ) {
        $data = [
            'record_id'=>$recordId,
            'domain' => $this->domain,
            'sub_domain' => $subDomain,
            'record_type' => $recordType,
            'record_line' => $recordLine,
            'value' => $value,
            'mx' => $mx,
            'status' => $status,
        ];
        if($ttl !== null){
            $data['ttl'] = $ttl;
        }
        if($weight !== null){
            $data['weight'] = $weight;
        }
        $uri = '/Record.Modify';
        return $this->doPost($uri, $data);
    }

    /**
     * 删除记录
     * @param int $recordId
     * @return array
     */
    public function delete($recordId)
    {
        $uri = '/Record.Remove';
        $data = [
            'domain'=>$this->domain,
            'record_id'=>$recordId
        ];
        return $this->doPost($uri, $data);
    }
}

// code = sgqy,  每个游戏的编号
// record_type = A,  解析记录 要么是 A 要么是cname
// value = 1.1.1.1,  解析的IP或者cname地址
// sub_domain = test 解析的域名，不允许出现.符号
// m = add  使用函数目前只开放add

$white_ip = ['127.0.0.1','localhost','172.16.80.130'];
$record_types = ['A', 'CNAME'];
$domainList = ['appdsa.com', 'yeahyoo.com', '17173you.com', 'sanguoshouyou.com', '5577you.com'];//  add 域名列表
if (in_array(Curl::get_client_ip(), $white_ip) && $_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['m'] == 'add') {
    //$code = $_POST['code']; // 去掉 游戏名前缀
    $record_type = $_POST['record_type']; // 'A' 或者 'CNAME'
    if (!in_array($record_type, $record_types)) {
        exit(json_encode(['code' => 0, 'err' => 'format error!']));
    }
    $record_line = '默认';
    $value = $_POST['value']; 
    $mx = 1;
    $sub_domain = $_POST['sub_domain'];
    if (strpos($sub_domain, '.')) {
        exit(json_encode(['code' => 0, 'err' => 'sub_domain format error!']));
    }
    $api = new DNSPodRecordApi();
    $domain_type = $_POST['domain_type'];
	$api->setDomain($domain_type);   //设置域名 $domain
	
    $response = $api->add($record_type, $record_line, $value, $mx, $sub_domain);
    if ($response['status']['code'] != 1) {
        exit(json_encode($response));
    }
    //$response['record']['domain'] = $response['record']['name'].".3kwan.com";
    $response['record']['domain'] = $response['record']['name'].".{$domain_type}";
    exit(json_encode($response));
} else {
    echo "来源IP：".Curl::get_client_ip()."\n";
    exit(json_encode(['code' => 0, 'err' => 'forbid request!']));
}



//$response = $api->delete('366216769');
//var_dump($response);
/**
 *  $recordType,
        $recordLine,
        $value,
        $mx,
        $subDomain='@',
        $status='enable',
        $ttl=null,
        $weight=null

 * $response = $api->add('A', '默认', '1.1.1.1', 1);
 * json_encode($response) = {"status":{"code":"1","message":"\u64cd\u4f5c\u5df2\u7ecf\u6210\u529f\u5b8c\u6210","created_at":"2018-06-25 17:51:23"},"record":{"id":"366084695","name":"@","status":"enabled","weight":null}}
 */

/**
 * $response = $api->update(366084695, 'A', '默认', '2.2.2.2', 2);
 * json_encode($response) = {"status":{"code":"1","message":"\u64cd\u4f5c\u5df2\u7ecf\u6210\u529f\u5b8c\u6210","created_at":"2018-06-25 17:55:54"},"record":{"id":366084695,"name":"@","value":"2.2.2.2","status":"enable","weight":null}}
 */


/**
 * $response = $api->delete(366084695)
 * json_encode($response) = {"status":{"code":"1","message":"\u64cd\u4f5c\u5df2\u7ecf\u6210\u529f\u5b8c\u6210","created_at":"2018-06-25 17:57:07"}}
 */
