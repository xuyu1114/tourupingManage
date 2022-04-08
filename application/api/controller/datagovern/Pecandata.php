<?php
namespace app\api\controller\datagovern;


use app\api\library\SqlsrvHelp;
use app\common\controller\Api;
use think\Db;

//首页部分数据
class Pecandata extends Api {

    protected $sqlHelp;
    public function _initialize()
    {
        //允许所有跨域请求
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers:*");
    }

    function getFolder($path){
        $dirs = [];
        if(is_dir($path)){
            $dir = scandir($path);
            foreach ($dir as $value){
                $sub_path =$path .'/'.$value;
                if($value == '.' || $value == '..'){
                    continue;
                }else if(is_dir($sub_path)){
                    array_push($dirs,$value);
                }else{
                    continue;
                    //.$path 可以省略，直接输出文件名
                    echo ' 最底层文件: '.$path. ':'.$value.' <hr/>';
                }
            }
        }
        return $dirs;
    }
    public function get_text($path){
        $text = [];
        if(is_dir($path)){
            $dir = scandir($path);
            foreach ($dir as $value){
                $sub_path =$path .'/'.$value;
                if($value == '.' || $value == '..'){
                    continue;
                }else if(is_dir($sub_path)){
                    //array_push($dirs,$value);
                }else{
                    $temp = array(
                        "text"=>$value,
                        "text_path"=>$path. '/'.$value
                    );
                    array_push($text,$temp);
                }
            }
        }
        return $text;
    }
    public function getDirs(){
        $base_dir = "D:/WWW/trpManage/runtime/log/";
        $floders = $this->getFolder($base_dir);
        $ret_data = [];
        foreach ($floders as $key=>$val){
            $text_info = $this->get_text($base_dir.$val);
            $ret_data[$val] = $text_info;
        }
        $this->success("success",$ret_data,0);
    }

    public function getData(){
       //产业主体数据
        $this->sqlHelp = new SqlsrvHelp();
        $sql = "select * from(
            select top 1 * from (
            select top 2 count(1) as zcnt from Custom_CYCL_ZDXZ_XZML
            where nd=year(getdate()) or nd = year(getdate())-1
            group by nd order by nd desc
            ) t where t.zcnt>0
            ) t1,(
            select count(1) hzscnt from Custom_CYZT_ZYHZS) t2,(
            select count(1) zzdhcnt from Custom_CYZT_ZZDH) t3,(
            select count(1) qycnt from Custom_CYZT_CHQY)t4,(
            --品牌企业
            select count(distinct SiteId) sitecnt from Tag_CertifiCate
            where CertifiType='201' and Status=0 and LEN(SiteId)>0)t6,(
            --炒货企业
            select COUNT(1) jgccnt from (
            select  distinct company_name,legal_name,address,phone from Custom_in_company where company_name is not null  and address is not null )a) t7,(
            --示范基地
            select count(1) jdcnt from Custom_ZRHJ_QXSJ_JDXX)t8,(
            --新型职业农民
            select COUNT(1) zynmcnt from Custom_CYZT_NJZH)t9,(
            --农技专家
            select count(1) njcnt from Custom_CYZT_QTZJ)t10";
         $res = $this->sqlHelp->query($sql);
         if(empty($res)){
             $ret_data['subject'] =["zcnt"=>0,"hzscnt"=>0,"zzdhcnt"=>0,"qycnt"=>0,"sitecnt"=>0,"jgccnt"=>0,"jdcnt"=>0,"zynmcnt"=>0,"njcnt"=>0];
         }else{
             $ret_data['subject'] = $res[0];
         }

        //产业_四大趋势
        $this->sqlHelp = new SqlsrvHelp();
        $sqls = "select ND,CYMJ,JGCL,CYCL,CYCZ/100 CYCZ,BZ from Col_CYTJXX order by ND";
        $res = $this->sqlHelp->query($sqls);
        if(empty($res)){
            $ret_data['scale'] = [	"ND"=>0,"CYMJ"=>0, "JGCL"=> 0,"CYCL"=> 0,"CYCZ"=> 0,"BZ"=> 0];
        }else{
            $ret_data['scale'] = $res[count($res)-1];
        }


        //当月病虫害防控
       $month = (int)date("m");
       $data = Db::connect("db_2")->table("bl_pestbank")->field("title as name,imgs")->where("find_in_set($month,months)")->select();
       foreach ($data as $key=>&$val) {
           $imgs = explode(",",$val['imgs']);
           $val['imgs'] = $imgs;
       }
       $ret_data['pest']['data'] = $data;
       $ai_count = Db::connect("db_2")->table("bl_dist_date")->count();
       $ret_data['pest']['ai'] = $ai_count;
       $report_count =  Db::connect("db_2")->table("bl_dist_question")->count();
       $ret_data['pest']['report'] = $report_count;
       $warning_count = 91;//Db::connect("db_2")->table("bl_pestbank")->count();
       $ret_data['pest']['warning'] = $warning_count;

       //品牌价值
       $brand = array(
           ['name'=>2017,"value"=>22.58],
           ['name'=>2018,"value"=>24.95],
           ['name'=>2019,"value"=>27.02],
           ['name'=>2020,"value"=>29.82],
       );
       $ret_data['brand'] = $brand;

       //消费者画像
       $kouwei =  Db::connect("db_2")->table('bl_ec_buy_categoty_analysis')->field('`index` as name,proportion as value')->where(['data_type'=>'口味分析'])->select();
       $ret_data['kouwei'] = $kouwei;
       $zhonglei =  Db::connect("db_2")->table('bl_ec_buy_categoty_analysis')->field('`index` as name,proportion as value')->where(['data_type'=>'种类分析'])->select();
       $ret_data['zhonglei'] =$zhonglei;

       //直播电商观看人数占比
       $datetime_new = Db::connect("db_2")->table('bl_ec_province_viewer')->field("datetime")->order("id desc")->find()['datetime'];
       //获取当月数据
       $bottm_data = Db::connect("db_2")->table('bl_ec_province_viewer')
           ->field("province as area,view_proportion as viewer_count,'-' as contrast")
           ->order("view_proportion desc")
           ->where(['datetime'=>$datetime_new])->select();
//       $datetime_new = "2022年1月";
       //获取上个月的时间
       $time = str_replace("年",'-',$datetime_new);
       $month = substr($time,5,2);
       if($month<10){
           $time = str_replace("年",'-0',$datetime_new);
       }else{
           $time = str_replace("年",'-',$datetime_new);
       }
       $time = strtotime(str_replace("月",'-',$time)."01");

       $last_month_time =strtotime("-1 month",$time);
       $year = date("Y",$last_month_time);
       $month = date("m",$last_month_time);
       $month = (int)$month;
       $last_month = $year."年".$month."月";
       $bottm_data_last = Db::connect("db_2")->table('bl_ec_province_viewer')
           ->field("province as area,view_proportion as viewer_count,'-' as contrast")
           ->where(['datetime'=>$last_month])->select();
       //计算环比
       if(!empty($bottm_data_last)){
           $column = array_column($bottm_data_last,"area");
           $bottm_data_last = array_combine($column,$bottm_data_last);
           foreach ($bottm_data as &$val){
               if(array_key_exists($val['area'],$bottm_data_last)){
                   $this_month_count= $val['viewer_count'];
                   $last_month_count= $bottm_data_last[$val['area']]['viewer_count'];
                   if($last_month_count<=0){
                       continue;
                   }
                   $contrast = sprintf('%.2f',($this_month_count-$last_month_count)/$last_month_count)*100;
                   $val['contrast'] = $contrast."%";
               }
           }
       }
       $ret_data['turnover'] = $bottm_data;
       //消费者能力分布
       $power = Db::connect("db_2")->table('bl_ec_buyer_portrait')->field('limits as name,proportion as value')->where(['data_type'=>"消费能力分析"])->select();
       $ret_data['buyer_power'] = $power;
       //浙农码赋码
       $znm = array(
           "green"=>4396,
           "yellow"=>43,
           'red'=>4
       );
       $ret_data['znm']=$znm;
       //农政贷
       $nzd = array(
           'credit'=>103550000,
           'loan'=>31570000,
           'data'=>array(
               ["name"=>"申请人数", "value"=>622, "ratio"=>"100%"],
               ["name"=>"授信人数", "value"=>410, "ratio"=>"66%"],
               ["name"=>"放款人数", "value"=>143, "ratio"=>"23%"]
           )
       );
       $ret_data['loan']=$nzd;
       $this->success("success",$ret_data,0);
   }


}