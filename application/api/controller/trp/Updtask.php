<?php
namespace app\api\controller\trp;

use app\api\library\SKHttpClient;
use app\api\library\Utils;
use think\Db;
use think\Exception;
use think\Log;

class Updtask{

    /**
     * 同步店铺信息
     */
    public function synCompanyData(){
        Db::startTrans();
        try {
            $url = "https://lany.spdapp.com/GetService/Getin_company";
            $sk = SKHttpClient::getInstance();
            $where_param = array(
                "appkey" => '9ecde8bd-0ce2-81e5-0803-4512c183eccb',
            );
            //获取农资企业数据
            $res = $sk->bigDataGet($url,$where_param);
            if(!$res){
                throw new Exception("接口未返回任何数据");
            }
            $company_info = json_decode($res,true);
            if(!is_array($company_info)||empty($company_info)){
                Log::info($res);
                throw new Exception("接口返回数据解析失败");
            }
            $local_social_code = Db::table("trp_company")->column("social_code");
            //$local_company = Db::table("trp_company")->column("social_code,id,company_name");
            $local_company_name = Db::table("trp_company")->column("company_name");
            $ins_company = array();
            foreach ($company_info as $key => $val){
                //查询到的数据的社会信用码在本地也存在 则不需要插入数据
                if(in_array($val['social_code'],$local_social_code)&&in_array($val['company_name'],$local_company_name)){
//                    //社会信用号码相同 但是店铺名称不相同 需要更新店铺名称
//                    if($val["company_name"]!=$local_company[$val['social_code']]["company_name"]){
//                        $upd_res = Db::table("trp_company")->where(["social_code"=>$val['social_code']])->update(["company_name"=>$val["company_name"]]);
//                        if(!$upd_res){
//                            throw new Exception("店铺：【{$local_company[$val['social_code']]["company_name"]}】名称更新失败");
//                        }
//                    }
                    continue;
                }
                //社会信用码不存在则需要新增店铺
                $single_company = array(
                    "in_company_id"=>md5($val['social_code']),
                    "company_name"=>$val['company_name'],
                    "social_code"=>$val['social_code'],
                    "legal_name"=>$val['legal_name'],
                    "card_no"=>$val['card_no'],
                    "phone"=>$val['phone'],
                    "address"=>$val['address'],
                    "business_category"=>$val['business_category'],
                    "business_main"=>$val['business_main'],
                    "business_area"=>$val['business_area'],
                    "createtime"=>date("Y-m-d H:i:s",time()),
                    "updatetime"=>date("Y-m-d H:i:s",time()),
                );
                array_push($ins_company,$single_company);
            }
            if(!empty($ins_company)){
               $ins_res = Db::table("trp_company")->insertAll($ins_company);
               if(!$ins_res){
                   throw new Exception("新增农资店失败");
               }
            }
            Db::commit();
            var_dump("success");die;
        }catch (Exception $e){
            Db::rollback();
            Log::error("获取农资企业定时任务错误：".$e->getMessage());
            Utils::sendDingMessage("获取农资企业定时任务告警：".$e->getMessage());
            var_dump("error:".$e->getMessage());die;
        }
    }

    /**
     * 同步领用数据
     */
    public function synCollectData(){
        Db::startTrans();
        try {
            $begin_data = $_POST["begin"];
            $end_data =  $_POST["end"];
            if(empty($begin_data)){
                $begin_data = Db::table("trp_collect")->order("optime desc")->limit(1)->column("optime");
                $begin_data = $begin_data[0];
            }
            if(empty($end_data)){
                $end_data = date("Y-m-d 23:59:59",strtotime("-1 day"));
            }
            $url = "https://lany.spdapp.com/GetService/Getin_collect";
            $sk = SKHttpClient::getInstance();
            $where_param = array(
                "appkey" => '9ecde8bd-0ce2-81e5-0803-4512c183eccb',
                "kssj" => $begin_data,
                "jssj" => $end_data
            );
            //获取领用记录
            $res = $sk->get($url,$where_param);
            if(!$res){
                throw new Exception("接口未返回任何数据");
            }
            $collect_info = json_decode($res,true);
            if(!is_array($collect_info)||empty($collect_info)){
                Log::info($res);
                throw new Exception("接口返回数据解析失败");
            }
            $ins_datas = array();

            //获取已存在的数据
            $local_collect = Db::table("trp_collect")
                ->where("optime",">",$begin_data)
                ->where("optime","<=",$end_data)
                ->column("transaction_no");
            foreach ($collect_info as $value){
                //对比，已存在：跳过
                if(in_array($value['transaction_no'],$local_collect)){
                    continue;
                }
                //否则 插入数据
                $single_data = array(
                    "in_collect_id" => md5($value['transaction_no'].$value['goods_no']),
                    "transaction_no" => $value['transaction_no'],
                    "goods_no" => $value['goods_no'],
                    "category" => $value['category'],
                    "brand_name" => $value['goods_name'],
                    "attr" => $value['attr'],
                    "out_num" => $value['num'],
                    "subsidy_price" => 0,
                    "sales_price" => $value['price'],
                    "shop_name" => $value['shop_name'],
                    "farmer_name" => $value['farmer_name'],
                    "card_no" => $value['card_no'],
                    "address" => $value['address'],
                    "village_name" => $value['village_name'],
                    "optime" => $value['optime'],
                    "createtime"=>date("Y-m-d H:i:s",time()),
                    "updatetime"=>date("Y-m-d H:i:s",time()),
                );

                //计算重量
                $num = intval($value["attr"]);
                $num = $num<1?1:$num;
                $unit = trim(str_replace($num,"",$value["attr"]));
                if(in_array($unit,['千克','Kg','升','L','公斤','l'])){
                    $weight = $num*$value['num'];
                }else{
                    $weight = $num*$value['num']/1000;
                }
                $single_data['weight'] = $weight;
                array_push($ins_datas,$single_data);
            }
            if(empty($ins_datas)){
                var_dump("同步农资品领用数据没有需要插入的新数据：没有获取到新的数据或者重复跑了task");
                Db::rollback();
                Utils::sendDingMessage("同步农资品领用数据没有获取到新的数据告警，是否重复跑了task？或者换个时间段试试");
                die;
            }
            $res = Db::table("trp_collect")->insertAll($ins_datas);
            if(!$res){
                throw new Exception("新增农资品领用数据失败");
            }
            Db::commit();
            var_dump("success，插入数据：".$res."条");
            die;
        }catch (Exception $e){
            Db::rollback();
            Log::error("同步农资品领用数据定时任务错误：".$e->getMessage());
            Utils::sendDingMessage("同步农资品领用数据定时任务告警：".$e->getMessage());
            var_dump("error:".$e->getMessage());die;
        }
    }

    /**
     * 同步采购表数据
     */
    public function synPurchaseData(){
        try{
            $begin_data = $_POST["begin"];
            $end_data =  $_POST["end"];
            if(empty($begin_data)){
                $begin_data = Db::table("trp_goods_purchase")->order("optime desc")->limit(1)->column("optime");
                $begin_data = $begin_data[0];
            }
            if(empty($end_data)){
                $end_data = date("Y-m-d 23:59:59",strtotime("-1 day"));
            }
            $url = "https://lany.spdapp.com/GetService/Getin_purchase";
            $sk = SKHttpClient::getInstance();
            $where_param = array(
                "appkey" => '9ecde8bd-0ce2-81e5-0803-4512c183eccb',
                "kssj" => $begin_data,
                "jssj" => $end_data
            );
            //获取领用记录
            $res = $sk->get($url,$where_param);
            if(!$res){
                throw new Exception("接口未返回任何数据");
            }
            $purchase_info = json_decode($res,true);
            if(!is_array($purchase_info)||empty($purchase_info)){
                Log::info($res);
                throw new Exception("接口返回数据解析失败");
            }
            //获取已存在的数据
            $local_purchase = Db::table("trp_goods_purchase")
                ->where("optime",">",$begin_data)
                ->where("optime","<=",$end_data)
                ->column("transaction_no");

            $ins_datas = array();
            foreach ($purchase_info as $value) {
                //对比，已存在：跳过
                if (in_array($value['transaction_no'], $local_purchase)) {
                    continue;
                }
                //否则 插入数据
                $single_data = array(
                    "in_purchase_id" => md5($value['transaction_no'] . $value['goods_no']),
                    "transaction_no" => $value['transaction_no'],
                    "shop_name" => $value['shop_name'],
                    "goods_name" => $value['goods_name'],
                    "category" => $value['category'],
                    "attr" => $value['attr'],
                    "unit" =>'',
                    "price" => $value['price'],
                    "num" => $value['num'],
                    "supplier_name" => $value['supplier_name'],
                    "goods_no" => $value['goods_no'],
                    "weight" => intval($value['attr'])*intval( $value['num']),
                    "optime" => $value['optime'],
                    "createtime" => date("Y-m-d H:i:s", time()),
                    "updatetime" => date("Y-m-d H:i:s", time()),
                );
                array_push($ins_datas,$single_data);
            }
            if(empty($ins_datas)){
                var_dump("同步采购数据没有需要插入的新数据没有获取到新的数据，或者重复跑了task");
                Db::rollback();
                Utils::sendDingMessage("同步采购数据没有获取到新的数据告警，是否重复跑了task？或者换个时间段试试");
                die;
            }
            $res = Db::table("trp_goods_purchase")->insertAll($ins_datas);
            if(!$res){
                throw new Exception("新增农资品领用数据失败");
            }
            Db::commit();
            var_dump("success，插入数据：".$res."条");
            die;
        }catch (Exception $e){
            Db::rollback();
            Log::error("同步采购数据定时任务错误：".$e->getMessage()."，行：".$e->getLine());
            Utils::sendDingMessage("同步采购数据定时任务告警：".$e->getMessage());
            var_dump("error:".$e->getMessage());die;
        }
    }

}