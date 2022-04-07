<?php
namespace app\api\library;

use think\Exception;
use think\Log;

class SqlsrvHelp{
    protected $connect;
    protected $res;
    protected $config;
    /*构造函数*/
    function __construct(){
        $this->connect = sqlsrv_connect('192.168.1.130', array('Database' => 'GTC_lasht', 'UID' => 'sa' , 'PWD' => 'r_159357'));
        if( $this->connect == false){
            throw new Exception("数据库链接失败",-1);
        }
    }

    //用于有记录结果返回的操作，特别是SELECT操作
    public function query($sql){
            $ret_data = array();
            @sqlsrv_query("SET NAMES UTF8",$this->connect);
            $result = @sqlsrv_query($this->connect, $sql);
            if($result===false){
                Log::error("sqlserver数据库链接失败".iconv("GBK","UTF-8",sqlsrv_errors()[0]['message']));
                @sqlsrv_close($this->connect);
                return false;
            }
            while ( $re = @sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC )) {
               foreach ($re as &$val){
                   if(!is_string($val)){
                       continue;
                   }
                   $val = iconv("GBK","UTF-8",$val);
               }
               array_push($ret_data,$re);
            }
            @sqlsrv_close($this->connect);
            return $ret_data;
        }
}
?>