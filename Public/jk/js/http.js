var alarmlist = new Array();


function savealarm() {
	var item_text = $("#item_id option:selected").text();
	var item_id = $("#item_id option:selected").val();
	var op = $("#op1 option:selected").val();
	var op_text = $("#op1 option:selected").text();
	var threshold = $("#threshold1").val();
	var unit = $("#unit option:selected").val();
	var calc = $("#calc option:selected").val();
	var atimes = $("#atimes option:selected").val();


	var aitem = $("#item_id").find("option:selected").text();
	if (aitem == '可用率') {
		op = $("#op2 option:selected").val();
		op_text = $("#op2 option:selected").text();
		threshold = $("#threshold2").val();
		unit = '%';
	}

	var index = alarmlist.length;
	var temp = new Array();
	temp[0] = item_id;
	temp[1] = op;
	temp[2] = threshold;
	temp[3] = unit;
	temp[4] = calc;
	temp[5] = atimes;
	temp[6] = item_text;
	temp[7] = op_text;

	alarmlist[index] = temp;

	$('#addalarm').modal('hide');
	$('#addalarm').modal('hide');
	var tr = createtr(temp);
	$('#alarm_table').append(tr);
	//console.log("alarmlist");
	//console.log(alarmlist);

}

function savefrom() {
	var num = alarmlist.length;
	$("input[name='alarm_num']").val(alarmlist.length);
	for (var i = 0; i < alarmlist.length; i++) {
		var temp = alarmlist[i];
		var name = "a" + i;
		var input = "<input type=\"hidden\" name=\"" + name + "\" value=\"0\"></div>";
		$('#hiddens').append(input);
		var s = "input[name='" + name + "']";
		$(s).val(temp);
	}
	$('#form1').submit();
}

function createtr(arr) {
	var html = "<tr>";
	html += "<td>" + arr[6] + "</td><td>" + arr[7] + "</td><td>" + arr[2] + arr[3] + "</td><td>" + arr[5] + "</td><td><span class=\"badge bg-green\">开启</span></td><td><div class=\"btn-group\"><button type=\"button\" class=\"btn btn-xs btn-info\">修改</button><button type=\"button\" class=\"btn btn-xs btn-info\">删除</button></div></td>";
	html += "</tr>";
	return html;
}