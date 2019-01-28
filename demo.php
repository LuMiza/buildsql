<?php
$model = \BuildSql\Sql::getInstance();

# union

$sql = $model->field('name')->table('user_0')
    ->union(array('field'=>'name','table'=>'user_1'))
    ->union(array('field'=>'name','table'=>'user_2'))
    ->select();
//exit($sql);

$sql = $model->field('name')
    ->table('user_0')
    ->union(array('SELECT name FROM user_1','SELECT name FROM user_2'))
    ->select();
//exit($sql);

$sql = $model->field('name')
    ->table('user_0')
    ->union('SELECT name FROM user_1')
    ->union('SELECT name FROM user_2')
    ->select();
//exit($sql);

# union all
$sql = $model->field('name')
    ->table('user_0')
    ->union('SELECT name FROM user_1',true)
    ->union('SELECT name FROM user_2',true)
    ->select();
//exit($sql);

$sql  = $model->field('name')
    ->table('user_0')
    ->union(array('SELECT name FROM user_1','SELECT name FROM user_2'),true)
    ->select();
exit($sql);

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

# 带有表达式 更新数据
unset($data);
$data['name'] = 'rumble';
$data['score'] = array('exp','score+1');// 用户的积分加1
$sql = $model->table('user')->where('id=5')->save($data); // 根据条件保存修改的数据


# 数据过滤
unset($data);
$data['name'] = 'test';
$data['email'] = '<b>test@gmail.com</b>';
$sql = $model->table('user')->where('id=5')->filter('strip_tags')->save($data); // 根据条件保存修改的数据
//exit($sql);

# 数据删除

$sql = $model->table('user')->where('id=5')->delete(); // 删除id为5的用户数据
$sql = $model->table('user')->delete(); // 删除id为5的用户数据
$sql = $model->table('user')->where('status=0')->delete(); // 删除所有状态为0的用户数据


# find
$sql = $model->table('user')->where('id=8')->find();

# 根据某个字段查询，例如查询姓名为Rumble的可以用
$sql = $model->table('user')->getByName("Rumble");


# 各种查询表达式
$sql = $model->table('user')->where('type=1 AND status=1')->select();

$condition['name'] = 'rname';
$condition['account'] = 'acrumble';
$condition['_logic'] = 'OR';// 把查询条件传入查询方法
$sql = $model->table('user')->where($condition)->select();

# 表达式查询
$sql = $model->table('user')->where(['id'=> ['eq', 100]])->select();
$sql = $model->table('user')->where(['id'=> 10])->select();
$sql = $model->table('user')->where(['id'=> ['neq', 100]])->select();
$sql = $model->table('user')->where(['id'=> ['gt', 100]])->select();
$sql = $model->table('user')->where(['id'=> ['lt', 100]])->select();
$sql = $model->table('user')->where(['id'=> ['egt', 100]])->select();
$sql = $model->table('user')->where(['id'=> ['elt', 100]])->select();
$sql = $model->table('user')->where(['name'=> ['like','Rumble%']])->select();

$map = [
    'a' => ['like',array('%thinkphp%','%tp'),'OR'],
    'b' => ['notlike',array('%thinkphp%','%tp'),'AND']
];
$sql = $model->table('user')->where($map)->select();

$sql = $model->table('user')->where(['id'=> ['between','1,8']])->select();
$sql = $model->table('user')->where(['id'=> ['between',[1,8]]])->select();
$sql = $model->table('user')->where(['id'=> ['not in',[1,8]]])->select();
$sql = $model->table('user')->where(['id'=> ['not in','88,77']])->select();
$sql = $model->table('user')->where(['id'=> ['in',[1,8]]])->select();
$sql = $model->table('user')->where(['id'=> ['in','88,77']])->select();
$sql = $model->table('user')->where(['id'=> ['exp','in (88,77)']])->select();

# 不同字段不同的查询条件
unset($map);
$map['status&title'] =array('1','rumble','_multi'=>true);// 把查询条件传入查询方法

unset($map);
$map['status&score&title'] =array('1',array('gt','0'),'rumble','_multi'=>true);
$sql = $model->table('user')->where($map)->select();

# 区间查询
$sql = $model->table('user')->where(['id'=>[['gt',1],['lt',10]]])->select();

$sql = $model->table('user')->where(['id'=>array(array('gt',3),array('lt',10), 'or')])->select();

$sql = $model->table('user')->where(['id'=> array(array('neq',6),array('gt',3),'and')])->select();

$sql = $model->table('user')
    ->where(['name'=> array(array('like','%a%'), array('like','%b%'), array('like','%c%'), 'rumble','or')])
    ->select();

# 组合查询
unset($map);
$map['id'] = array('neq',1);
$map['name'] = 'ok';
$map['_string'] = 'status=1 AND score>10';

unset($map);
$map['id'] = array('gt','100');
$map['_query'] = 'status=1&score=100&_logic=or';


unset($map);
$map['_complex'] = [
    'name' => array('like', '%rumble%'),
    'title' => array('like', '%rumble%'),
    '_logic' => 'or',
];
$map['id']  = array('gt',1);

# 复杂列操作
$column = [
    'user.imgurl' => 'banner_img',
    'user.title' => 'title',
    'concat(p_paths,",",p_id)' => 'level_paths',
    'length(concat(p_paths,",",p_id)) - length(REPLACE (concat(p_paths,",",p_id), ",", ""))' => 'level',
];
$sql = $model->table('user')->field($column)->where($map)->select();
//exit($sql);

# 动态查询
$sql = $model->table('user')->getByEmail('liu21st@gmail.com');

# selectAdd 通过select查询添加数据
$sql = $model->table('user')
    ->comment('this is my test')
    ->where('parentid=2')
    ->field('id,name,5')
    ->selectAdd('id,name,parentid','part');

exit($sql);


