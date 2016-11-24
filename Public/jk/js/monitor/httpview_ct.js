//$(function() {
	var mymap = echarts.init(document.getElementById('map'));

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

	

	//Date range as a button
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
			$('#daterange-btn span').html(start.format('YYYY-MM-DD') + ' 至 ' + end.format('YYYY-MM-DD'));
		}
	);

	$('#daterange-btn').on('apply.daterangepicker', function(ev, picker) {
		//console.log(picker.startDate.format('YYYY-MM-DD'));
		//console.log(picker.endDate.format('YYYY-MM-DD'));
		var sdate = picker.startDate.format('YYYY-MM-DD');
		var edate = picker.endDate.format('YYYY-MM-DD');
		var url = rooturl + "/HttpView/ctindex/"+"tid/"+tid+"/sdate/"+sdate+"/edate/"+edate;
		window.location.href = url;
	});

//});