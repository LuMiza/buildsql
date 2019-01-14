<?php

include_once __DIR__ . '/SqlDriver.class.php';
include_once __DIR__ . '/SqlBuild.class.php';
include_once __DIR__ . '/Sql.class.php';


$model = Sql::getInstance('think');

# 读取字段值
$nickname = $model->table('table_name')->where('id=3')->getField('nickname');
$nickname = $model->table('table_name')->where('id=3')->getField('id,nickname');
$sql = $model->table('table_name')->where('id=88')->getField('id,nickname,email',':');
$sql = $model->table('table_name')->where('id=88')->getField('id,nickname,email',5);
$sql = $model->table('table_name')->where('id=88')->getField('id',5);
//exit($sql);

# 批量添加数据
$dataList[] = array('name'=>'table_name','email'=>'table_name@gamil.com', 'sort'=>8);
$dataList[] = array('name'=>'table_name888','email'=>'table_name@gamil.com','sort'=>7);
$sql = $model->table('user_your')->addAll($dataList);
//exit($sql);

# add方法
$data['name'] = 'table_name';
$data['email'] = '892222@gmail.com';
$data['sort'] = 9;
$sql = $model->table('tuip')->add($data);
//exit($sql);

//此种操作不支持
$data['name'] = 'table_name';
$data['email'] = 'table_name@gmail.com';
$data['test'] = 'test';
$sql = $model->table('usssss')->field('name')->data($data)->add();
//exit($sql);

#count
$sql = $model->table('usssss')->count();
//exit($sql);

# max
$sql = $model->table('user_your')->max('score');
//exit($sql);

# min
$sql = $model->table('user_your')->where('score>0')->min('score');
//exit($sql);

# avg
$avgScore = $model->table('user_your')->avg('score');
//exit($avgScore);

$sumScore = $model->table('user_your')->where('id > 10')->sum('score');
//exit($sumScore);



# setInc
$sql =  $model->table('user_your')->where('id=5')->setInc('score',3);
//exit($sql);

# setDec
$sql = $model->table('user')->where('id=5')->setDec('score',3);
//exit($sql);

# setField 设置记录的某个字段值
$data = array('name'=>'table_name','email'=>'table_name@gmail.com', 'sort'=>88);
$sql = $model->table('yyyuser')-> where('id=5')->setField($data);
//exit($sql);

# 查询select
$sql = $model->field('user.name,role.title')
    ->table('user,role')
    ->join('left join orders on orders.uid = user.id')
    ->limit(10)
    ->order('add_time desc')
    ->distinct(true)
    ->select();

# 更新数据
$data['name'] = 'table_name';
$data['email'] = 'table_name@gmail.com';
$sql = $model->table('user')->where('id=5')->save($data);


# 数据过滤
$data['name'] = 'test';
$data['email'] = '<b>test@gmail.com</b>';
$sql = $model->table('user')->where('id=5')->filter('strip_tags')->save($data); // 根据条件保存修改的数据

# 数据删除

$sql = $model->table('user')->where('id=5')->delete(); // 删除id为5的用户数据
$sql = $model->table('user')->delete('1,2,5'); // 删除主键为1,2和5的用户数据
//$sql = $model->table('user')->where('status=0')->delete(); // 删除所有状态为0的用户数据

exit($sql);