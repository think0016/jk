// 基于准备好的dom，初始化echarts实例
var myChart = echarts.init(document.getElementById('mmap'));
var layer;
var layer1;
var mapposturl = rooturl+"/Utils/getmpdata";


layui.use(['layer'], function() {
	layer = layui.layer;
	//layer.msg('Hello World');
});


//临时数据
var ddata = [{
	name: '广东',
	selected: true,
	value: 1
}, {
	name: '四川',
	selected: true,
	value: 1
}];



function smp() {
	//初始化模态框
	layer1 = layer.open({
		area: ['1200px', '700px'],
		title: '选择监控点',
		resize : false,
		type: 1,
		content: $('#pointdiv') //这里content是一个普通的String
	});

	//初始化地图
	$.post(mapposturl, {mids: ''}, function(data, textStatus, xhr) {
		/*optional stuff to do after success */
	ddata = jQuery.parseJSON(data);

			var option = {
				title: {
					text: '全国监控点地图'
				},
				series: [{
					name: '中国',
					type: 'map',
					mapType: 'china',
					// selectedMode : 'false',
					label: {
						normal: {
							show: true
						},
						emphasis: {
							show: true
						}
					},
					data: ddata
				}]
			};

			myChart.setOption(option);
	});	
}

function smpcls() {
	layer.close(layer1);
}

$("input[name='cmpoint']").click(function(event) {
	/* Act on the event */
	var str = '';
	$("input[name='cmpoint']:checked").each(function(index, el) {
		if(str !== ''){
			str = str + ',' + ":" +$(this).val()+ ":";
		}else{
			str = str + ":" +$(this).val()+ ":";
		}
	});

	$.post(mapposturl, {mids: str}, function(data, textStatus, xhr) {
		/*optional stuff to do after success */
	ddata = jQuery.parseJSON(data);

			var option = {
				title: {
					text: '全国监控点地图'
				},
				series: [{
					name: '中国',
					type: 'map',
					mapType: 'china',
					// selectedMode : 'false',
					label: {
						normal: {
							show: true
						},
						emphasis: {
							show: true
						}
					},
					data: ddata
				}]
			};

			myChart.setOption(option);
	});	



});

