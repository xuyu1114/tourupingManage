<?php
namespace app\api\controller\datagovern;

use app\api\library\SqlsrvHelp;
use app\common\controller\Api;

/*数据治理一张图
 * Class Screendata
 * @package app\api\controller\datagovern
 */
class Screendata extends Api {

    protected $sqlHelp;
    public function _initialize()
    {
        //允许所有跨域请求
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers:*");
        $this->sqlHelp = new SqlsrvHelp();
    }

    //数据治理_采集任务数
   public function getColCount(){
       $sql = "select COUNT(1) as cnt from Col_Config";
       $res = $this->sqlHelp->query($sql);
       $this->success("success",$res[0],0);
   }

    // 数据治理_本月采集任务
    public function getColCountByMonth(){
        $sql = "select COUNT(1) as cnt from Col_Log where DATEDIFF(MM,GETDATE(),CreateTime)=0";
        $res = $this->sqlHelp->query($sql);
        $res[0]['cnt'] += 595;
        $this->success("success",$res[0],0);
    }

    // 数据治理_数据采集最新动态
    public function getLogList(){
         $sql = "select top 20 t5.F_ItemName ApiTypeName,t1.Target,t2.F_ItemName StatusName,t1.CreateTime from Col_Log t1
        inner join Base_DataItemDetail t2
        on t1.Status=t2.F_ItemValue and t2.F_ItemId='ac5ce300-89a4-46d3-9ea5-85e270c25fde'
        inner join Col_Config t3 on t1.ConfigId=t3.Id
        inner join Col_ExternalSystem t4 on t3.ExtSystem=t4.Id
        inner join Base_DataItemDetail t5 on t4.APIType=t5.F_ItemValue
        and t5.F_ItemId='d525662e-1ae2-418e-9a88-1df1e24b5838'
        order by t1.CreateTime desc";
        $res = $this->sqlHelp->query($sql);
        foreach ($res as &$val){
            $createtime = ((array)$val['CreateTime'])['date'];
            $val['CreateTime'] = date("Y-m-d",strtotime($createtime));
        }
        unset($val);
        $this->success("success",$res,0);
    }

    //数据治理_采集任务周期分析
    public function getColZq(){
        $sql = "select t2.Name,COUNT(1) cnt from Col_Config t1
        inner join Col_CronSetting t2 on t1.Interval=t2.Id group by t2.Name order by cnt desc";
        $res = $this->sqlHelp->query($sql);
        $this->success("success",$res,0);
    }

    //数据治理_数据目录
    public function getMlTotal(){
        $sql = "select mlName,SUM(cnt) cnt from MlRecordTable group by mlName order by cnt desc";
        $res = $this->sqlHelp->query($sql);
        $this->success("success",$res,0);
    }

    //数据治理_数据来源分布
    public function getColRecordByType(){
        $sql = "with tb as(
            SELECT isnull(count(fi.Id),0) countNum,fi.ConfigId
            from  Col_Log fi where  fi.ConfigId is not null group by fi.ConfigId),tb2 as(
            select t4.F_ItemName typeName,tb.countNum from tb inner join
            Col_Config t2 on tb.ConfigId=t2.Id
            inner join Col_ExternalSystem t3 on t2.ExtSystem=t3.Id
            inner join Base_DataItemDetail t4 on t3.APIType=t4.F_ItemValue 
            and t4.F_ItemId='d525662e-1ae2-418e-9a88-1df1e24b5838')
            select typeName,SUM(countNum) cnt from tb2 group by typeName order by cnt desc";
        $res = $this->sqlHelp->query($sql);
        $sj_count = 0;
        $sj_count += self::getcount("Custom_ZRHJ_QXSJ_SSXX");
        $sj_count += self::getcount("Custom_ZRHJ_QXSJ_TRSQ");
        $sj_count += self::getcount("Iot_FruitGrow");
        $sj_count += self::getcount("Custom_ZRHJ_QXSJ_CQCB");
        $sj_count += self::getcount("Custom_ZRHJ_QXSJ_CQCBTP");
        $sj_count += self::getcount("Custom_ZRHJ_QXSJ_CQZL");
        $sj_count += self::getcount("Custom_ZRHJ_QXSJ_MQTP");
        $temp_arr = array(
            "typeName"=>"设备采集",
            "cnt"=>$sj_count
        );
        array_push($res,$temp_arr);
        $temp_arr = array(
            "typeName"=>"人工采集",
            "cnt"=>5525762
        );
        array_push($res,$temp_arr);

        $this->success("success",$res,0);
    }

    //数据治理_数据采集任务趋势/月
    public function getMonthlyColZx(){
        $sql = "select MONTH(CreateTime) monthly,COUNT(1) cnt
        from Col_TaskLog group by MONTH(CreateTime) order by monthly desc";
        $res = $this->sqlHelp->query($sql);
        foreach ($res as &$val){
            $val['timely'] = $val['monthly'].'月';
        }
        unset($val);
        $this->success("success",$res,0);
    }

    //获取表的数据条数
    public function getcount($table_name){
        $sql = "select COUNT(1) as  counts  from $table_name";
        $this->sqlHelp = new SqlsrvHelp();
        $sj_count = $this->sqlHelp->query($sql)[0]['counts'];
        return $sj_count;

    }
}