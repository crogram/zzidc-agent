<?php
namespace Frontend\Controller;

use Frontend\Controller\FrontendController;

class WhiteController extends FrontendController{

    public function _initialize()
    {
        parent::_initialize();
        $site_config = WebSiteConfig();
        if($site_config['white_list'] != 'yes'){
            $this->error("白名单未开启！");
        }
    }

    protected $captcha_type_white = 'frontend_white';
    
    /**
     * 白名单
     * @date: 2017年2月21日 上午11:12:48
     * @author: Lyubo
     * @param: $GLOBALS
     * @return:
     */
    public function whitelist(){
        $this->display("whiteList");
    }
    /**
     * 白名单查询
     * @date: 2017年2月21日 下午2:25:43
     * @author: Lyubo
     * @param: variable
     * @return:
     */
    public function getDomainWhiteList(){
        $parms = request();
        $result = WhiteList('select',$parms);
        if($result['code'] == '1'  || $result['code'] == '0'){
            $this->ajaxReturn($result);
        }elseif($result['code'] == '2'){
            $result['msg'] = 'ok';
            $this->ajaxReturn($result);
        }
    }
    /**
     * 添加白名单
     * @date: 2017年2月21日 下午5:46:48
     * @author: Lyubo
     * @param: variable
     * @return:
     */
    public function addDomainWhite(){
        $parms = request();
        $white_info = WhiteList('select',$parms);
        if($white_info['code'] == '2'){//域名不在景安白名单中
                $add_result = WhiteList('add',$parms);
                if($add_result['code'] == '1'){
                        $white = M('white');
                        $ins['domain'] = strtolower($parms['domain']);
                        $ins['ip_address'] = $parms['ymip'];
                        $ins['create_time'] = current_date();
                        $result_ins = $white->add($ins);
                        if(!$result_ins){
                            $msg = "白名单添加成功,数据表插入失败。请联系管理员";
                            $this->ajaxReturn($msg);
                        }else{
                            $this->ajaxReturn('ok');
                        }
                }elseif($add_result['code'] == '2'){
                        $this->ajaxReturn('添加白名单失败，请联系管理员');
                }elseif ($add_result['code'] == '0'){
                    $this->ajaxReturn($add_result['msg']);
                }
         }else{
             $this->ajaxReturn($white_info['msg']);
         }
    }
    /**
     * ajax验证验证码
     * @date: 2017年2月22日 上午9:57:47
     * @author: Lyubo
     * @param: $GLOBALS
     * @return:
     */
    public function checkCodeNum(){
        $parms = request();
        $request['captcha'] = $parms['codeNum'];
        $request['type'] = $this->captcha_type_white;
        $imagesAide = new \Common\Aide\ImagesAide();
        $captcha_res = $imagesAide->checkCaptcha($request['captcha'],$request['type']);
        if ($captcha_res) {
            $data['status'] = 'y';
            $this->ajaxReturn($data);
        }else{
            $data['status'] = 'n';
            $this->ajaxReturn($data);
        }
    }
}