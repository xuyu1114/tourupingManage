<?php
namespace app\api\controller\datagovern;


use app\api\library\HttpUtil;
use app\api\library\SKHttpClient;
use app\api\library\SqlsrvHelp;
use app\common\controller\Api;
use think\Exception;

//临安山核桃特色产业化应用大屏部分数据
class Camera extends Api {

   public function getCameraData(){
       try{
           // 组装POST请求body
//            $body = array(
//                "cameraIndexCode"=>'a10cafaa777c49a5af92c165c95970e0'
//            );
            $body =  "{'cameraIndexCode': 'a10cafaa777c49a5af92c165c95970e0'}";
            // 填充Url
            $uri ="/artemis/api/resource/v1/cameras/indexCode";
            $http_help = new HttpUtil("22748257", "p43FrRKsHcLZqWSRqcn5", "218.108.67.197", 443, true);
            $http_help->curlPost($uri,$body,15);
       }catch (Exception $e){
           var_dump($e->getMessage());die;
       }
   }

}