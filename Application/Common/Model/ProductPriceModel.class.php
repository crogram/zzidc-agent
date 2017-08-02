<?php
namespace Common\Model;
use Common\Model\BaseModel;

use \Common\Aide\AgentAide as Agent;
use \Common\Data\GiantAPIParamsData as GiantAPIParams;

/**
 * -------------------------------------------------------
 * | 产品模型类
 * | @author: duanbin
 * | @时间: 2016年10月9日 下午3:34:41
 * | @version: 1.0
 * -------------------------------------------------------
 */
class ProductPriceModel extends BaseModel{
	
	//关联的表名

	
	
	
	/**
	 * ----------------------------------------------
	 * | 批量同步产品价格(目前只同步虚拟主机类的)
	 * | @时间: 2016年11月22日 下午3:21:36
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function priceBacthSynchronizer($ptype, $type = 1){
		
		//验证参数合法性
		$m_product_type = new \Common\Model\ProductTypeModel();
		$ptypes = $m_product_type->getCanBatchSyncPriceProductType();

		if (!key_exists($ptype, $ptypes)){
			$this->error = '参数错误';
			return false;
		}

		if (!in_array($type, [ 1, 2 ])){
			$this->error = '参数错误';
			return false;
		}
		/**
		 * ---------------------------------------------------
		 * | 同步产品类型的价格
		 * | @时间: 2017年1月10日 下午3:48:46
		 * ---------------------------------------------------
		 */
		if ($ptype == GiantAPIParams::PTYPE_DOMAIN){
		    //同步域名类
			return $this->syncDomainPrice($ptype, $type);
		} else if($ptype == GiantAPIParams::PTYPE_FAST_CLOUDVPS){
		    //同步快云VPS类
		    return $this->syncFastvpsPrice($ptype,$type);
		}else if($ptype == GiantAPIParams::PTYPE_VPS){
		    //同步VPS类
		    return $this->syncVpsPrice($ptype,$type);
		}else {
			/**
			 * ---------------------------------------------------
			 * | 同步主机类的价格
			 * | @时间: 2017年1月10日 下午3:54:26
			 * ---------------------------------------------------
			 */
			return $this->syncHostPrice($ptype, $type);
		}

	}
	/**
	* 同步VPS类型产品价格
	* @date: 2017年4月20日 下午4:02:55
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
	public function syncVpsPrice($ptype, $type = 1){
	    $m_product_type = new \Common\Model\ProductTypeModel();
	    //这里获取ptype对应的id值，后面再查商品时会用到；
	    //$ptype_id = $m_product_type->getProductIdByApiType($ptype);
	    //因为VPS是顶级分类前台没有显示手动赋值
	    $ptype_id = '1';
	    $tid = '35';
	    $log_message = "ptype:" . GiantAPIParams::PTYPE_SELF . '||tid='.$tid.'||pname = '.$ptype;
	    $result = '';
	    try {
	        $agent = new Agent();
	        $transaction = $agent->servant;
	        $result = $transaction->syncprice( GiantAPIParams::PTYPE_SELF, $tid ,'',$ptype);
	        // 记录日志
	        api_log ( "-1", $log_message, $result, '同步产品价格，产品名称：[' .$ptype . ']' );
	        // 解析JSON
	        $json = json_decode ( $result, true );
	        //p($json);
	    } catch ( \Exception $e ) {
	        api_log ( "-1", $log_message, $result, '同步产品价格，产品名称：[' .$ptype . ']--'.$e->getMessage() );
	        $this->error = business_code(-9);
	        return false;
	    }
	    //处理异常信息
	    if ($json ['code'] != 0) {
	        $this->error = business_code($json ['code']);
	        return false;
	    }
	    $bus_info = $json ['info'] ;
	    if (null == $bus_info) {
	        $this->error = business_code(-10);
	        return false;
	    }
	    //处理返回结果
	    $common_product = new \Common\Model\ProductModel();
	    $common_product_price = new \Common\Model\ProductPriceModel();
	    $ok = $err = $skip = 0;
	    foreach ($bus_info as $index => $value){
	        foreach ($value as $key=>$val){
	            //循环地区编号
	            foreach ($val as $k=>$v){
	                //判断api_name是否是增值产品
	                if(strpos($index, 'zengzhi.yingpan') !== false || strpos($index, 'zengzhi.ip') !== false || strpos($index, 'zengzhi.neicun') !== false){
	                    //vps增值api_name有点特殊
	                    $index = 'temai.'.$index;
	                    unset($val['4003']);
	                    $get_product_id_where = [
	                        "product_type_id"=>[ 'eq', $ptype_id ],
	                        "api_name" => [ 'eq', $index ],
	                        "area_code"=>['eq',$k],
	                    ];
	                    $product_info = $common_product->field('id,api_name')->where($get_product_id_where)->find();
	                    //如果没有改产品信息，那么就跳过
	                    if (empty($product_info)){
	                        $skip++;
	                        continue;
	                    }
	                }elseif(strpos($index, 'zengzhi.yingpan') === false || strpos($index, 'zengzhi.ip') === false || strpos($index, 'zengzhi.neicun') === false){
	                    foreach ($val as $y=>$l){
	                        //拿到产品id
	                        if($y =="dqbh"){
	                            $get_product_id_where = [
	                                "product_type_id"=>[ 'eq', $ptype_id ],
	                                "api_name" => [ 'eq', $index ],
	                                "area_code"=>['eq',$l],
	                            ];
	                        }
	                        $product_info = $common_product->field('id,api_name')->where($get_product_id_where)->find();
	                        //如果没有改产品信息，那么就跳过
	                        if (empty($product_info)){
	                            $skip++;
	                            continue;
	                        }
	                    }
	                }
	                //生成更新条件，去除所用的产品id
	                $save_where['product_id'] = [ 'eq', $product_info['id'] ];
	                //准备更新产品的价格
	                if($product_info['api_name'] == $index){
	                    foreach ($val as $ke => $va) {
	                        if(strpos($index, 'zengzhi.yingpan') !== false || strpos($index, 'zengzhi.ip') !== false || strpos($index, 'zengzhi.neicun') !== false){
	                            $month  = '1';
	                            $giant_price = $va;
	                        }else{
	                            if($ke != 'dqbh'){
	                                $month = $ke;
	                                $giant_price = $va;
	                            }
	                        }
	                        $save_where['month'] = [ 'eq', $month ];
	                        //更新的数据
	                        $save_data['giant_price'] = $giant_price;
	                        //如果$type==2，说明是既要更新景安价格，又要更新销售价格
	                        if ($type == 2){
	                            $save_data['product_price'] = $giant_price;
	                        }
	                        $update_res = $common_product_price->where($save_where)->save($save_data);
	                        $update_res === false ? $err++: $ok++;
	                    }
	                }
	    
	    
	            }
	        }//循环地区编号
	    }
	    //TODO 次数拿的不正确，价格修改完成
	    if ( ($ok+$err-$skip) <= 0 ){
	        $this->error = '本次没有需要同步价格的产品啦~';
	    }else {
	        $this->error = '本次共同步成功'.$ok.'个价格，同步失败'.$err.'个价格';
	    }
	    return true;
	}
	/**
	* 同步快云VPS类型产品价格
	* @date: 2017年4月19日 下午5:12:24
	* @author: Lyubo
	* @param: variable
	* @return:
	*/
	public function syncFastvpsPrice($ptype, $type = 1){
	    $m_product_type = new \Common\Model\ProductTypeModel();
	    //这里获取ptype对应的id值，后面再查商品时会用到；
	    $ptype_id = $m_product_type->getProductIdByApiType($ptype);
	    
	    $tid = '35';
	    $log_message = "ptype:" . GiantAPIParams::PTYPE_SELF . '||tid='.$tid.'||pname = '.$ptype;
	    $result = '';
	    try {
	        $agent = new Agent();
	        $transaction = $agent->servant;
	        $result = $transaction->syncprice( GiantAPIParams::PTYPE_SELF, $tid ,'',$ptype);
	        // 记录日志
	        api_log ( "-1", $log_message, $result, '同步产品价格，产品名称：[' .$ptype . ']' );
	        // 解析JSON
	        $json = json_decode ( $result, true );
	        //p($json);
	    } catch ( \Exception $e ) {
	        api_log ( "-1", $log_message, $result, '同步产品价格，产品名称：[' .$ptype . ']--'.$e->getMessage() );
	        $this->error = business_code(-9);
	        return false;
	    }
	    //处理异常信息
	    if ($json ['code'] != 0) {
	        $this->error = business_code($json ['code']);
	        return false;
	    }
	    $bus_info = $json ['info'] ;
	    if (null == $bus_info) {
	        $this->error = business_code(-10);
	        return false;
	    }
	    //处理返回结果
	    $common_product = new \Common\Model\ProductModel();
	    $common_product_price = new \Common\Model\ProductPriceModel();
	    $ok = $err = $skip = 0;
	    foreach ($bus_info as $index => $value){
	        foreach ($value as $key=>$val){
	            //循环地区编号
	            foreach ($val as $k=>$v){
	                //判断api_name是否是增值产品
    	            if(strpos($index, 'zengzhi.yingpan') !== false || strpos($index, 'zengzhi.ip') !== false || strpos($index, 'zengzhi.neicun') !== false){
    	                unset($val['4002']);
                    	                //拿到产品id
                    	                if($k == '4003'){
                    	                    $ptype_id = '14';
                    	                }
                    	                $get_product_id_where = [
                    	                    "product_type_id"=>[ 'eq', $ptype_id ],
                    	                    "api_name" => [ 'eq', $index ],
                    	                    "area_code"=>['eq',$k],
                    	                ];
                    	                $product_info = $common_product->field('id,api_name')->where($get_product_id_where)->find();
                    	                //如果没有改产品信息，那么就跳过
                    	                if (empty($product_info)){
                    	                    $skip++;
                    	                    continue;
                    	                }
                  }elseif(strpos($index, 'zengzhi.yingpan') === false || strpos($index, 'zengzhi.ip') === false || strpos($index, 'zengzhi.neicun') === false){
                                      foreach ($val as $y=>$l){
                                          //拿到产品id
                                          if($y =="dqbh"){
                                              if($l == '4003'){
                                                  $ptype_id = '14';
                                              }elseif($l == '4001'){
                                                  $ptype_id = '13';
                                              }
                                              $get_product_id_where = [
                                                  "product_type_id"=>[ 'eq', $ptype_id ],
                                                  "api_name" => [ 'eq', $index ],
                                                  "area_code"=>['eq',$l],
                                              ];
                                          }
                                          $product_info = $common_product->field('id,api_name')->where($get_product_id_where)->find();
                                          //如果没有改产品信息，那么就跳过
                                          if (empty($product_info)){
                                              $skip++;
                                              continue;
                                          }
                                     }
                  }
                    	               //生成更新条件，去除所用的产品id
                    	               $save_where['product_id'] = [ 'eq', $product_info['id'] ];
                    	               //准备更新产品的价格
                    	               if($product_info['api_name'] == $index){
                        	                       foreach ($val as $ke => $va) {
                        	                           if(strpos($index, 'zengzhi.yingpan') !== false || strpos($index, 'zengzhi.ip') !== false || strpos($index, 'zengzhi.neicun') !== false){
                        	                               $month  = '1';
                        	                               $giant_price = $va;
                        	                           }else{
                        	                               if($ke != 'dqbh'){
                        	                                   $month = $ke;
                        	                                   $giant_price = $va;
                        	                               }
                        	                           }
                        	                           $save_where['month'] = [ 'eq', $month ];
                        	                           //更新的数据
                        	                           $save_data['giant_price'] = $giant_price;
                        	                           //如果$type==2，说明是既要更新景安价格，又要更新销售价格
                        	                           if ($type == 2){
                        	                               $save_data['product_price'] = $giant_price;
                        	                           }
                        	                           $update_res = $common_product_price->where($save_where)->save($save_data);
                        	                           $update_res === false ? $err++: $ok++;
                        	                       }
                    	               }
                	           
    	           
    	            }
	             }//循环地区编号
	    }
	    //TODO 次数拿的不正确，价格修改完成
	    if ( ($ok+$err-$skip) <= 0 ){
	        $this->error = '本次没有需要同步价格的产品啦~';
	    }else {
	        $this->error = '本次共同步成功'.$ok.'个价格，同步失败'.$err.'个价格';
	    }
	    return true;
	}
	/**
	 * ----------------------------------------------
	 * | 同步主机类产品的价格
	 * | @时间: 2017年1月10日 下午3:56:27
	 * | @author: duanbin
	 * | @param: variable
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function syncHostPrice($ptype, $type = 1){
		$m_product_type = new \Common\Model\ProductTypeModel();
		//这里获取ptype对应的id值，后面再查商品时会用到；
		$ptype_id = $m_product_type->getProductIdByApiType($ptype);

		$tid = '35';
		$log_message = "ptype:" . $ptype . '||tid='.$tid;
		$result = '';
		try {
			$agent = new Agent();
			$transaction = $agent->servant;
			$result = $transaction->syncprice( $ptype, $tid );
			// 记录日志
			api_log ( "-1", $log_message, $result, '同步产品价格，产品名称：[' .$ptype . ']' );
			// 解析JSON
			$json = json_decode ( $result, true );
			//p($json);
		} catch ( \Exception $e ) {
			api_log ( "-1", $log_message, $result, '同步产品价格，产品名称：[' .$ptype . ']--'.$e->getMessage() );
			$this->error = business_code(-9);
			return false;
		}
		//处理异常信息
		if ($json ['code'] != 0) {
			$this->error = business_code($json ['code']);
			return false;
		}
		$bus_info = $json ['info'] ;
		if (null == $bus_info) {
			$this->error = business_code(-10);
			return false;
		}
		
		//处理返回结果
		$common_product = new \Common\Model\ProductModel();
		$common_product_price = new \Common\Model\ProductPriceModel();
		$ok = $err = $skip = 0;
		foreach ($bus_info as $index => $value){
			foreach ($value as $key=>$val){
				//拿到产品id
				$get_product_id_where = [
						"product_type_id"=>[ 'eq', $ptype_id ],
						"api_name" => [ 'eq', $key ],
				];
				$product_info = $common_product->field('id,api_name')->where($get_product_id_where)->find();
				//如果没有改产品信息，那么就跳过
				if (empty($product_info)){
					$skip++;
					continue;
				}
				//生成更新条件，去除所用的产品id
				$save_where['product_id'] = [ 'eq', $product_info['id'] ];
				//准备更新产品的价格
				if($product_info['api_name'] == $key){
					//这里返回的产品的价格可能是多个，结构有些蛋疼。。。额外处理 下
					foreach ($val as $k=>$v){
						//如果是多个价格
						if (count($val) > 1) {
							foreach ($val as $ke => $va) {
								//$va====>   [ $month => $giant_price ]；一个只有一个键值对的数组
								list($month,$giant_price) = each($va);
								$save_where['month'] = [ 'eq', $month ];
								//更新的数据
								$save_data['giant_price'] = $giant_price;
								//如果$type==2，说明是既要更新景安价格，又要更新销售价格
								if ($type == 2){
									$save_data['product_price'] = $giant_price;
								}
								$update_res = $common_product_price->where($save_where)->save($save_data);
								$update_res === false ? $err++: $ok++;
							}
							//一个价格
						} else {
							$save_where['month'] = [ 'eq', $k ];
							//更新的数据
							$save_data['giant_price'] = $v;
							//如果$type==2，说明是既要更新景安价格，又要更新销售价格
							if ($type == 2){
								$save_data['product_price'] = $v;
							}
							$update_res = $common_product_price->where($save_where)->save($save_data);
							$update_res === false ? $err++: $ok++;
						}
		
					}
				}
			}
				
		}
		if ( ($ok+$err-$skip) <= 0 ){
			$this->error = '本次没有需要同步价格的产品啦~';
		}else {
			$this->error = '本次共同步成功'.$ok.'个价格，同步失败'.$err.'个价格';
		}
		return true;
	}
	
	/**
	 * ----------------------------------------------
	 * | 同步域名类产品的价格
	 * | @时间: 2017年1月10日 下午3:56:53
	 * | @author: duanbin
	 * | @param: $GLOBALS
	 * | @return: type
	 * ----------------------------------------------
	 */
	public function syncDomainPrice($ptype, $type = 1){
		$m_product_type = new \Common\Model\ProductTypeModel();
		//这里获取ptype对应的id值，后面再查商品时会用到；
		$ptype_id = $m_product_type->getProductIdByApiType($ptype);

		$tid = '35';
		$log_message = "ptype:" . $ptype . '||tid='.$tid;
		$result = [];
		$extra_res = [];
		try {
			$agent = new Agent();
			$transaction = $agent->servant;
			$extras = [
				'9-0' => [ 'cplb' => 'endomain', 'operation_type' => 0, 'pname' => 'domain.price' ],	// $en_buy_domain
				'10-0' => [ 'cplb' => 'cndomain', 'operation_type' => 0, 'pname' => 'domain.price' ], 	// $cn_buy_domain
				'9-1' => [ 'cplb' => 'endomain', 'operation_type' => 1, 'pname' => 'domain.price' ],	// $en_renew_domain
				'10-1' => [ 'cplb' => 'cndomain', 'operation_type' => 1, 'pname' => 'domain.price' ],	// $cn_renew_domain	
			];
			foreach ($extras as $k => $extra){
				$extra_res = $transaction->syncprice( $ptype, $tid, $extra );
				api_log ( "-1", $log_message.'--cplb:'.$extra['cplb'].'--czlx:'.$extra['czlx'].'--pname:'.$extra['pname'], $extra_res, '同步产品价格，产品名称：[' .$ptype . ']' );
				$extra_res = json_decode ( $extra_res, true );
				// 处理异常信息
				if ($extra_res ['code'] != 0){
					$this->error = business_code($extra_res ['code']);
					return false;
				}else {
					$result[$k] = $extra_res;
				}
			}
		} catch ( \Exception $e ) {
			api_log ( "-1", $log_message, $extra_res, '同步产品价格，产品名称：[' .$ptype . ']--'.$e->getMessage() );
			$this->error = business_code(-9);
			return false;
		}
		
		//处理返回结果
		$common_product = new \Common\Model\ProductModel();
		$common_product_price = new \Common\Model\ProductPriceModel();
		$ok = $err = $skip = 0;
		foreach ($result as $price_type => $bus_info){
			if (empty($bus_info['info'])){
				continue;
			}else {
				//中文域名还是英文域名，购买的还是续费的
				$price_type_temp = explode('-', $price_type);
				$product_type_id = $price_type_temp[0];
				$buy_or_renew = $price_type_temp[1];
				
				foreach ($bus_info['info'] as $prices_info){
					foreach ($prices_info as $product_name => $product_prices_colloction){
						//拿到产品id
						$get_product_id_where = [
								"product_type_id"=>[ 'eq', $product_type_id ],
								"product_name" => [ 'eq', $product_name ],
						];
						$product_info = $common_product->field('id,api_name')->where($get_product_id_where)->find();
						//如果没有改产品信息，那么就跳过
						if (empty($product_info)){
							$skip++;
							continue;
						}
						$save_where['product_id'] = [ 'eq', $product_info['id'] ];
						$save_where['type'] = [ 'eq', $buy_or_renew ];
						$save_where['api_type'] = [ 'eq', 5 ];
						//准备更新产品的价格
						foreach ($product_prices_colloction as $product_prices){
							//这里返回的产品的价格可能是多个，结构有些蛋疼。。。额外处理 下
							foreach ($product_prices as $k=>$v){
								//如果是多个价格
								if (count($product_prices) > 1) {
									foreach ($product_prices as $ke => $va) {
										//$va====>   [ $month => $giant_price ]；一个只有一个键值对的数组
										list($month,$giant_price) = each($va);
										$save_where['month'] = [ 'eq', $month ];
										//更新的数据
										$save_data['giant_price'] = $giant_price;
										//如果$type==2，说明是既要更新景安价格，又要更新销售价格
										if ($type == 2){
											$save_data['product_price'] = $giant_price;
										}
										$update_res = $common_product_price->where($save_where)->save($save_data);
										$update_res === false ? $err++: $ok++;
									}
									//一个价格
								} else {
									$save_where['month'] = [ 'eq', $k ];
									//更新的数据
									$save_data['giant_price'] = $v;
									//如果$type==2，说明是既要更新景安价格，又要更新销售价格
									if ($type == 2){
										$save_data['product_price'] = $v;
									}
									$update_res = $common_product_price->where($save_where)->save($save_data);
									$update_res === false ? $err++: $ok++;
								}
				
							}
						}	
					}
				}
			}
		}
		if ( ($ok+$err-$skip) <= 0 ){
			$this->error = '本次没有需要同步价格的产品啦~';
		}else {
			$this->error = '本次共同步成功'.$ok.'个价格，同步失败'.$err.'个价格';
		}
		return true;
	}
 	
	

}
