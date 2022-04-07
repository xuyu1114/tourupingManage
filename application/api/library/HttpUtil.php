<?php
namespace app\api\library;

class HttpUtil
{
    /// 平台ip
    private  $_ip;

    /// 平台端口
    private  $_port = 443;

    /// 平台APPKey
    private  $_appkey;

    /// 平台APPSecret
    private  $_secret;

    /// 是否使用HTTPS协议
    private  $_isHttps = true;

    public function __construct($appkey='', $secret='', $ip='', $port = 443, $isHttps = true)
    {
       $this->_appkey = $appkey;
       $this->_secret = $secret;
       $this->_ip = $ip;
       $this->_port = $port;
       $this->_isHttps = $isHttps;
    }

    public function curlPost($uri,$postData,$timeout){
        $url = $this->_isHttps?"https://".$this->_ip.":".$this->_port.$uri:"http://".$this->_ip.":".$this->_port.$uri;
        if(is_array($postData)){
            $postData=http_build_query($postData);
        }
        $guid = new Guid();
        $str_guid = $guid->toString();
        $ch = curl_init();
        //超时时间
//        curl_setopt($ch,CURLOPT_TIMEOUT,30);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        $headers = array();
        array_push($headers, 'Accept: application/json');
        array_push($headers, 'Content-Type: application/json');
        array_push($headers, 'content-md5: '.md5($postData));
        array_push($headers, 'x-ca-timestamp: '.time());
        array_push($headers, 'x-ca-nonce: '.$str_guid);
        array_push($headers, 'x-ca-key: '.$this->_appkey);


//
//        array_push($headers, 'Connection: keep-alive');
//        array_push($headers, 'Cache-Control: max-age=0');
//        array_push($headers, 'Upgrade-Insecure-Requests: 1');
//        array_push($headers, 'Content-Type: multipart/form-data; boundary=');
//        array_push($headers, 'Accept-Encoding: gzip, deflate');
//        array_push($headers, 'Accept-Language: zh-CN,zh;q=0.8');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch,CURLOPT_HEADER,FALSE);

        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //post提交方式
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            var_dump($data);die;
            return $data;
        }
        else {
            $error = curl_errno($ch);
            curl_close($ch);
            var_dump("errorno:".$error);die;
            return false;
        }
    }
}

class  System
{
    static function currentTimeMillis()
    {
        list($usec,$sec)  =  explode(" ",microtime());
        return  $sec.substr($usec,  2,  3);
    }
}
class NetAddress
{
    private static $Name  =  'localhost';
    private static $IP  =  '127.0.0.1';
   public static function  getLocalHost()  //  static
    {
        $address  =  new  NetAddress();
        $address->Name  =  self::$Name;
        $address->IP  =  self::$IP;
        return  $address;
    }
    function  toString()
    {
        return  strtolower($this->Name.'/'.$this->IP);
    }
}
class  Random
{
   static function  nextLong()
    {
        $tmp  =  mt_rand(0,1)?'-':'';
        return  $tmp.rand(1000,9999).rand(1000,9999).rand(1000,9999).rand(100,999).rand(100,999);
    }
}
//  三段
//  一段是微秒  一段是地址  一段是随机数
class  Guid
{
    var  $valueBeforeMD5;
    var  $valueAfterMD5;
    function __construct()
    {
        $this->getGuid();
    }
    //
    function  getGuid()
    {
        $this->valueBeforeMD5  =   "localhost"."/"."127.0.0.1".':'.System::currentTimeMillis().':'.Random::nextLong();
        $this->valueAfterMD5  =  md5($this->valueBeforeMD5);
    }
    function  newGuid()
    {
        $Guid  =  new  Guid();
        return  $Guid;
    }
    function  toString()
    {
        $raw  =  strtoupper($this->valueAfterMD5);
        return  substr($raw,0,8).'-'.substr($raw,8,4).'-'.substr($raw,12,4).'-'.substr($raw,16,4).'-'.substr($raw,20);
    }
}