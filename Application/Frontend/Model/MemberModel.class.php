<?php
namespace Frontend\Model;
use Think\Model;

class MemberModel extends Model{
    protected $trueTableName = 'agent_member';
    protected $_validate = array();
	
	public function conver_par(){
	    $date =request();
	    $where["user_id"] = array("eq",session("user_id"));
	    return $where;
	}
    /**
    * 获取会员个人信息
    * @date: 2016年10月25日 上午11:17:13
    * @author: Lyubo
    * @param: $where
    * @return:
    */
    public function get_member_info($where){
        return  $this->where($where)->find();
    }
    /**
    * 修改会员密码
    * @date: 2016年11月1日 下午5:01:54
    * @author: Lyubo
    * @return:
    */
    public function member_modify_pass($data){
            $login_name = session("login_name");
            $old_pass = clearXSS( I("post.old_pass") );//获取旧密码经过request方法转换成数组
            $user_pass = clearXSS( I("post.user_pass") );
            $user_pass2 = clearXSS( I("post.user_pass2") );
            
            /**
             * ---------------------------------------------------
             * | 修改密码的逻辑
             * | @时间: 2016年12月14日 下午4:55:50
             * ---------------------------------------------------
             */
            if (!passwordIsStrong($user_pass)){
            	$this->error = '密码要是在8-20位的数字和大写小写字母组成！';
            	return false;
            }
            
            if ($user_pass != $user_pass2){
            	$this->error = '确认密码和新密码不一致！';
            	return false;
            }
            
            $map["login_name"] = array("eq" ,$login_name);
            $old_pass= pwd($login_name,md5($old_pass));
            $info = $this->where($map)->find();//先用旧密码查询是否存在会员
            if ($old_pass != $info['user_pass']){
            	$this->error = '原密码错误';
            	return false;
            }
            $upd_info["user_pass"] = pwd($login_name , md5($user_pass));
            $where["login_name"] = array("eq" , $login_name);
            //会员存在进行修改密码
            $res = $this->where($where)->save($upd_info);
            if ($res === false){
				$this->error = '服务器繁忙，请重试！';
				return false;
            }else {
	            return  $res;
            }
    }
    /**
    * 修改会员信息
    * @date: 2016年11月2日 上午11:21:53
    * @author: Lyubo
    * @param: $where
    * @return: boolean
    */
    public function member_modify_info($where){
        $this->_validate =[
            ['user_name','require','请填写用户名！'],
            ["user_name","1,10","姓名最大长度10！",2,"length"],
            ['user_mail','require','请填写邮箱！'],
            ['user_mail','email','请填写真实有效的邮箱！'],
            ['user_code','require','请填写身份证号码！'],
            ['user_mobile','require','请填写手机号码！'],
            ['user_mobile','/^1[3|4|5|7|8][0-9]\d{8}$/','请正确填写您的手机号码！',2,"regex"],
            ["user_mobile","11","手机号码不能小于11个数字！",2,"length"],
        ];
        $member_info = request(true);
        if($this->create())
        {
            if(!checkIdCard($member_info["user_code"]))
            {
                $this->error = "请正确填写您的身份证号号码!";
                return false;
            }
            return $result = $this->where($where)->save($member_info);
        }
        else
        {
            return false;
        }
    }

    /**
     * ----------------------------------------------
     * | 注册的业务逻辑
     * | @时间: 2016年12月7日 上午10:57:56
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function regist($request){
    	
    	//验证数据
//    	if ($request['agree'] == false){
//    		$this->error = '您尚未同意注册协议';
//    		return  false;
//    	}
    	
    	$imagesAide = new \Common\Aide\ImagesAide();
    	$captcha_res = $imagesAide->checkCaptcha($request['captcha'], $request['type']);
    	if (!$captcha_res){
    		$this->error = '验证码不对哦';
    		return false;
    	}
    	
    	if (!filter_var($request['user_mail'], FILTER_VALIDATE_EMAIL)){
    		$this->error = '邮箱格式不正确哦！';
    		return  false;
    	}
    	
    	if ( !empty( $request['user_qq'] ) ){
    	    if ( !is_numeric($request['user_qq']) ){
    	        $this->error = '请填写正确的qq号码';
    	        return false;
    	    }
    	}
    	
       if (!empty( $request['user_mobile'] )){
    		if ( !is_tel($request['user_mobile']) ){
    			$this->error = '手机号码格式不对';
    			return false;
    		}
		}
    	
    	//查看是否有人注册过了。。。
    	$exists = $this->where([ 'login_name' => [ 'eq', $request['username'] ] ])->find();
    	if (!empty($exists)){
    		$this->error = '该用户已经存在啦，换一个试试吧 :)';
    		return  false;
    	}
    	
    	if (!passwordIsStrong($request['password'])){
    		$this->error = '密码是8到20位数字和大小写字母';
    		return  false;
    	}
    	
    	if ($request['password'] != $request['confirm_password']){
    		$this->error = '密码和确认密码不一致';
    		return  false;
    	}
    	//添加到数据库
    	$data['user_state'] = 1;
    	$data['type'] = 1;
    	$data['login_name'] = $request['username'];
    	$data['user_mail'] = $request['user_mail'];
    	$data['user_name'] = $request['username'];
    	$data['user_pass'] = pwd($request['username'], md5($request['password']));
    	$data['create_time'] = date('Y-m-d H:i:s');
    	$data['user_code'] = '';
    	$data['user_province'] = '';
    	$data['user_city'] = '';
    	$data['user_address'] = '';
    	$data['user_mobile'] = $request['user_mobile'] ? $request['user_mobile'] : '';
    	$data['user_postal'] = '';
    	$data['user_qq'] = $request['user_qq'];
    	$res = $this->add($data);
    	if ($res === false){
    		$this->error = '服务器繁忙，请稍后再试！';
    		return false;
    	}else {
    		//这里生成一条member_account记录
    		$m_member_account = M('member_account');
    		$m_member_account->add([
    				'user_id' => $res,
    				'state' => 1,
    				'balance' => 0,
    				'purchases' => 0,
    				'update_time' => date('Y-m-d H:i:s'),
    		]);
    		return [
    				'user_id' => $res,
    				'user_name' => $data['user_name'],
    				'login_name' => $data['login_name'],
    		];
    	}
    	
    }
	public function ywcount($user_id)
	{
		$where["bs.user_id"] = $user_id;
		$vps_count = $this->query_count("vps_business",$where);
		$fast_vps_count = $this->query_count("fast_vps_business",$where);
        $cloudserver_count = $this->query_count("cloudserver_business",$where);
        $cloudspace_count = $this->query_count("cloudspace_business",$where);
        $clouddb_count = $this->query_count("clouddb_business",$where);
        $cloudserver_business_ip_count = $this->query_count("cloudserver_business_ip",$where);
        $ssl_count = $this->query_count("ssl_business",$where);
        $domain_count = $this->query_count("domain_business",$where);
        $where["bs.virtual_type"] = array("in","0,4");
        $gn_virtualhost_count = $this->query_count("virtualhost_business",$where);
        $where["bs.virtual_type"] = 1;
        $hk_virtualhost_count = $this->query_count("virtualhost_business",$where);
        $where["bs.virtual_type"] = 2;
        $usa_virtualhost_count = $this->query_count("virtualhost_business",$where);
        $where["bs.virtual_type"] = 3;
        $yun_virtualhost_count = $this->query_count("virtualhost_business",$where);
        $count["ykj"] = $cloudspace_count;
		$count["vps"] = $vps_count;
		$count["fvps"] = $fast_vps_count;
        $count["ffwq"] = $cloudserver_count;
        $count["fsjk"] = $clouddb_count;
        $count["fip"] = $cloudserver_business_ip_count;
        $count["gnzj"] = $gn_virtualhost_count;
        $count["hkzj"] = $hk_virtualhost_count;
        $count["usazj"] = $usa_virtualhost_count;
        $count["yunzj"] = $yun_virtualhost_count;
        $count["ssl"] = $ssl_count;
        $count["ym"] = $domain_count;
        $count["dxf"] = $this->get_overdue_business_count($user_id);
		return $count;
	}

    /**
     * ----------------------------------------------
     * | 获取临近$days的所有业务
     * | @时间: 2016年11月24日 上午11:33:55
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    protected function get_overdue_business_count($user_id,$days = 30)
    {
        $where["bs.user_id"] = $user_id;
        $where['_string'] = 'bs.overdue_time between now() and date_add(now(), INTERVAL '.$days.' day)';
        $cv = $this->query_count("cloudvirtual_business",$where);
        $vh = $this->query_count("virtualhost_business",$where);
        $fvps = $this->query_count("fast_vps_business",$where);
        $vps = $this->query_count("vps_business",$where);
        $cs = $this->query_count("cloudserver_business",$where);
        $cd = $this->query_count("clouddb_business",$where);
        $ip = $this->query_count("cloudserver_business_ip",$where);
        $cp = $this->query_count("cloudspace_business",$where);
        $sb = $this->query_count("ssl_business",$where);
        $db = $this->query_count("domain_business",$where);
        $all_bs_count = $cv + $vh + $fvps + $vps + $cs + $ip + $cp + $cd + $sb + $db;
        return $all_bs_count;
    }

    protected function query_count($table,$where){
        $query = M($table);
        $query_count = $query->alias(' bs ')
            ->join('left join '.C('DB_PREFIX').'product as p on p.id = bs.product_id')
            ->join('inner join '.C('DB_PREFIX').'product_type as pt on p.product_type_id= pt.id ')
            ->where($where)->count();//返回带条件的总记录数
        return $query_count;
    }

    public function get_member_money()
    {
        $where["m.user_id"] = array("eq",session("user_id"));
        $member_info = $this->alias('m')
            ->field("m.user_id,m.user_name,m.login_name,ma.balance,ma.purchases")
            ->join('left join '.C('DB_PREFIX').'member_account as ma on ma.user_id = m.user_id')
            ->where($where)->find();
        $arr = explode(".",$member_info["balance"]);
        if(!$arr[0]){
            $arr[0] = 0;}
        if(!$arr[1]){
            $arr[1] = "00";}
        $member_info["balance"] = $arr;
        $arr = explode(".",$member_info["purchases"]);
        if(!$arr[0]){
            $arr[0] = 0;}
        if(!$arr[1]){
            $arr[1] = "00";}
        $member_info["purchases"] = $arr;
        return $member_info;
    }
    public function checkLogin($request){
        if($request['check_type'] == '1'){
            //查看是否有人注册过了。。。
            $exists = $this->where([ 'login_name' => [ 'eq', $request['username'] ] ])->find();
            if (!empty($exists)){
                $this->error = '该用户已经存在啦，换一个试试吧 :)';
                return  false;
            }
            return  true;
        }elseif ($request['check_type'] == '2'){
            $imagesAide = new \Common\Aide\ImagesAide();
            $captcha_res = $imagesAide->checkCaptcha($request['captcha'], $request['type']);
            if (!$captcha_res){
                $this->error = '验证码不对哦';
                return false;
            }
            return true;
        }
    }
}