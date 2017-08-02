<?php
namespace Backend\Model;
use Backend\Model\BackendModel;


/**
 * -------------------------------------------------------
 * | 管理员数据模型
 * | @author: duanbin
 * | @时间: 2016年10月21日 下午3:37:24
 * | @version: 1.0
 * -------------------------------------------------------
 */
class UsersModel extends BackendModel{

	protected $trueTableName = 'agent_users';

	
    // 自动验证
    protected $_validate=array(
        array('username','require','用户名必须',0,'',3), // 验证字段必填
    );

    // 自动完成
    protected $_auto=array(
        //array('password','md5Password',1,'function') , // 对password字段在新增的时候使md5函数处理
        array('register_time','time',1,'function'), // 对date字段在新增的时候写入当前时间戳
    );

    
    /**
     * ----------------------------------------------
     * | 加密密码
     * | @时间: 2016年10月8日 下午4:36:29
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function md5Password($password){
    	return md5( $password.C('APP_KEY') );
    }
    
    /**
     * 添加用户
     */
    public function addData($data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            return false;
        }else{
            // 验证通过
            $result=$this->add($data);
            return $result;
        }
    }

    /**
     * 修改用户
     */
    public function editData($map,$data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            return false;
        }else{
            // 验证通过
            $result=$this
                ->where(array($map))
                ->save($data);
            return $result;
        }
    }

    /**
     * 删除数据
     * @param   array   $map    where语句数组形式
     * @return  boolean         操作是否成功
     */
    public function deleteData($map){
        //die('禁止删除用户');
        if($map['id'] != '1'){
            $result = $this->where($map)->delete();
            return $result;
        }else{
            return false;
        }
    }


    /**
     * ----------------------------------------------
     * | 修改管理员密码
     * | @时间: 2016年11月14日 下午3:34:21
     * | @author: duanbin
     * | @param: variable
     * | @return: type
     * ----------------------------------------------
     */
    public function updatePwd($data) {
    	
    	if (empty($data['old_pwd'])){
    		$this->error = '原密码不能为空哦！';
    		return false;
    	}
    	if (empty($data['new_pwd'])){
    		$this->error = '新密码不能为空哦！';
    		return false;
    	}
    	if (empty($data['confirm_pwd'])){
    		$this->error = '确认密码不能为空哦！';
    		return false;
    	}
    	
    	//新密码和确认密码是否一致
		if ($data['new_pwd'] != $data['confirm_pwd']){
			$this->error = '新密码和确认密码不一样哦！您可能填错了一个！';
			return false;
		}
		//查看新密码是否足够安全（数字大小写字母 6-20位）
		if (!passwordIsStrong($data['new_pwd'])){
			$this->error = '新密码必须是数字和大小写字母 8-20位哦！';
			return false;
		}
		
		//判断输入的原密码是否正确
		$admin_id = session('admin')['id'];
		$current_admin = $this->find( $admin_id );
		if (md5Password($data['old_pwd']) != $current_admin['password']){
			$this->error = '原密码错误！';
			return false;
		}
		
		//修改新密码
		$where['id'] = [ 'eq', $admin_id ];
		$res = $this->where($where)->setField('password', md5Password($data['new_pwd']));
		if ($res === false){
			$this->error = '操作失败了，在试一次吧！';
			return false;
		}else {
			return true;
		}

    }

}
