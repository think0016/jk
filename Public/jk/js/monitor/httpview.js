var mymap = echarts.init(document.getElementById('map'));
var myline = echarts.init(document.getElementById('line'));

function randomData() {
    return Math.round(Math.random() * 500);
}

option1 = {
    title: {
        text: '实时监控状态图',
        subtext: '响应时间',
        left: 'center'
    },
    tooltip: {
        trigger: 'item'
    },
    // legend: {
    //     orient: 'vertical',
    //     left: 'left',
    //     data:['iphone3']
    // },
    visualMap: {
        min: 0,
        max: 1000,
        left: 'left',
        top: 'bottom',
        text: ['高', '低'], // 文本，默认为数值文本
        calculable: true
    },
    // toolbox: {
    //     show: true,
    //     orient: 'vertical',
    //     left: 'right',
    //     top: 'center',
    //     feature: {
    //         dataView: {readOnly: false},
    //         restore: {},
    //         saveAsImage: {}
    //     }
    // },
    series: [{
        name: 'iphone3',
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
        data: [{
            name: '北京',
            value: randomData()
        }, {
            name: '天津',
            value: randomData()
        }, {
            name: '上海',
            value: randomData()
        }, {
            name: '重庆',
            value: randomData()
        }, {
            name: '河北',
            value: randomData()
        }, {
            name: '河南',
            value: randomData()
        }, {
            name: '云南',
            value: randomData()
        }, {
            name: '辽宁',
            value: randomData()
        }, {
            name: '黑龙江',
            value: randomData()
        }, {
            name: '湖南',
            value: randomData()
        }, {
            name: '安徽',
            value: randomData()
        }, {
            name: '山东',
            value: randomData()
        }, {
            name: '新疆',
            value: randomData()
        }, {
            name: '江苏',
            value: randomData()
        }, {
            name: '浙江',
            value: randomData()
        }, {
            name: '江西',
            value: randomData()
        }, {
            name: '湖北',
            value: randomData()
        }, {
            name: '广西',
            value: randomData()
        }, {
            name: '甘肃',
            value: randomData()
        }, {
            name: '山西',
            value: randomData()
        }, {
            name: '内蒙古',
            value: randomData()
        }, {
            name: '陕西',
            value: randomData()
        }, {
            name: '吉林',
            value: randomData()
        }, {
            name: '福建',
            value: randomData()
        }, {
            name: '贵州',
            value: randomData()
        }, {
            name: '广东',
            value: randomData()
        }, {
            name: '青海',
            value: randomData()
        }, {
            name: '西藏',
            value: randomData()
        }, {
            name: '四川',
            value: randomData()
        }, {
            name: '宁夏',
            value: randomData()
        }, {
            name: '海南',
            value: randomData()
        }, {
            name: '台湾',
            value: randomData()
        }, {
            name: '香港',
            value: randomData()
        }, {
            name: '澳门',
            value: randomData()
        }]
    }]
};

mymap.setOption(option1);



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
    // grid: {
    //     left: '3%',
    //     right: '4%',
    //     bottom: '3%',
    //     containLabel: true
    // },
    toolbox: {
        feature: {
            saveAsImage: {}
        }
    },
    xAxis: {
        type: 'category',
        boundaryGap: false,
        data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
    },
    yAxis: [
        {
            name: '响应时间(ms)',
            min:0,
            max: 1000,
            type: 'value',
            splitNumber:5,
            axisLine:{
                // show:false
            }
        },
        {
            name: '可用率(%)',
            min:0,
            max: 100,
            type: 'value',
            splitNumber:5,
            axisLine:{
                // show:false
            }
        }
    ],
    series: [{
        name: '响应时间',
        yAxisIndex:0,
        type: 'line',
        data: [120, 132, 101, 134, 90, 230, 210]
    }, {
        name: '可用率',
        yAxisIndex:1,
        type: 'line',
        data: [100, 100, 50, 80, 100, 74, 100]
    }]
};

myline.setOption(option2);