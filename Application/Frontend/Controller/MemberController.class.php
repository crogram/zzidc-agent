<?php
namespace Frontend\Controller;
use Frontend\Controller\FrontendController;
/**
* 会员中心
* @date: 2016年10月25日 上午10:23:41
* @author: Lyubo
*/
class MemberController extends FrontendController
{

    /**
     * 判断用户是否登陆
     * @date: 2016年10月25日 下午3:25:56
     * 
     * @author : Lyubo
     */
    public function _initialize()
    {
        if (! session('?user_id')) {
            redirect(U('/Frontend/Login/login', [], false), 0, '');
            die();
        }
    }

    public function index()
    {
        $member = new \Frontend\Model\MemberModel();
        $member_info = $member->get_member_money();
        $article = new \Frontend\Model\ArticleModel();
        $notices = $article->home_news(array("type"=>3));
        $this->assign([
            "notices"=>$notices,
            "member_info"=>$member_info
        ]);
        $this->display();
    }
    /**
    * 获取会员信息
    * @date: 2016年11月1日 下午3:22:12
    * @author: Lyubo
    * @param: $info
    * @return:
    */
    public function memberinfo(){
        $member = new \Frontend\Model\MemberModel();
        $where = $member->conver_par();
        $member_info = $member->get_member_info($where);
        if(IS_POST){
            $result = $member->member_modify_info($where);
            if($result !== false){
                session('user_name',I('post.user_name'));
                $this->success("修改信息成功","/frontend/member/memberinfo");
            }else{
                $msg = $member->getError();
                $msg = "修改信息失败-".$msg;
                $this->error($msg,"/frontend/member/memberinfo");
            }
        }else {
            $this->assign([
                "member_info"=>$member_info
            ]);
            $this->display();
        }
    }
    /**
    * 安全中心
    * @date: 2016年11月1日 下午3:45:59
    * @author: Lyubo
    * @return:
    */
    public function membersafe(){
        $member = new \Frontend\Model\MemberModel();
        $where = $member->conver_par();
        $member_info = $member->get_member_info($where);
        $this->assign([
            "member_info"=>$member_info
        ]);
        $this->display();
    }
    
    /**
    * 修改会员密码（原密码修改）
    * @date: 2016年11月1日 下午4:38:55
    * @author: Lyubo
    * @return: boolean
    */
    public function memeber_modify()
    {
        $member = new \Frontend\Model\MemberModel();
        $data = $member->conver_par();
        $result = $member->member_modify_pass($data);
        if($result !== false){
            session('user_id',null);
            session('user_name',null);
            session('login_name',null);
            $this->ajaxReturn(
                [
                    'code' => 1,
                    'url'=>U('frontend/login/login','',false),
                    'msg' =>"修改密码成功,请重新登陆",
                ], 'json');
        }else{
            $this->ajaxReturn(
                [
                    'code' => -1,
                    'msg' =>$member->getError(),
                ], 'json');
        }
    }
    /**
     * 业务数获取
     * @date: 2016年12月8日 上午10:09:49
     * @author: Guopeng
     */
    public function state_count(){
        $user_id = intval(session("user_id"));
        $member = new \Frontend\Model\MemberModel();
        $sum = $member->ywcount($user_id);
        $date["status"] = 'ok';
        $date["sum"] = $sum;
        return $this->ajaxReturn($date);
    }
}