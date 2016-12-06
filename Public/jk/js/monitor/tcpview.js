var appurl = rooturl + '/TcpView/';
var mymap = echarts.init(document.getElementById('map'));
var myline = echarts.init(document.getElementById('line'));
var posturl = appurl + 'getlinedatax.html';

var mapdata = $.parseJSON(mapdata);
var mapitem = "";
var mformatter = '';
var maxv = 0;
var subtext = "";
var inRange_color = ['#008000', '#FFFF00', '#B22222'];
if (item == "1") {
	mapitem = "响应时间";
	mformatter = '平均{a}:{c}毫秒';
	maxv = 1000;
	subtext = "响应时间";
} else if (item == "2") {
	mapitem = "可用率";
	mformatter = '平均{a}:{c}%';
	maxv = 100;
	subtext = "可用性";
	inRange_color = ['#B22222', '#FFFF00', '#008000'];
}

function randomData() {
	return Math.round(Math.random() * 500);
}

option1 = {
	title: {
		text: '实时监控状态图',
		subtext: subtext,
		left: 'center'
	},
	tooltip: {
		trigger: 'item',
		formatter: mformatter
	},
	visualMap: {
		min: 0,
		max: maxv,
		left: 'left',
		top: 'bottom',
		inRange: {
			color: inRange_color
		},
		text: ['高', '低'], // 文本，默认为数值文本
		calculable: true
	},
	series: [{
		name: mapitem,
		type: 'map',
		mapType: 'china',
		roam: false,
		label: {
			normal: {
				show: true
			},
			emphasis: {
				show: true
			}
		},
		data: mapdata
	}]
};

mymap.setOption(option1);

$.post(posturl, {
	tid: tid,
	sdate: sdate,
	edate: edate,
	step: step
}, function(data, textStatus, xhr) {
	/* optional stuff to do after success */
	var linedata = $.parseJSON(data);


	option2 = {
		title: {
			text: '响应时间&可用率曲线图'
		},
		tooltip: {
			trigger: 'axis'
		},
		legend: {
			data: ['响应时间', '可用率']
		},
		toolbox: {
			feature: {
				saveAsImage: {}
			}
		},
		xAxis: {
			type: 'category',
			boundaryGap: false,
			interval: 100,
			data: linedata.xv
		},
		yAxis: [{
			name: '响应时间(ms)',
			min: 0,
			//max : 100,
			type: 'value',

			axisLine: {
				// show:false
			}
		}, {
			name: '可用率(%)',
			min: 0,
			max: 100,
			type: 'value',
			axisLine: {
				// show:false
			}
		}],
		series: [{
			name: '响应时间',
			yAxisIndex: 0,
			type: 'line',
			smooth: true,
			data: linedata.yv1
		}, {
			name: '可用率',
			yAxisIndex: 1,
			type: 'line',
			smooth: true,
			data: linedata.yv2
		}]
	};

	myline.setOption(option2);
});

//Date range as a button
var sdate1 = sdate.split(" ");
var edate1 = edate.split(" ");
$('#daterange-btn span').html(sdate1[0] + ' 至 ' + edate1[0]);
$('#daterange-btn').daterangepicker({
		maxDate: moment(), //最大时间   
		showDropdowns: true,
		timePicker: false, //是否显示小时和分钟 
		dateLimit: {
			days: 120
		},
		ranges: {
			'今日': [moment().startOf('day'), moment()],
			'昨日': [moment().subtract('days', 1).startOf('day'), moment().subtract('days', 1).endOf('day')],
			'最近一周': [moment().subtract('days', 6), moment()]
		},
		opens: 'right', //日期选择框的弹出位置  
		format: 'YYYY-MM-DD', //控件中from和to 显示的日期格式  
		locale: {
			applyLabel: '确定',
			cancelLabel: '取消',
			fromLabel: '起始时间',
			toLabel: '结束时间',
			customRangeLabel: '自定义',
			daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
			monthNames: ['一月', '二月', '三月', '四月', '五月', '六月',
				'七月', '八月', '九月', '十月', '十一月', '十二月'
			],
			firstDay: 1
		},
		startDate: moment().subtract(29, 'days'),
		endDate: moment()
	},
	function(start, end) {
		//$('#daterange-btn span').html(start.format('YYYY-MM-DD') + ' 至 ' + end.format('YYYY-MM-DD'));
	}
);

$('#daterange-btn').on('apply.daterangepicker', function(ev, picker) {
	//console.log(picker.startDate.format('YYYY-MM-DD'));
	//console.log(picker.endDate.format('YYYY-MM-DD'));
	var sdate = picker.startDate.format('YYYY-MM-DD');
	var edate = picker.endDate.format('YYYY-MM-DD');
	var url = appurl + "index/" + "tid/" + tid + "/sdate/" + sdate + "/edate/" + edate;
	window.location.href = url;
});


function createtable(sdate, edate, aid) {
	var purl = appurl + "getalarmtabledata/tid/" + tid + "/sdate/" + sdate + "/edate/" + edate + "/aid/" + aid + "/limit/2";

	// if(table != ''){
	// 	$('#alarmtable').dataTable().fnDestroy();
	// }

	table = $('#alarmtable').dataTable({
		"ajax": {
			"url": purl,
			//默认为data,这里定义为空，则只需要传不带属性的数据
			"dataSrc": ""
		},
		"pageLength": 2,
		"paging": false,
		"info": false,
		"lengthChange": false,
		"searching": false,
		"ordering": true,
		"retrieve": true,
		"autoWidth": false,
		"columns": [{
			"data": 0
		}, {
			"data": 1
		}, {
			"data": 5
		}, {
			"data": 6
		}, {
			"data": 7
		}],
		"language": {
			"lengthMenu": "每页 _MENU_ 条记录",
			"zeroRecords": "近期无告警记录",
			"info": "第 _PAGE_ 页 ( 总共 _PAGES_ 页 )",
			"infoEmpty": "无记录",
			"infoFiltered": "(从 _MAX_ 条记录过滤)",
			"oPaginate": {
				"sFirst": "首页",
				"sPrevious": "上页",
				"sNext": "下页",
				"sLast": "末页"
			}
		}
	});
}

createtable(sdate1[0], edate1[0], 0);