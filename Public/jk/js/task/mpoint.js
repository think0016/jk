// 基于准备好的dom，初始化echarts实例
var myChart = echarts.init(document.getElementById('mmap'));
var layer;
var layer1;
var mapposturl = rooturl + "/Utils/getmpdata";
var pointlist = new Array();

layui.use([ 'layer' ], function() {
	layer = layui.layer;
	// layer.msg('Hello World');
});

function smp() {
	// 初始化模态框
	layer1 = layer.open({
		area : [ '1200px', '700px' ],
		title : '选择监控点',
		resize : false,
		type : 1,
		content : $('#pointdiv')
	// 这里content是一个普通的String
	});

	// 初始化地图
	var str = gcheck();
	$.post(mapposturl, {
		mids : str
	}, function(data, textStatus, xhr) {
		/* optional stuff to do after success */
		ddata = jQuery.parseJSON(data);

		var option = {
			title : {
				text : '全国监控点地图'
			},
			series : [ {
				name : '中国',
				type : 'map',
				mapType : 'china',
				// selectedMode : 'false',
				label : {
					normal : {
						show : true
					},
					emphasis : {
						show : true
					}
				},
				data : ddata
			} ]
		};

		myChart.setOption(option);
	});
}

function smpcls() {
	// console.log(pointlist);
	layer.close(layer1);
}

$("input[name='cmpoint']").click(function(event) {
	/* Act on the event */
	var str = gcheck();

	$.post(mapposturl, {
		mids : str
	}, function(data, textStatus, xhr) {
		/* optional stuff to do after success */
		ddata = jQuery.parseJSON(data);

		var option = {
			title : {
				text : '全国监控点地图'
			},
			series : [ {
				name : '中国',
				type : 'map',
				mapType : 'china',
				// selectedMode : 'false',
				label : {
					normal : {
						show : true
					},
					emphasis : {
						show : true
					}
				},
				data : ddata
			} ]
		};

		myChart.setOption(option);
	});

});

function savepoint() {
	// var n = 0;
	fixflag = false;
	pointlist = new Array();
	$("input[name='cmpoint']:checked").each(function(index, el) {
		var temp = new Array();
		temp[0] = $(this).val();
		temp[1] = $(this).attr("mname");
		pointlist[index] = temp;

		var pointtype = $(this).attr("pointtype");
		if(pointtype == 1 ){
			fixflag = true;
		}
		// console.log(pointlist);

		// if (str !== '') {
		// str = str + ',' + ":" + $(this).val() + ":";
		// } else {
		// str = str + ":" + $(this).val() + ":";
		// }
	});

	fix_frequency_button(fixflag);//修正联通点和测试点同步问题
	midinfo();
	smpcls();
}

function gcheck() {
	var str = '';
	if (pointlist.length > 0) {
		$("input[name='cmpoint']:checked").each(function(index, el) {
			if (str !== '') {
				str = str + ',' + ":" + $(this).val() + ":";
			} else {
				str = str + ":" + $(this).val() + ":";
			}
		});
	}
	// else {
	// var mids = $("input[name='mids']").val();
	// if (mids != '') {
	// // mids = mids.replace(/:/g, "");
	// // var m = mids.split(',',mids);
	// str = mids;
	// }

	// }
	return str;
}

function midinfo() {
	var mnum = pointlist.length;
	if (userlevel == 1 && mnum > 3) {
		$('#midinfo').html("<font color=\"red\">监控点数超过限制</font>");
	} else if (userlevel == 2 && mnum > 6) {
		$('#midinfo').html("<font color=\"red\">监控点数超过限制</font>");
	} else if (userlevel == 3 && mnum > 10) {
		$('#midinfo').html("<font color=\"red\">监控点数超过限制</font>");
	} else {
		$('#midinfo').text("已配置" + pointlist.length + "个监控点");
	}
}

$("input[name='cmpoint']:checked").each(function(index, el) {
	var temp = new Array();
	temp[0] = $(this).val();
	temp[1] = $(this).attr("mname");
	pointlist[index] = temp;

});

//修正联通点和测试点同步问题
function fix_frequency_button(fixflag){
	if(fixflag){
			$('.frequency').removeClass('active');
			$('.frequency').removeClass('disabled');
			$(".frequency[time='1']").addClass("disabled").attr("dis","1");
			$(".frequency[time='2']").addClass("disabled").attr("dis","1");
			$(".frequency[time='3']").addClass("disabled").attr("dis","1");
			$(".frequency[time='5']").addClass("disabled").attr("dis","1");
			$(".frequency[time='10']").addClass("active").attr("dis","0");
			$(".frequency[time='15']").addClass("disabled").attr("dis","1");
			//$(".frequency[time='30']").addClass("disabled");
			//$(".frequency[time='60']").addClass("disabled");
			$("input[name='frequency']").val(10);
	}else{
			$('.frequency').removeClass('active');
			$('.frequency').removeClass('disabled');
		if(userlevel == 1){
			$(".frequency[time='1']").addClass("disabled").attr("dis","1");
			$(".frequency[time='2']").addClass("disabled").attr("dis","1");
			$(".frequency[time='3']").addClass("disabled").attr("dis","1");
			$(".frequency[time='5']").addClass("disabled").attr("dis","1");
			$(".frequency[time='10']").addClass("active").attr("dis","0");
			$(".frequency[time='15']").attr("dis","0");
			$(".frequency[time='30']").attr("dis","0");
			$(".frequency[time='60']").attr("dis","0");
			$("input[name='frequency']").val(10);
		}else{
			$(".frequency[time='1']").addClass("disabled").attr("dis","1");
			$(".frequency[time='2']").addClass("disabled").attr("dis","1");
			$(".frequency[time='3']").addClass("disabled").attr("dis","1");
			$(".frequency[time='5']").addClass("active").attr("dis","0");
			$(".frequency[time='10']").attr("dis","0");
			$(".frequency[time='15']").attr("dis","0");
			$(".frequency[time='30']").attr("dis","0");
			$(".frequency[time='60']").attr("dis","0");
			$("input[name='frequency']").val(5);

		}
	}

}

midinfo();