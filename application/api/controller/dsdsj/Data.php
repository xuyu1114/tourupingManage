<?php

namespace app\api\controller\dsdsj;

use app\common\controller\Api;
use think\Log;
Use think\Db;
use think\Exception;

/**
 * 首页接口
 */
class Data extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 左侧数据信息
     * @ApiTitle    (左侧数据信息)
     * @ApiSummary  (左侧数据信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dsdsj/data/index)
     * @ApiParams   (name="type", type="integer", required=true, description="产业类型，1=全部，2=山核桃，3=坚果，4=笋类制品")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="返回数据")
     * @ApiReturn   ({
    'code':'0',
    'msg':'提示信息'
    'data:'结果'
    })
     */
    public function index()
    {
        $type = request()->post('type/d',2);
        $str = '';
        $map = [];
        if($type == 1) {
            $str = 'QB';
            $map['type'] = 0;
        } elseif ($type == 2) {
            $str = 'SHT';
            $map['type'] = 1;
        } elseif ($type == 3) {
            $str = 'JG';
            $map['type'] = 2;
        } elseif ($type == 3) {
            $str = 'SL';
            $map['type'] = 3;
        }

        try {
            $list = Db::table('qs_exponent')
                ->field('Turnover'.$str.' as Turnover,increase'.$str.' as increase,scale'.$str.' as scale,scalePM'.$str.' as scalePM,effect'.$str.' as effect,effectPM'.$str.' as effectPM,deal'.$str.' as deal,dealPM'.$str.' as dealPM,rankingcountry'.$str.' as rankingcountry,rankingprovince'.$str.' as rankingprovince,rankingcity'.$str.' as rankingcity,rankingcountryTB'.$str.' as rankingcountryTB,rankingprovinceTB'.$str.' as rankingprovinceTB,rankingcityTB'.$str.' as rankingcityTB')
                ->where('isDeleted',0)
                ->order('createTime','desc')
                ->find();
            $province = Db::table('qs_flowout')
                ->field('liao2 as 辽宁,ji2 as 吉林,jing1 as 北京,jin1 as 天津,hei1 as 黑龙江,qing1 as 青海,zhe4 as 浙江,ning2 as 宁夏,xin1 as 新疆,gan1 as 甘肃,shan3 as 陕西,yun2 as 云南,qian2 as 贵州,su1 as 江苏,chuan1 as 四川,zang4 as 西藏,min3 as 福建,gan4 as 江西,xiang1 as 湖南,yue4 as 广东,gui4 as 广西,wan3 as 安徽,ji4 as 河北,yu4 as 河南,yu2 as 重庆,qiong2 as 海南,e4 as 湖北,lu3 as 山东,jin4 as 山西,hu4 as 上海,meng2 as 内蒙古,gang3 as 香港,ao4 as 澳门,tai2 as 台湾')
                ->where($map)
                ->order('createTime','desc')
                ->find();
            $temp = [];
            foreach($province as $key => $item) {
                $temp[$key]['value'] = $item;
            }
            array_multisort($temp,SORT_DESC,$province);
            $data = [
                'list' => $list,
                'province' => $province
            ];
            $this->success('success',$data,0);
        } catch (Exception $e) {
            Log::error('查询失败'.$e->getMessage().'，行：'.$e->getLine());
            $this->error('查询失败',[],-1);
        }
    }

    /**
     * 重点产业结构分布
     * @ApiTitle    (重点产业结构分布)
     * @ApiSummary  (重点产业结构分布)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dsdsj/data/inustryData)
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="返回数据")
     * @ApiReturn   ({
    'code':'0',
    'msg':'提示信息'
    'data:'结果'
    })
     */
    public function inustryData()
    {
        try {
            $info = Db::table('qs_structure')
                ->field('ProportionSHT,ProportionSUN,ProportionJG,exponentSHT,exponentSUN,exponentJG,increaseSHT,increaseSUN,increaseJG')
                ->where('isDeleted',0)
                ->order('month','desc')
                ->find();
            $data = [
                'sht' => ['proportion' => $info['ProportionSHT'], 'exponent' => $info['exponentSHT'], 'increase' => $info['increaseSHT']],
                'sun' => ['proportion' => $info['ProportionSUN'], 'exponent' => $info['exponentSUN'], 'increase' => $info['increaseSUN']],
                'jg' => ['proportion' => $info['ProportionJG'], 'exponent' => $info['exponentJG'], 'increase' => $info['increaseJG']],
            ];
            $this->success('success',$data,0);
        } catch (Exception $e) {
            Log::error('查询失败'.$e->getMessage().'，行：'.$e->getLine());
            $this->error('查询失败',[],-1);
        }
    }

    /**
     * 右侧商家品牌数据
     * @ApiTitle    (右侧商家品牌数据)
     * @ApiSummary  (右侧商家品牌数据)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dsdsj/data/brand)
     * @ApiParams   (name="type", type="integer", required=true, description="产业类型，1=山核桃，2=坚果，3=笋类制品")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="返回数据")
     * @ApiReturn   ({
    'code':'0',
    'msg':'提示信息'
    'data:'结果'
    })
     */
    public function brand()
    {
        $type = request()->post('type/d',1);
        $map['type'] = $type;
        $str = '';
        if($type == 1) {
            $str = 'SHT';
        } elseif ($type == 2) {
            $str = 'JG';
        } elseif ($type == 3) {
            $str = 'SUN';
        }
        try {
            //商家规模数量变化趋势
            $info['scale'] = Db::table('qs_brand')
                ->field('scale,scaleTB,month')
                ->where($map)
                ->where('isDeleted',0)
                ->order('Id','desc')
                ->limit(0,6)
                ->select();
            //商家成交额指数变化趋势
            $info['turnover'] = Db::table('qs_structure')
                ->field('exponent'.$str.' as exponent,increase'.$str.' as increase,month')
                ->where('isDeleted',0)
                ->order('Id','desc')
                ->limit(0,6)
                ->select();
            //品牌影响力指数变化趋势
            $info['brand'] = Db::table('qs_brand')
                ->field('influence,influenceTB,month')
                ->where($map)
                ->where('isDeleted',0)
                ->order('Id','desc')
                ->limit(0,6)
                ->select();
            //不同影响力品牌成交额占比情况
            $info['proportion'] = Db::table('qs_brand')
                ->field('di,zhongdi,zhong,zhonggao,gao')
                ->where($map)
                ->where('isDeleted',0)
                ->order('Id','desc')
                ->find();

            $this->success('success',$info,0);
        } catch (Exception $e) {
            Log::error('查询失败'.$e->getMessage().'，行：'.$e->getLine());
            $this->error('查询失败',[],-1);
        }
    }

    /**
     * 右侧产销/商品数据
     * @ApiTitle    (右侧产销/商品数据)
     * @ApiSummary  (右侧产销/商品数据)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dsdsj/data/product)
     * @ApiParams   (name="type", type="integer", required=true, description="产业类型，1=山核桃，2=坚果，3=笋类制品")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="返回数据")
     * @ApiReturn   ({
    'code':'0',
    'msg':'提示信息'
    'data:'结果'
    })
     */
    public function product()
    {
        $type = request()->post('type/d',1);
        $map['type'] = $type;

        try {
            //竞对产销模式对比
            $info['compared'] = Db::table('qs_product')
                ->field('ben,you1,you2')
                ->where($map)
                ->where('isDeleted',0)
                ->order('Id','desc')
                ->find();
            //成交商品数量指数趋势
            $info['transaction'] = Db::table('qs_product')
                ->field('deal,dealTB,month')
                ->where($map)
                ->where('isDeleted',0)
                ->order('Id','desc')
                ->limit(0,6)
                ->select();

            $this->success('success',$info,0);
        } catch (Exception $e) {
            Log::error('查询失败'.$e->getMessage().'，行：'.$e->getLine());
            $this->error('查询失败',[],-1);
        }
    }

    /**
     * 右侧热销商品/潜力商品排行榜
     * @ApiTitle    (右侧热销商品/潜力商品排行榜)
     * @ApiSummary  (右侧热销商品/潜力商品排行榜)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dsdsj/data/ranking)
     * @ApiParams   (name="page", type="integer", required=true, description="页数，默认1")
     * @ApiParams   (name="page_size", type="integer", required=true, description="每页显示数量，默认5")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="返回数据")
     * @ApiReturn   ({
    'code':'0',
    'msg':'提示信息'
    'data:'结果'
    })
     */
    public function ranking()
    {
        $page = request()->post('page/d',1);
        $page = htmlentities($page,ENT_QUOTES);
        $page_size = request()->post('page_size/d',5);
        $page_size = htmlentities($page_size,ENT_QUOTES);
        $start = ($page - 1) * 5;

        try {
            //热销商品排行榜
            $sql = "SELECT top,goodName,hot,Price,changes FROM `qs_goodtop` WHERE type=1 AND `month`=(SELECT month FROM `qs_goodtop` ORDER BY Id DESC LIMIT 1) ORDER BY top ASC LIMIT $start,$page_size";
            $info['hot'] = Db::query($sql);

            //潜力商品排行榜
            $sql = "SELECT top,goodName,hot,Price,changes FROM `qs_goodtop` WHERE type=2 AND `month`=(SELECT month FROM `qs_goodtop` ORDER BY Id DESC LIMIT 1) ORDER BY top ASC LIMIT $start,$page_size";
            $info['potential'] = Db::query($sql);

            $this->success('success',$info,0);
        } catch (Exception $e) {
            Log::error('查询失败'.$e->getMessage().'，行：'.$e->getLine());
            $this->error('查询失败',[],-1);
        }
    }

    /**
     * 右侧消费者画像
     * @ApiTitle    (右侧消费者画像)
     * @ApiSummary  (右侧消费者画像)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/dsdsj/data/portrait)
     * @ApiParams   (name="type", type="integer", required=true, description="产业类型，1=山核桃，2=坚果，3=笋类制品")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="返回数据")
     * @ApiReturn   ({
    'code':'0',
    'msg':'提示信息'
    'data:'结果'
    })
     */
    public function portrait()
    {
        $type = request()->post('type/d',1);

        try {
            //消费能力分布
            $info['consume'] = Db::table('qs_consumption')
                ->field('di,zhongdi,zhong,zhonggao,gao')
                ->where('goodtype',$type)
                ->where('isDeleted',0)
                ->order('Id','desc')
                ->find();

            $param['type'] = $type;
            //左侧数据
            $sql = "SELECT flavor,val FROM `qs_portrait` WHERE type=1 AND goodtype=:type AND isDeleted=0 AND month=(SELECT month FROM `qs_portrait` ORDER BY Id DESC LIMIT 1) ORDER BY Id DESC";
            $info['classify_left'] = Db::query($sql,$param);
            //右侧数据
            $sql = "SELECT flavor,val FROM `qs_portrait` WHERE type=2 AND goodtype=:type AND isDeleted=0 AND month=(SELECT month FROM `qs_portrait` ORDER BY Id DESC LIMIT 1) ORDER BY Id DESC";
            $info['classify_right'] = Db::query($sql,$param);
            //消费者网购热搜
            $sql = "SELECT flavor,val FROM `qs_portrait` WHERE type=3 AND goodtype=:type AND isDeleted=0 AND month=(SELECT month FROM `qs_portrait` ORDER BY Id DESC LIMIT 1) ORDER BY Id DESC";
            $info['hot_search'] = Db::query($sql,$param);

            $this->success('success',$info,0);
        } catch (Exception $e) {
            Log::error('查询失败'.$e->getMessage().'，行：'.$e->getLine());
            $this->error('查询失败',[],-1);
        }
    }
}
