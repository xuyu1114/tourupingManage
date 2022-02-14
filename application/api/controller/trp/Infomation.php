<?php
namespace app\api\controller\trp;

use app\api\library\Utils;
use app\common\controller\Api;
use think\Config;
use think\Db;
use think\Exception;

class Infomation extends Api{

    /**
     * 获取 经营主体入网数，店铺交易活跃等数据1
     */
    public function getTotalAndActive(){
        try {
            $last_month_today = date("Y-m-d 00:00:00",strtotime("-30 day"));
            $today = date("Y-m-d 23:59:59",time());
            $data_where = [$last_month_today,$today];
            $alive_shop = Db::table("trp_collect")
                ->group("shop_name")
                ->where("optime","between",$data_where)
                ->column("shop_name");
            $all_shop = Db::table("trp_company")->column("id");
            $ret_data['shop_count_all'] =count($all_shop);
            $ret_data['shop_count_alive'] = count($alive_shop);
            $ret_data['shop_count_unalive'] =count($all_shop) - count($alive_shop);
            $this->success("success",$ret_data,0);
        }catch (Exception $e){
            $this->error("数据查询失败",[],-1);
        }
    }

    /**
     * 获取店铺采购排名
     */
    public function getHerbicidePurchasing(){
        try{
            $top = $this->request->post('top/d',11);
            $last_month_today = date("Y-m-d 00:00:00",strtotime("-30 day"));
            $today = date("Y-m-d 23:59:59",time());
            $sql = "select a.shop_name,sum(if(a.unit='g'||a.unit='ml'||a.unit='克'||a.unit='毫升',(a.attr*a.num/1000),a.attr*a.num)) total from trp_goods_purchase a
                    left join trp_goods_contrast b on a.goods_name = b.goods_name
                where b.current_goods_name = '草甘膦'  and a.optime BETWEEN '{$last_month_today}' and '{$today}' 
                GROUP BY a.shop_name
                ORDER BY total desc
                limit {$top}";
            $result = Db::query($sql);
            $this->success("success",$result,0);
        }catch (Exception $e){
            $this->error("数据查询失败",[],-1);
        }


    }


    /**
     * 获取商品分类种类数量
     */
    public function getGoodsList(){
        try{
            $sql = "SELECT a.category,SUM(1) as counts from (select category,goods_name from trp_goods group BY category,goods_name) a GROUP BY category";
            $data_goods_type = Db::query($sql);
            $arr_type = array(
                "ny"=>array('床土调酸剂','植物生长调节剂','杀菌剂','杀虫剂','杀螨剂','除草剂'),//农药
                "hf"=>array('复混（合）肥料','有机无机复混肥料','有机肥料','氮肥','水溶肥料','磷肥','钾肥'),//化肥
                "sy"=>array('杀鼠剂'),//兽药
            );
            $result = array(
                "ny"=>0,//农药
                "hf"=>0,//花费
                "sy"=>0,//兽药
                "other" => 0,//其他
            );

            //组装数据
            foreach ($data_goods_type as $key=>$value){
                if(empty($value['category'])){
                    $result['other'] += $value['counts'];
                    continue;
                }
                foreach ($arr_type as $t_key => $t_val){
                    if(in_array($value['category'],$t_val)){
                        $result[$t_key] += $value['counts'];
                    }
                }
            }

            $ret_data = array(
                ["categoryName"=>"其他","totalAmount"=>$result['other']],
                ["categoryName"=>"兽药","totalAmount"=>$result['sy']],
                ["categoryName"=>"农药","totalAmount"=>$result['ny']],
                ["categoryName"=>"化肥","totalAmount"=>$result['hf']],
            );
            $this->success("success",$ret_data,0);
        }catch (Exception $e){
            $this->error("数据查询失败",[],-1);
        }
    }

    /**
     * 农资品销售情况排行数据
     */
    public function getSaleInfo(){
        try{
            $last_month_today = date("Y-m-d 00:00:00",strtotime("-30 day"));
            $today = date("Y-m-d 23:59:59",time());
            $top = $this->request->post('top/d',11);
            $arr_category = array(
                "ccj" => "('除草剂')",
                "ny"=>"('床土调酸剂','植物生长调节剂','杀菌剂','杀虫剂','杀螨剂','除草剂')",//农药
                "hf"=>"('复混（合）肥料','有机无机复混肥料','有机肥料','氮肥','水溶肥料','磷肥','钾肥')",//花费
                "sy"=>"('杀鼠剂')",//兽药
                "other" =>"('','其他')"
            );
            $ret_data = [];
            foreach ($arr_category as $key => $val){
                $sql = "select b.current_goods_name as goods_name,TRUNCATE(SUM(a.weight),1) as weight from trp_collect a
                        LEFT JOIN trp_goods_contrast b on a.brand_name = b.goods_name
                        where a.category in {$val} and a.optime BETWEEN '{$last_month_today}' and '{$today}' and b.current_goods_name is not null
                        GROUP BY b.current_goods_name 
                        ORDER BY weight desc
                        limit {$top}";
                $data = Db::query($sql);
                $ret_data[$key] = $data;
            }
            $this->success("success",$ret_data,0);
        }catch (Exception $e){
            var_dump($e->getMessage());
            $this->error("数据查询失败",[],-1);
        }
    }


    public function getAllCompany(){
        try{
            $all_company = Db::table("trp_company")->column("id,address,business_area,business_category,company_name,legal_name,longitude_latitude,phone");

            $this->success("success",array_values($all_company),0);
        }catch (Exception $e){
            $this->error("数据查询失败",[],-1);
        }
    }

    /**
     * 获取两个柱状图数据
     */
    public function getTwoMinus(){
        try{
            $last_year = date("Y-01-01 00:00:00",strtotime("-1 year"));
            $this_year = date("Y-01-01 00:00:00",time());
            $cgl_weight_last_year = Db::table("trp_collect")
                ->where("optime",">",$last_year)
                ->where("optime","<",$this_year)
                ->whereLike("brand_name","%草甘%")
                ->sum("weight");
            $cgl_weight_this_year = Db::table("trp_collect")
                ->where("optime",">",$this_year)
                ->whereLike("brand_name","%草甘%")
                ->sum("weight");
            $ret_data["cgl"] = array(
                date("Y",strtotime($last_year)) => sprintf("%.1f",$cgl_weight_last_year) ,
                date("Y",strtotime($this_year)) => sprintf("%.1f",$cgl_weight_this_year),
            );
            $hf_weight_last_year = Db::table("trp_collect")
                ->where("optime",">",$last_year)
                ->where("optime","<",$this_year)
                ->whereLike("category","%肥%")
                ->sum("weight");
            $hf_weight_this_year = Db::table("trp_collect")
                ->where("optime",">",$this_year)
                ->whereLike("category","%肥%")
                ->sum("weight");
            $ret_data["hf"] = array(
                date("Y",strtotime($last_year)) => sprintf("%.1f",$hf_weight_last_year),
                date("Y",strtotime($this_year)) => sprintf("%.1f",$hf_weight_this_year),
            );
            $this->success("success",$ret_data,0);
        }catch (Exception $e){
            $this->error("数据查询失败",[],-1);
        }
    }

    public function insGoodsConstrast(){
        $goods_name = $this->request->post('goods_name/s','');
        $alias_name = $this->request->post('alias_name/s','');
        $ignor_id = $this->request->post('ignor_id','');
        $ignor_where = [];
        if(empty($alias_name)){
            $alias_name = $goods_name;
        }
        if(!empty($ignor_id)){
            ["id","not in","({$ignor_id})"];
        }
        $like_goods = Db::table("trp_collect")->whereLike("brand_name","%{$goods_name}%")
            ->where("")
            ->group("brand_name")
            ->column("brand_name");
        $ins_data_all = [];
        foreach ($like_goods as $val){
            $ins_data = array(
                "current_goods_name" =>$alias_name,
                "goods_name" =>$val,
            );
            array_push($ins_data_all,$ins_data);
        }
        $s = Db::table("trp_goods_contrast")->insertAll($ins_data_all);
        var_dump($s);die;
    }

    //浙农码使用情况
    public function codeUserSituation(){
        $info = Db::table("trp_fylz_company")->field("code_type,sum(code_type) as count")->where('code_type is not null')->group("code_type")->select();
        $retdata = array(
            'green_code'=>0,
            'yello_code'=>0,
            'red_code'=>0,
        );
        foreach ($info as $val){
            if($val['code_type']==1){
                $retdata['green_code'] += $val['count'];
                continue;
            }
            if($val['code_type']==2){
                $retdata['yello_code'] += $val['count'];
                continue;
            }
            if($val['code_type']==3){
                $retdata['red_code'] += $val['count'];
            }
        }
        $this->success('success',$retdata,0);
    }

    /**
     * 获取浙农码主体总数，总购买数和实名购买数
     */
    public function getTotalTransaction(){
        try{
            $all_order = Db::table("trp_collect")->count(1);
            $real_name_order = Db::table("trp_collect")->where("card_no","<>","")->count();
            $code_company_count = Db::table("trp_fylz_company")->where("znmUrlCode","<>","")->count();
            $ret_data['code_company_count'] = $code_company_count;
            $ret_data['all_order'] = $all_order;
            $ret_data['real_name_order'] = $real_name_order;
            $this->success("success",$ret_data,0);
        }catch (Exception $e){
            $this->error("数据查询失败",[],-1);
        }
    }

    //农药使用标准
    public function useStandard(){
        $info = Db::table('trp_fylz_standard')->select();
        $this->success("success",$info,0);
    }

    //主体肥药使用情况分析
    public function useSituationAli(){
        $where['fertili_name'] = ["<>",''];
        $where['area'] = [">",0];
        $info = Db::table("trp_fylz_info")
            ->field("company_name,sum(area) as area,'未超标' as ny_situation,'未超标' as fl_situation")
            ->where($where)
            ->group('company_name')
            ->order('area desc')
            ->select();
        $this->success("success",$info,0);
    }

    //肥药采购情况
    public function buySituation(){
        try{
            $type = $this->request->post('type',1);//查询类型 1：化肥 2：农药
            $page = $this->request->post('page/d',1);
            $page_size = $this->request->post('page_size/d',20);
            $where = [];
            if($type == 1){
                $where['category'] = ['in','复混（合）肥料,有机无机复混肥料,有机肥料,氮肥,水溶肥料,磷肥,钾肥'];
            }else{
                $where['category'] = ['in','床土调酸剂,植物生长调节剂,杀菌剂,杀虫剂,杀螨剂,除草剂'];
            }
            $data = Db::table('trp_collect')
                ->field('optime,farmer_name,brand_name,out_num,attr')
                ->where($where)
                ->order('optime desc')
                ->page($page,$page_size)
                ->select();
            foreach ($data as &$val){
                $attr_num = (int)$val['attr'];
                $sum_num = $val['out_num']*$attr_num;
                $unit = str_replace($attr_num,'',$val['attr']);
                $val['amount'] = $sum_num.$unit;
            }
            $this->success("success",$data,0);
        }catch (Exception $e){
            $this->error("数据查询失败",[],-1);
        }
    }

    //肥药使用情况
    public function useSituation(){
        try{
            $type = $this->request->post('type',1);//查询类型 1：化肥 2：农药
            $page = $this->request->post('page/d',1);
            $page_size = $this->request->post('page_size/d',20);
            $where['type'] = $type;
            $where['amount'] = ['>',0];
            $data = Db::table('trp_fylz_info')
                ->field('start_time,company_name,fertili_name,address,amount')
                ->where($where)
                ->order('end_time desc')
                ->page($page,$page_size)
                ->select();
            $this->success("success",$data,0);
        }catch (Exception $e){
            $this->error("数据查询失败",[],-1);
        }
    }

    //获取区域领用数据
    public function getAreaCollectData(){
        $start_date = date('Y-m-d 00:00:00',strtotime("-230 days"));

        $data = Db::table('trp_collect')->alias('a')
            ->join('trp_company b','a.shop_name = b.company_name','left')
            ->field('a.id,a.brand_name,a.out_num,a.attr,b.company_name,b.address')
            ->where('b.company_name is not null and (a.brand_name like "%草甘%" or a.category like "%肥%")')
            ->where(['a.optime'=>['>',$start_date]])
            ->select();

        $ret_data = array();
        $location_conf = Config::get("linan_street");
        foreach ($data as &$val) {
            $street = Utils::getStreet_new($val['address']);
            if(!array_key_exists($street,$location_conf)){
               continue;
            }
            $attr_num = (int)$val['attr'];
            $unit = str_replace($attr_num,'',$val['attr']);
            if(in_array(trim($unit),['千克','Kg','升','L','公斤','l'])){
                $weight =  sprintf('%.2f',$val['out_num']*$attr_num);
            }else{
                $weight =  sprintf('%.2f',$val['out_num']*$attr_num/1000);
            }
            if(!array_key_exists($street,$ret_data)){
                $ret_data[$street]['street'] = $street;
                $ret_data[$street]['lon'] = $location_conf[$street][0];
                $ret_data[$street]['lat'] = $location_conf[$street][1];
                $ret_data[$street]['buy_times'] = 1;
                if(strstr($val['brand_name'],'草甘')){
                    $ret_data[$street]['cgl_buy_weight'] = $weight;
                    $ret_data[$street]['fl_buy_weight'] = 0;
                }else{
                    $ret_data[$street]['cgl_buy_weight'] = 0;
                    $ret_data[$street]['fl_buy_weight'] = $weight;
                }
            }else{
                $ret_data[$street]['buy_times'] += 1;
                if(strstr($val['brand_name'],'草甘')){

                    $ret_data[$street]['cgl_buy_weight'] += $weight;
                }else{
                    $ret_data[$street]['fl_buy_weight'] += $weight;
                }
            }
       }
        $info = array();
        foreach ($ret_data as &$val){
            $val['cgl_buy_weight'] = (string)$val['cgl_buy_weight'];
            array_push($info,$val);
        }
        $this->success("success",$info,0);
    }
}