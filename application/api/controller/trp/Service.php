<?php
namespace app\api\controller\trp;

use app\api\library\SKHttpClient;
use app\api\library\Utils;
use app\common\controller\ServiceBase;
use think\Db;
use think\Exception;
use think\Log;

class Service extends ServiceBase {

    /**
     * 提供农资店信息
     */
    public function getCompanyData(){
        try {
            $start = htmlspecialchars($this->request->post('startDate'),ENT_QUOTES);
            $end =  htmlspecialchars($this->request->post('endDate'),ENT_QUOTES);
            $where = [];
            if(!empty($start)&&!empty($end)){
                $where['createtime'] = array('between',[$start,$end]);
            }
            $ret_data = Db::table('trp_company')
                ->field('
                        in_company_id,
                        company_name,
                        social_code,
                        legal_name,
                        card_no,
                        phone,
                        address,
                        business_category,
                        business_main,
                        business_area,
                        operator_id,
                        longitude_latitude,createtime')
                ->where($where)->order('createtime desc')->select();
            $this->success('success',$ret_data,0);
        }catch (Exception $e){
            Db::rollback();
            Log::error("第三方获取农资企业信息错误：".$e->getMessage());
            $this->error("sysErr",[],-1);
        }
    }

    /**
     * 获取农户领用信息
     */
    public function getCollectData()
    {
        try {
            $start = htmlspecialchars($this->request->post('startDate'), ENT_QUOTES);
            $end = htmlspecialchars($this->request->post('endDate'), ENT_QUOTES);
            if (empty($start) || empty($end)) {
                $this->error("请设置查询的开始时间和结束时间", [], 1001);
            }
            if (strtotime($end) - strtotime($start) > 86400 * 30) {
                $this->error("最多一次可获取三十天内的数据", [], 1001);
            }
            $where['optime'] = array('between', [$start, $end]);
            $ret_data = Db::table('trp_collect')
                ->field('in_collect_id,
                        transaction_no,
                        goods_no,
                        category,
                        brand_name,
                        attr,
                        out_num,
                        subsidy_price,
                        sales_price,
                        shop_name,
                        farmer_name,
                        card_no,
                        address,
                        village_name,
                        optime,
                        operator_id,
                        unit,
                        round(weight,2) as weight')->where($where)->order('optime desc')->select();
            $this->success('success', $ret_data, 0);
        } catch (Exception $e) {
            Db::rollback();
            Log::error("第三方获取农户领用信息错误：" . $e->getMessage());
            $this->error("sysErr", [], -1);
        }
    }
        /**
         * 获取投入品采购信息
         */
        public function getPurchaseData()
        {
            try {
                $start = htmlspecialchars($this->request->post('startDate'), ENT_QUOTES);
                $end = htmlspecialchars($this->request->post('endDate'), ENT_QUOTES);
                if (empty($start) || empty($end)) {
                    $this->error("请设置查询的开始时间和结束时间", [], 1001);
                }
                if (strtotime($end) - strtotime($start) > 86400 * 30) {
                    $this->error("最多一次可获取三十天的数据", [], 1001);
                }
                $where = [];
                if (!empty($start) && !empty($end)) {
                    $where['optime'] = array('between', [$start, $end]);
                }
                $ret_data = Db::table('trp_goods_purchase')
                    ->field('in_purchase_id,
                                transaction_no,
                                shop_name,
                                goods_name,
                                category,
                                attr,
                                unit,
                                price,
                                num,
                                supplier_name,
                                goods_no,
                                optime,
                                operator_id')->where($where)->order('optime desc')->select();
                $this->success('success', $ret_data, 0);
            } catch (Exception $e) {
                Db::rollback();
                Log::error("第三方获取农户领用信息错误：" . $e->getMessage());
                $this->error("sysErr", [], -1);
            }
        }
}