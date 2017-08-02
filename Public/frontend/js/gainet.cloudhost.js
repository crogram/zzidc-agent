//计算刻度
function getInterval(type) {
	var interval = 0;
	if (type === "cpu") {
		interval = buy.cpu.interval || 1;
	} else if (type === "memory") {
		interval = buy.memory.interval || 1;
	} else if (type === "disk") {
		interval = buy.disk.interval || 5;
	} else if (type === "bandwidth") {
		interval = buy.bandwidth.interval || 1;
	} else if (type === "time") {
		interval = buy.time.interval || 1;
	}
	return interval;
}

// 统一价格计算
function getPrice(que, par, beforeCallback, afterCallback) {
	// if(que.queue().length == 0){
	if(!par.url){
		par.url = "price";
	}
	gainet.callFunc(null, beforeCallback);
	que.queue(function() {
		if (par.gmqx > 0) {
			gainet.post(par.url, par, function(data) {
				if (que.queue().length == 1) {
					var price = data.price;
					gainet.callFunc(null, afterCallback, price);
				}
				que.dequeue();
			}, "json");
		} else {
			if (que.queue().length == 1) {
				gainet.callFunc(null, afterCallback, 0);
			}
			gainet(this).dequeue();
		}
	});
	// }
}

// IP价格计算
function getIpPrice(que, par, beforeCallback, afterCallback) {
	gainet.callFunc(null, beforeCallback);
	que.queue(function() {
		gainet.ajax({
			type:"GET",
			url:"ipprice",
			data:{
					"bandwidth":par.bandwidth,
					"voucher":par.voucher,
					"dqbh":par.dqbh,
					"gmqx":par.fwqx
				},
			dataType:"json",
			success:function(data) {
						if (que.queue().length == 1) {
							var price = data.price;
							gainet.callFunc(null, afterCallback, price);
						}
						que.dequeue();
					},
			error:function(data){
						que.dequeue();
					}
		});
	});
}
// 升级IP价格计算
function getUpIpPrice(que, par, beforeCallback, afterCallback) {
	gainet.callFunc(null, beforeCallback);
	que.queue(function() {
		gainet.ajax({
			type:"GET",
			url:"upIpPrice",
			data:{
			"bandwidth":par.bandwidth,
			"voucher":par.voucher,
			"chbh":par.chbh,
			"dqbh":par.dqbh
		},
		dataType:"json",
		success:function(data) {
			if (que.queue().length == 1) {
				var price = data.price;
				gainet.callFunc(null, afterCallback, price);
			}
			que.dequeue();
		},
		error:function(data){
			que.dequeue();
		}
		});
	});
}

// 升级价格计算
function getUpPrice(que, par, beforeCallback, afterCallback) {
	gainet.callFunc(null, beforeCallback);
	que.queue(function() {
		gainet.ajax({
			type:"GET",
			url:"upprice",
			data:{
					"cpu":par.cpu,
					"mem":par.mem,
					"disk":par.disk,
					"bandwidth":par.bandwidth,
					"voucher":par.voucher,
					"dqbh":par.dqbh,
					"ywbh":par.ywbh
				},
			dataType:"json",
			success:function(data) {
						if (que.queue().length == 1) {
							var price = data.price;
							gainet.callFunc(null, afterCallback, price);
						}
						que.dequeue();
					},
			error:function(data){
						que.dequeue();
					}
		});
	});
}