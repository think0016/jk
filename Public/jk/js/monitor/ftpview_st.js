var appurl = rooturl + '/FtpView/';
var mymap = echarts.init(document.getElementById('map'));


//MAP
var mapdata = $.parseJSON(mapdata);
option1 = {
	title : {
		text : '实时监控状态图',
		subtext : "可用性",
		left : 'center'
	},
	tooltip : {
		trigger : 'item',
		formatter : '平均{a}:{c}%'
	},
	visualMap : {
		min : 0,
		max : 100,
		left : 'left',
		top : 'bottom',
		text : [ '高', '低' ], // 文本，默认为数值文本
		calculable : true
	},
	series : [ {
		name : "可用率",
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



//Date range as a button
var sdate1=sdate.split(" ");
var edate1=edate.split(" ");
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
	var url = appurl + "stindex/" + "tid/" + tid + "/sdate/" + sdate + "/edate/" + edate;
	window.location.href = url;
});


var lb = 1;
var po = 1;
function drewline(param, type) {

	if (type == 'lb') {
		lb = param;
		if (param == 1) {
			$('#llbtn').addClass('active');
			$('#bbbtn').removeClass('active');
		} else {
			$('#bbbtn').addClass('active');
			$('#llbtn').removeClass('active');
		}
	} else {
		po = param;
		if (po == 1) {
			$('#opbtn').addClass('active');
			$('#prbtn').removeClass('active');
		} else {
			$('#prbtn').addClass('active');
			$('#opbtn').removeClass('active');
		}
	}


	var myline = echarts.init(document.getElementById('line'));
	var posturl = appurl + 'getlinedata.html';
	if (lb == 2) {
		var posturl = appurl + 'getbardata.html';
	}
	$.post(posturl, {
		tid: tid,
		sdate: sdate,
		edate: edate,
		po: po,
		itemid: item,
		step: 3600
	}, function(data, textStatus, xhr) {
		/* optional stuff to do after success */

		var linedata = $.parseJSON(data);
		var option2 = {};
		if (lb == 2) {
			option2 = {
				tooltip: {
					trigger: 'axis',
					axisPointer: { // 坐标轴指示器，坐标轴触发有效
						type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
					}
				},
				legend: {
					data: linedata.legend
				},
				grid: {
					left: '3%',
					right: '4%',
					bottom: '3%',
					containLabel: true
				},
				xAxis: {
					type: 'value'
				},
				yAxis: {
					type: 'category',
					data: linedata.xv
				},
				series: linedata.series
			};
		} else {
			option2 = {
				title: {
					text: '可用率曲线图'
				},
				tooltip: {
					trigger: 'axis'
				},
				legend: {
					data: linedata.legend
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
					name: '可用率(%)',
					type: 'value',
					axisLine: {
						// show:false
					}
				}],
				series: linedata.series
			};
		}


		myline.setOption(option2);
	});
};


drewline(1, 'po');
// 	$.post(posturl, {
// 	tid : tid,
// 	sdate : sdate,
// 	edate : edate,
// 	po : 1,
// 	itemid : 2,
// 	lb : 1,
// 	step : 3600
// }, function(data, textStatus, xhr) {
// 	/* optional stuff to do after success */

// });
//});

//datatable 数据表格
$('.potable').DataTable({
	"paging": true,
	"lengthChange": false,
	"searching": false,
	"ordering": true,
	"info": true,
	"autoWidth": false,
	"language": {
		"lengthMenu": "每页 _MENU_ 条记录",
		"zeroRecords": "没有找到记录",
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