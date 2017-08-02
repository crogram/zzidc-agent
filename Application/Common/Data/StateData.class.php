<?php
namespace Common\Data;
/**
 * 状态类
 * @author Administrator
 *
 */
class StateData{
/**
 * 正常状态
 */
const SUCCESS = 1;
/**
 * 删除状态
 */
const DELETE = 2;
/**
 * 过期状态
 */
const OVERDUE = 3;
/**
 * 失败状态
 */
const FAILURE = 4;
/**
 * 待获取业务状态
 * 快云服务器获取开通进度
 */
const NOTGET = 5;
/**
 * 充值类型
 */
const RECHRAGE=0;
/**
 * 消费类型
 */
const CONSUM=1;
/**
 * 取款类型
 */
const WITHDRAW=2;
/**
 * 新增订单类型
 */
const NEW_ORDER=0;
/**
 * 增值订单类型
 */
const APP_ORDER=1;
/**
 * 续费订单类型
 */
const RENEWALS_ORDER=2;
/**
 * 变更方案订单类型
 */
const CHANGE_ORDER=3;
/**
 * 转正订单类型
 */
const ONFORMAL_ORDER=4;
/**
 * 失败订单状态
 */
const FAILURE_ORDER=0;
/**
 * 成功订单状态
 */
const SUCCESS_ORDER=1;
/**
 * 待处理订单状态
 */
const WAIT_ORDER=2;
/**
 * 处理中订单状态
 */
const HANDLE_ORDER=3;
/**
 * 审核中订单状态
 */
const EXAMINE_ORDER=4;
/**
 * 已删除订单状态
 */
const DELETE_ORDER=5;
/**
 * 已付款订单状态
 */
const PAYMENT_ORDER=6;
/**
 * 未完成
 */
const BDYM_ORDER=11;
/**
 * 已经绑定
 */
const BD_ORDER=12;
/**
 * 系统管理类型：管理员
 */
const SYS_ADMIN=0;
/**
 * 系统管理类型：站点信息
 */
const SYS_SITE=1;
/**
 * 系统管理类型：图片轮换
 */
const SYS_IMAGE=2;
/**
 * 系统管理类型：头部通知
 */
const SYS_HEAD=3;
/**
 * 系统管理类型：关于我们
 */
const SYS_ON_THE=4;
/**
 * 系统管理类型：支付方式
 */
const SYS_PAYMENT=5;
/**
 * 系统管理类型：域名广告
 */
const SYS_AD_DOMAIN=6;
/**
 * 系统管理类型：虚拟主机广告
 */
const SYS_AD_VIRTUALHOST=7;
/**
 * 系统管理类型：云计算广告
 */
const SYS_AD_CLOUD=8;
/**
 * 系统管理类型：SSL广告
 */
const SYS_AD_SSL=9;
/**
 * 系统管理类型：默认域名
 */
const SYS_DEFAULT_DOMAIN=10;
/**
 * 系统管理类型：热销产品
 */
const SYS_HOT_PRODUCT=11;
/**
 * 系统管理类型:网页关键字
 */
const SYS_KEY_WORDS=12;
/**
 * 系统管理类型:邮箱配置
 */
const SYS_KEY_MAIL=13;
/**
 * 系统管理类型：广告图
 */
const SYS_GGIMAGE=14;
/**
 * 运行状态（启用）
 */
const STATE_ON=1;
/**
 * 运行状态（未启用）
 */
const STATE_OFF=0;
/**
 * 购买状态
 */
const STATE_BUY=0;
/**
 * 续费状态
 */
const STATE_RENEWALS=1;

/**
 * 成功状态
 */
const STATE_ONLINE_TRAN_SUCCESS=1;
/**
 * 失败状态
 */
const STATE_ONLINE_TRAN_ERROR=0;
/**
 * 等待充值状态
 */
const STATE_ONLINE_TRAN_WAIT=2;

}