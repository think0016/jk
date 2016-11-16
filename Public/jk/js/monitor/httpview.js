var mymap = echarts.init(document.getElementById('map'));
var myline = echarts.init(document.getElementById('line'));
var posturl = rooturl + '/HttpView/getlinedatax.html';

var mapdata = $.parseJSON(mapdata);
var mapitem = "";
var mformatter = '';
var maxv = 0;
var subtext = "";
if (item == "2") {
	mapitem = "响应时间";
	mformatter = '平均{a}:{c}毫秒';
	maxv = 3000;
	subtext = "响应时间";
} else if (item == "8") {
	mapitem = "可用率";
	mformatter = '平均{a}:{c}%';
	maxv = 100;
	subtext = "可用性";
}

function randomData() {
	return Math.round(Math.random() * 500);
}

option1 = {
	title : {
		text : '实时监控状态图',
		subtext : subtext,
		left : 'center'
	},
	tooltip : {
		trigger : 'item',
		formatter : mformatter
	},
	visualMap : {
		min : 0,
		max : maxv,
		left : 'left',
		top : 'bottom',
		text : [ '高', '低' ], // 文本，默认为数值文本
		calculable : true
	},
	series : [ {
		name : mapitem,
		type : 'map',
		mapType : 'china',
		roam : false,
		label : {
			normal : {
				show : true
			},
			emphasis : {
				show : true
			}
		},
		data : mapdata
	} ]
};

mymap.setOption(option1);

$.post(posturl, {
	tid : tid,
	sdate : sdate,
	edate : edate,
	step : step
}, function(data, textStatus, xhr) {
	/* optional stuff to do after success */
	var linedata = $.parseJSON(data);
	
	
	option2 = {
			title : {
				text : '响应时间&可用率曲线图'
			},
			tooltip : {
				trigger : 'axis'
			},
			legend : {
				data : [ '响应时间', '可用率' ]
			},
			toolbox : {
				feature : {
					saveAsImage : {}
				}
			},
			xAxis : {
				type : 'category',
				boundaryGap : false,
				interval : 100,
				data : linedata.xv
			},
			yAxis : [ {
				name : '响应时间(ms)',
				min : 0,
				max : 5000,
				type : 'value',
				
				axisLine : {
				// show:false
				}
			}, {
				name : '可用率(%)',
				min : 0,
				max : 100,
				type : 'value',
				axisLine : {
				// show:false
				}
			} ],
			series : [ {
				name : '响应时间',
				yAxisIndex : 0,
				type : 'line',
				data : linedata.yv1
			}, {
				name : '可用率',
				yAxisIndex : 1,
				type : 'line',
				data : linedata.yv2
			} ]
		};
	
	myline.setOption(option2);
});

//option2 = {
//	title : {
//		text : '响应时间&可用率曲线图'
//	},
//	tooltip : {
//		trigger : 'axis'
//	},
//	legend : {
//		data : [ '响应时间', '可用率' ]
//	},
//	toolbox : {
//		feature : {
//			saveAsImage : {}
//		}
//	},
//	xAxis : {
//		type : 'category',
//		boundaryGap : false,
//		data : [ '周一', '周二', '周三', '周四', '周五', '周六', '周日' ]
//	},
//	yAxis : [ {
//		name : '响应时间(ms)',
//		min : 0,
//		max : 1000,
//		type : 'value',
//		splitNumber : 5,
//		axisLine : {
//		// show:false
//		}
//	}, {
//		name : '可用率(%)',
//		min : 0,
//		max : 100,
//		type : 'value',
//		splitNumber : 5,
//		axisLine : {
//		// show:false
//		}
//	} ],
//	series : [ {
//		name : '响应时间',
//		yAxisIndex : 0,
//		type : 'line',
//		data : [ 120, 132, 101, 134, 90, 230, 210 ]
//	}, {
//		name : '可用率',
//		yAxisIndex : 1,
//		type : 'line',
//		data : [ 100, 100, 50, 80, 100, 74, 100 ]
//	} ]
//};

