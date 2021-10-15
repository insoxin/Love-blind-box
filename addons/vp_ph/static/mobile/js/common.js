(function($){  
	$.fn.serializeJson=function(){  
		var serializeObj={};  
		$(this.serializeArray()).each(function(){  
			serializeObj[this.name]=this.value;  
		});  
		return serializeObj;  
	};  
})(jQuery);

function VP_TIME_FORMAT(t,m){
	var s='';
	if(t>172800){
		s+=parseInt(t/86400)+'天';
		t=t%86400;
		if(m=='d'){
			return s;
		}
	}
	if(t>3600){
		s+=parseInt(t/3600)+'小时';
		t=t%3600;
		if(m=='h'){
			return s;
		}
	}
	if(t>60){
		s+=parseInt(t/60)+'分';
		t=t%60;
		if(m=='m'){
			return s;
		}
	}
	s+=parseInt(t)+'秒';
	return s;
};

Date.prototype.VP_FORMAT = function(format) {
	var date = {
		  "M+": this.getMonth() + 1,
		  "d+": this.getDate(),
		  "h+": this.getHours(),
		  "m+": this.getMinutes(),
		  "s+": this.getSeconds(),
		  "q+": Math.floor((this.getMonth() + 3) / 3),
		  "S+": this.getMilliseconds()
	};
	if (/(y+)/i.test(format)) {
		  format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
	}
	for (var k in date) {
		  if (new RegExp("(" + k + ")").test(format)) {
				 format = format.replace(RegExp.$1, RegExp.$1.length == 1
						? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
		  }
	}
	return format;
}

function VP_FORMAT(t,fmt){
	var newDate = new Date();
	newDate.setTime(t * 1000);
	return newDate.VP_FORMAT(fmt);
}

function VP_MONEY_FORMAT(money, digit){
	digit=digit?digit:2;
	var tpMoney = '0.00';  
	if(undefined != money){  
		tpMoney = money;  
	}  
	tpMoney = new Number(tpMoney);  
	if(isNaN(tpMoney)){  
	  return '0.00';  
	}  
	tpMoney = tpMoney.toFixed(digit) + '';  
	var re = /^(-?\d+)(\d{3})(\.?\d*)/;  
	while(re.test(tpMoney)){  
		 tpMoney = tpMoney.replace(re, "$1,$2$3")  
		}  	  
	return tpMoney;  
} 

$(function(){
	FastClick.attach(document.body);
});