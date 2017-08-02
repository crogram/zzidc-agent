<?php
/**
 * Created by PhpStorm.
 * User: 123456
 * Date: 2017/1/18
 * Time: 17:25
 */
namespace Common\Respository;

use Common\Respository\BusinessRespository;
use Common\Data\GiantAPIParamsData as GiantAPIParams;

class ClouddbBusinessRespository extends BusinessRespository{
    /**
     * ----------------------------------------------
     * | 扩充查询字段，在连表的情况下有别名使用
     * | @author: guopeng
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    protected function fieldsExtra($fields){
        if (is_string($fields)){
            $fields = explode(',', $fields);
        }
        foreach ($fields as $k => $filed){
            if ($filed == 'product_name'){
                $fields[$k] = 'b.'.$filed;
            }elseif ($filed == 'login_name'){
                $fields[$k] = 'c.'.$filed;
            }else{
                $fields[$k] = $this->alias.'.'.$filed;
            }
        }
        return $fields;
    }

    /**
     * ----------------------------------------------
     * | 返回一个join数组
     * | 连表查询时，请实现此方法
     * | @author: Guopeng
     * | @param: variable
     * | @return type
     * ----------------------------------------------
     */
    protected function joinsExtra(){

        return [
            'left join '.$this->table_prefix.'product as b on '.$this->alias.'.product_id = b.id',
            'left join '.$this->table_prefix.'member as c on '.$this->alias.'.user_id = c.user_id',
        ];
    }


    /**
     * ----------------------------------------------
     * | 额外的扩充条件，这里要增加一项
     * | product_id值得条件
     * | @author: Guopeng
     * | @param: variable
     * | @return type
     * ----------------------------------------------
     */
    protected function whereExtra($where, $request){

        $_where = [];
        foreach ($where as $k => $v){
            if ($k == 'product_name') {
                $_where['b.product_name'] = $v;
            }elseif ($k == 'login_name'){
                $_where['c.login_name'] = $v;
            }else {
                $_where[$this->alias.'.'.$k] = $v;
            }
        }
        return $_where;
    }

    /**
     * ----------------------------------------------
     * | 业务统计方法，统计出各个分类的总数等
     * | @author: Guopeng
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function counter($field){
        $counter = [];

        /**
         * ---------------------------------------------------
         * | 总记录数
         * ---------------------------------------------------
         */
        $counter['total'] = $this->queryBuilder($field, []);
        /**
         * ---------------------------------------------------
         * | 成功业务
         * ---------------------------------------------------
         */
        $where['a.state'] =[ 'eq', 1 ];
        $counter['successful'] = $this->queryBuilder($field, $where);

        /**
         * ---------------------------------------------------
         * | 已删除业务
         * ---------------------------------------------------
         */
        $where['a.state'] =[ 'eq', 2 ];
        $counter['deleted'] = $this->queryBuilder($field, $where);

        /**
         * ---------------------------------------------------
         * | 已过期业务
         * ---------------------------------------------------
         */
        $where['a.state'] =[ 'eq', 3 ];
        $counter['expired'] = $this->queryBuilder($field, $where);

        /**
         * ---------------------------------------------------
         * | 失败业务
         * ---------------------------------------------------
         */
        $where['a.state'] =[ 'eq', 4 ];
        $counter['failed'] = $this->queryBuilder($field, $where);

        return $counter;
    }


    /**
     * ----------------------------------------------
     * | 同步云数据库的业务逻辑
     * | 方法内部已经对ptype进行了处理，会自动找出对应的ptype，不需要再传入了
     * | @author: Guopeng
     * | @param: $business_info  一条业务记录或者是业务表的id值  建议传入一条业务记录
     * | @return: type
     * ----------------------------------------------
     */
    public function ClouddbBusinessSynchronizing($business_info, $attr = 'api_bid'){
        $business_info_exists = "";
        //如果你传入的是id值那么就获取信息，如果你传入的是一条业务记录(数组),那么就不用再去取了
        if (is_numeric($business_info) && !is_array($business_info)){
            //先获取本地业务信息
            $business_info_exists =$this->model()->where([ $attr => [ 'eq',$business_info ] ])->find();

            //如果不存在这条记录，那么就写进去
            if (empty($business_info_exists)){
                list($business_info, $temp) = [ [], $business_info];
                $business_info[$attr] = $temp;
                $business_info['user_id'] = -1;
                $business_info['product_name'] = '未同步业务';
                $add_id = $this->model()->add($business_info);
                $business_info['id'] = $add_id;
            }else {
                $business_info = $business_info_exists;
            }
        }
        $business_info['business_id'] = $business_info[$attr];
        $id = $business_info['id'];
        //调用api同步业务信息
        $result = $this->syncBusinessFromZZIDC($business_info,GiantAPIParams::PTYPE_CLOUD_DATABASE);
        if(is_array($result) && !empty($result['json'])){
            //说明通用的单次同步业务逻辑未出错；
            //拿取api返回的信息
            $product_info = $result['product_info'];
            $bus_info = $result['json'] ['info'] ;
            //处理相应的信息，拼凑要更新到本地的数据
            // 业务信息
            $parms['create_time'] = $bus_info['base']['createTime']; // 创建时间
            $parms['overdue_time'] = $bus_info['base']['overTime']; // 到期时间
            $parms['buy_time'] = $bus_info['base']['gmqx'];//购买期限
            $parms['api_bid'] = $bus_info['base']['ywbh'];//业务编号
            $parms['state'] = $bus_info['base']['slzt'];//业务状态
            $parms['ywbs'] = $bus_info['base']['slmc'];//业务状态
            $parms['free_trial'] = $bus_info['base']['isTest'] == 1 ? 0:1;//业务状态
            $parms['wwdz'] = $bus_info['config']['atlas_wan1'];//外网ip
            $parms['nwdz'] = $bus_info['config']['atlas1vps'];//内网ip
            $parms['memory'] = $bus_info['config']['memory'];//内存
            $parms['disk'] = $bus_info['config']['disk'];//硬盘
            $parms['conn'] = $bus_info['config']['connections'];//最大链接数
            $parms['iops'] = $bus_info['config']['iops'];//IOPS
            $parms['area_code'] = 4001;//地区编号
            $parms['version'] = $bus_info['config']['version'];//版本类型
            $parms['product_name'] = '快云数据库';
            $parms['product_id'] = '306';
            $parms['user_id'] = !$business_info['user_id'] ? '-1': $business_info['user_id'];
            $parms['login_name'] = !$business_info['login_name'] ? '待转让会员': $business_info['login_name'];
            //这里更新的是clouddb业务表的信息
            $clouddb_model = $this->model();
            $clouddb_update_res = $clouddb_model->where([ 'id' => [ 'eq', $id ] ])->save($parms);
            if ($clouddb_update_res === false){
                if (empty($business_info_exists)){
                    //删除同步失败的新增业务
                    $where['user_id'] = -1;
                    $where['id'] = $id;
                    $where['product_name'] = '未同步业务';
                    $this->model()->where($where)->delete();
                }
                $this->model()->setError('服务器繁忙，同步失败，请重试');
                return false;
            }else {
                return true;
            }
        }else{
            if (empty($business_info_exists)){
                //删除同步失败的新增业务
                $where['user_id'] = -1;
                $where['id'] = $id;
                $where['product_name'] = '未同步业务';
                $this->model()->where($where)->delete();
            }
            //说明同步调用api出错了,将错误结果返回出去
            $this->model()->setError(business_code($result));
            return FALSE;
        }
    }

    /**
     * ----------------------------------------------
     * | 删除快云数据库的方法
     * | @author: Guopeng
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function eraseClouddbBusiness($id){
        return $this->erase( $this->firstOrFalse( [ 'id' => [ 'eq', $id] ]), GiantAPIParams::PTYPE_CLOUD_DATABASE);
    }
}