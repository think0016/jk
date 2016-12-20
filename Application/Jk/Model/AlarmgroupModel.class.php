<?php

namespace Jk\Model;

use Think\Model;

class AlarmgroupModel extends Model {

    protected $tableName = 'jk_alarm_group';
    protected $_validate = array(
        array("name", "require", "报警组名称不能为空！"),
        array("begintime", "require", "报警开始时间不能为空！"),
        array("endtime", "require", "报警结束时间不能为空！"),
    );

}

?>