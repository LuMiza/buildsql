<?php
/**
 *
 * 实现了数据库操作sql构建
 * @author Rumble
 * @date  2019-1-14
 */
namespace BuildSql;

class Sql {
    //单例模式
    private static $instance = null;
    // mysql数据库操作语句构建驱动对象
    protected $db               =   null;
    // 数据表前缀
    protected $tablePrefix      =   '';
    // 数据表名（不包含表前缀）
    protected $tableName        =   '';
    // 实际数据表名（包含表前缀）
    protected $trueTableName    =   '';
    // 字段信息
    protected $fields           =   array();
    // 数据信息
    protected $data             =   array();
    // 查询表达式参数
    protected $options          =   array();
    protected $_scope           =   array();  // 命名范围定义
    // 链操作方法列表
    protected $methods          =   array('strict','order','alias','having','group','lock','distinct','auto','filter','validate','result','token','index','force');

    /**
     * 架构函数
     * 取得DB类的实例对象 字段检查
     * @access public
     */
    public function __construct() {
        $this->db = new SqlBuild();
    }

    /**
     * 单例模式
     * @return Model|null
     */
    public static function getInstance()
    {
        if (! (self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    public function parse_name($name, $type=0) {
        if ($type) {
            return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match){return strtoupper($match[1]);}, $name));
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }

    /**
     * 设置数据对象的值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name,$value) {
        // 设置数据对象属性
        $this->data[$name]  =   $value;
    }

    /**
     * 获取数据对象的值
     * @access public
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name) {
        return isset($this->data[$name])?$this->data[$name]:null;
    }

    /**
     * 检测数据对象的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /**
     * 销毁数据对象的值
     * @access public
     * @param string $name 名称
     * @return void
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     */
    public function __call($method,$args) {
//        echo $method . '  ';
        if(in_array(strtolower($method),$this->methods,true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] =   $args[0];
            return $this;
        }elseif(in_array(strtolower($method),array('count','sum','min','max','avg'),true)){
            // 统计查询的实现
            $field =  isset($args[0])?$args[0]:'*';
            return $this->getField(strtoupper($method).'('.$field.') AS sql_'.$method);
        }elseif(strtolower(substr($method,0,5))=='getby') {
            // 根据某个字段获取记录
            $field   =   $this->parse_name(substr($method,5));
            $where[$field] =  $args[0];
            return $this->where($where)->find();
        }elseif(strtolower(substr($method,0,10))=='getfieldby') {
            // 根据某个字段获取记录的某个值
            $name   =   $this->parse_name(substr($method,10));
            $where[$name] =$args[0];
            return $this->where($where)->getField($args[1]);
        }elseif(isset($this->_scope[$method])){// 命名范围的单独调用支持
            return $this->scope($method,$args[0]);
        }else{
            return;
        }
    }

    /**
     * 获取一条记录的某个字段值
     * @access public
     * @param string $field  字段名
     * @param string $spea  字段数据间隔符号 NULL返回数组
     * @return mixed
     */
    public function getField($field,$sepa=null) {
        $options['field']       =   $field;
        $options                =   $this->_parseOptions($options);
        $field                  =   trim($field);
        if(strpos($field,',') && false !== $sepa) { // 多字段
            if(!isset($options['limit'])){
                $options['limit']   =   is_numeric($sepa)?$sepa:'';
            }
            $resultSet          =   $this->db->select($options);
            if(!empty($resultSet)) {
                if(is_string($resultSet)){
                    return $resultSet;
                }
                $_field         =   explode(',', $field);
                $field          =   array_keys($resultSet[0]);
                $key1           =   array_shift($field);
                $key2           =   array_shift($field);
                $cols           =   array();
                $count          =   count($_field);
                foreach ($resultSet as $result){
                    $name   =  $result[$key1];
                    if (2 == $count) {
                        $cols[$name]   =  $result[$key2];
                    }else{
                        $cols[$name]   =  is_string($sepa)?implode($sepa,array_slice($result,1)):$result;
                    }
                }
                return $cols;
            }
        }else{   // 查找一条记录
            // 返回数据个数
            if(true !== $sepa) {// 当sepa指定为true的时候 返回所有数据
                $options['limit']   =   is_numeric($sepa)?$sepa:1;
            }
            $result = $this->db->select($options);
            if (! empty($result)) {
                if(is_string($result)){
                    return $result;
                }
                if(true !== $sepa && 1==$options['limit']) {
                    $data   =   reset($result[0]);
                    return $data;
                }
                foreach ($result as $val){
                    $array[]    =   $val[$field];
                }
                return $array;
            }
        }
        return null;
    }

    /**
     * 对保存到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @return boolean
     */
     protected function _facade($data) {

        // 检查数据字段合法性
        if(!empty($this->fields)) {
            if(!empty($this->options['field'])) {
                $fields =   $this->options['field'];
                unset($this->options['field']);
                if(is_string($fields)) {
                    $fields =   explode(',',$fields);
                }    
            }else{
                $fields =   $this->fields;
            }        
            foreach ($data as $key=>$val){
                if(!in_array($key,$fields,true)){
                    unset($data[$key]);
                }elseif(is_scalar($val)) {
                    // 字段类型检查 和 强制转换
                    $this->_parseType($data,$key);
                }
            }
        }
        // 安全过滤
        if(!empty($this->options['filter'])) {
            $data = array_map($this->options['filter'],$data);
            unset($this->options['filter']);
        }
        return $data;
     }

    /**
     * 新增数据
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @param boolean $replace 是否replace
     * @return mixed
     */
    public function add($data='',$options=array(),$replace=false) {
        if (! $this->isSetTable()) {
            return false;
        }
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     = array();
            }else{
                return false;
            }
        }
        // 数据处理
        $data       =   $this->_facade($data);
        // 分析表达式
        $options    =   $this->_parseOptions($options);

        // 写入数据到数据库
        $result = $this->db->insert($data,$options,$replace);
        return $result;
    }

    public function addAll($dataList,$options=array(),$replace=false){
        if (! $this->isSetTable()) {
            return false;
        }
        if(empty($dataList)) {
            return false;
        }
        // 数据处理
        foreach ($dataList as $key=>$data){
            $dataList[$key] = $this->_facade($data);
        }
        // 分析表达式
        $options =  $this->_parseOptions($options);
        // 写入数据到数据库
        $result = $this->db->insertAll($dataList,$options,$replace);
        return $result;
    }

    /**
     * 通过Select方式添加记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $options 表达式
     * @return boolean
     */
    public function selectAdd($fields='',$table='',$options=array()) {
        if (! $this->isSetTable()) {
            return false;
        }
        // 分析表达式
        $options =  $this->_parseOptions($options);
        // 写入数据到数据库
        if(false === $result = $this->db->selectInsert($fields?:$options['field'],$table?:$this->getTableName(),$options)){
            // 数据库插入操作失败
            return false;
        }else {
            // 插入成功
            return $result;
        }
    }

    /**
     * 保存数据
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return boolean
     */
    public function save($data='',$options=array()) {
        if (! $this->isSetTable()) {
            return false;
        }
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data           =   $this->data;
                // 重置数据
                $this->data     =   array();
            }else{
                return false;
            }
        }
        // 数据处理
        $data       =   $this->_facade($data);
        if(empty($data)){
            // 没有数据则不执行
            return false;
        }
        // 分析表达式
        $options    =   $this->_parseOptions($options);
        $result     =   $this->db->update($data,$options);
        return $result;
    }

    /**
     * 删除数据
     * @access public
     * @param mixed $options 表达式
     * @return mixed
     */
    public function delete($options=array()) {
        if (! $this->isSetTable()) {
            return false;
        }
        if ($options) {
            return false;
        }
        if(empty($options) && empty($this->options['where'])) {
            // 如果删除条件为空 则删除当前数据对象所对应的记录
            return $this->db->delete($this->options);
        }
        // 分析表达式
        $options =  $this->_parseOptions($options);
        if(empty($options['where'])){
            // 如果条件为空 不进行删除操作 除非设置 1=1
            return false;
        }
        $result  =    $this->db->delete($options);
        return $result;
    }

    /**
     * 查询数据集
     * @access public
     * @param array $options 表达式参数
     * @return mixed
     */
    public function select($options=array()) {
        if (! $this->isSetTable()) {
            return false;
        }
        if ($options) {
            return false;
        }
        // 分析表达式
        $options    =  $this->_parseOptions($options);
        $resultSet  = $this->db->select($options);
        return $resultSet;
    }

    /**
     * 得到完整的数据表名
     * @return string
     */
    protected function getTableName()
    {
        if(empty($this->trueTableName)) {
            $tableName  = !empty($this->tablePrefix) ? $this->tablePrefix : '';
            if(!empty($this->tableName)) {
                $tableName .= $this->tableName;
            }else{
                $tableName .= $this->parse_name($this->tableName);
            }
            $this->trueTableName    =   strtolower($tableName);
        }
        return $this->trueTableName;
    }

    /**
     * 分析表达式
     * @access protected
     * @param array $options 表达式参数
     * @return array
     */
    protected function _parseOptions($options=array()) {
        if(is_array($options))
            $options =  array_merge($this->options,$options);

        if (! isset($options['table'])){
            // 自动获取表名
            $options['table']   =   $this->getTableName();
//            $fields             =   $this->fields;
        }
/*        else{
            // 指定数据表 则重新获取字段列表 但不支持类型检测
//            $fields             =   $this->getDbFields();
        }*/

        // 数据表别名
        if(!empty($options['alias'])) {
            $options['table']  .=   ' '.$options['alias'];
        }
        // 记录操作的模型名称
//        $options['model']       =   $this->name;

        // 字段类型验证
        /*if(isset($options['where']) && is_array($options['where']) && !empty($fields) && !isset($options['join'])) {
            // 对数组查询条件进行字段类型检查
            foreach ($options['where'] as $key=>$val){
                $key            =   trim($key);
                if(in_array($key,$fields,true)){
                    if(is_scalar($val)) {
                        $this->_parseType($options['where'],$key);
                    }
                }elseif(!is_numeric($key) && '_' != substr($key,0,1) && false === strpos($key,'.') && false === strpos($key,'(') && false === strpos($key,'|') && false === strpos($key,'&')){

                    unset($options['where'][$key]);
                }
            }
        }*/
        // 查询过后清空sql表达式组装 避免影响下次查询
        $this->options  =   array();
        // 表达式过滤
        return $options;
    }

    /**
     * 数据类型检测
     * @access protected
     * @param mixed $data 数据
     * @param string $key 字段名
     * @return void
     */
    protected function _parseType(&$data,$key) {
        if(!isset($this->options['bind'][':'.$key]) && isset($this->fields['_type'][$key])){
            $fieldType = strtolower($this->fields['_type'][$key]);
            if(false !== strpos($fieldType,'enum')){
                // 支持ENUM类型优先检测
            }elseif(false === strpos($fieldType,'bigint') && false !== strpos($fieldType,'int')) {
                $data[$key]   =  intval($data[$key]);
            }elseif(false !== strpos($fieldType,'float') || false !== strpos($fieldType,'double')){
                $data[$key]   =  floatval($data[$key]);
            }elseif(false !== strpos($fieldType,'bool')){
                $data[$key]   =  (bool)$data[$key];
            }
        }
    }

    /**
     * 查询数据
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function find($options=array()) {
        if (! $this->isSetTable()) {
            return false;
        }
        // 总是查找一条记录
        $options['limit']   =   1;
        // 分析表达式
        $options            =   $this->_parseOptions($options);
        $resultSet          =   $this->db->select($options);
        return $resultSet;
    }

    /**
     * 字段值增长
     * @access public
     * @param string $field  字段名
     * @param integer $step  增长值
     * @return boolean
     */
    public function setInc($field,$step=1) {
        if (! $this->isSetTable()) {
            return false;
        }
        return $this->setField($field,array('exp',$field.'+'.$step));
    }

    /**
     * 字段值减少
     * @access public
     * @param string $field  字段名
     * @param integer $step  减少值
     * @return boolean
     */
    public function setDec($field,$step=1) {
        if (! $this->isSetTable()) {
            return false;
        }
        return $this->setField($field,array('exp',$field.'-'.$step));
    }

    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     * @access public
     * @param string|array $field  字段名
     * @param string $value  字段值
     * @return boolean
     */
    public function setField($field,$value='') {
        if (! $this->isSetTable()) {
            return false;
        }
        if(is_array($field)) {
            $data           =   $field;
        }else{
            $data[$field]   =   $value;
        }
        return $this->save($data);
    }

    /**
     * 解析SQL语句
     * @access public
     * @param string $sql  SQL指令
     * @param boolean $parse  是否需要解析SQL
     * @return string
     */
    protected function parseSql($sql,$parse) {
        // 分析表达式
        if(true === $parse) {
            $options =  $this->_parseOptions();
            $sql    =   $this->db->parseSql($sql,$options);
        }elseif(is_array($parse)){ // SQL预处理
            $parse  =   array_map(array($this->db,'escapeString'),$parse);
            $sql    =   vsprintf($sql,$parse);
        }else{
            $sql    =   strtr($sql,array('__TABLE__'=>$this->getTableName(),'__PREFIX__'=>$this->tablePrefix));
            $prefix =   $this->tablePrefix;
            $sql    =   preg_replace_callback(
                "/__([A-Z0-9_-]+)__/sU",
                function($match) use($prefix){
                    return $prefix.strtolower($match[1]);
                },
                $sql
            );
        }
//        $this->db->setModel($this->name);
        return $sql;
    }

    /**
     * 设置数据对象值
     * @access public
     * @param mixed $data 数据
     * @return Model
     */
    public function data($data=''){
        if('' === $data && !empty($this->data)) {
            return $this->data;
        }
        if(is_object($data)){
            $data   =   get_object_vars($data);
        }elseif(is_string($data)){
            parse_str($data,$data);
        }
        $this->data = $data;
        return $this;
    }

    /**
     * 指定当前的数据表
     * @access public
     * @param mixed $table
     * @return Model
     */
    public function table($table) {
        $prefix =   $this->tablePrefix;
        if(is_array($table)) {
            $this->options['table'] =   $table;
        }elseif(!empty($table)) {
            //将__TABLE_NAME__替换成带前缀的表名
            $table  = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function($match) use($prefix){ return $prefix.strtolower($match[1]);}, $table);
            $this->options['table'] =   $table;
        }
        return $this;
    }

    /**
     * USING支持 用于多表删除
     * @access public
     * @param mixed $using
     * @return Model
     */
    public function using($using){
        $prefix =   $this->tablePrefix;
        if(is_array($using)) {
            $this->options['using'] =   $using;
        }elseif(!empty($using)) {
            //将__TABLE_NAME__替换成带前缀的表名
            $using  = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function($match) use($prefix){ return $prefix.strtolower($match[1]);}, $using);
            $this->options['using'] =   $using;
        }
        return $this;
    }

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join
     * @param string $type JOIN类型
     * @return Model
     */
    public function join($join,$type='INNER') {
        $prefix =   $this->tablePrefix;
        if(is_array($join)) {
            foreach ($join as $key=>&$_join){
                $_join  =   preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function($match) use($prefix){ return $prefix.strtolower($match[1]);}, $_join);
                $_join  =   false !== stripos($_join,'JOIN')? $_join : $type.' JOIN ' .$_join;
            }
            $this->options['join']      =   $join;
        }elseif(!empty($join)) {
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $join  = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function($match) use($prefix){ return $prefix.strtolower($match[1]);}, $join);
            $this->options['join'][]    =   false !== stripos($join,'JOIN')? $join : $type.' JOIN '.$join;
        }
        return $this;
    }

    /**
     * 查询SQL组装 union
     * @access public
     * @param mixed $union
     * @param boolean $all
     * @return Model
     */
    public function union($union,$all=false) {
        if(empty($union)) return $this;
        if($all) {
            $this->options['union']['_all']  =   true;
        }
        if(is_object($union)) {
            $union   =  get_object_vars($union);
        }
        // 转换union表达式
        if(is_string($union) ) {
            $prefix =   $this->tablePrefix;
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $options  = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function($match) use($prefix){ return $prefix.strtolower($match[1]);}, $union);
        }elseif(is_array($union)){
            if(isset($union[0])) {
                if (! isset($this->options['union'])) {
                    $this->options['union'] = [];
                }
                $this->options['union']  =  array_merge($this->options['union'],$union);
                return $this;
            }else{
                $options =  $union;
            }
        }else{
            E(L('_DATA_TYPE_INVALID_'));
        }
        $this->options['union'][]  =   $options;
        return $this;
    }

    /**
     * 指定查询字段 支持字段排除
     * @access public
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return Model
     */
    public function field($field,$except=false){
        if(true === $field) {// 获取全部字段
            $field      =  '*';
        }elseif($except) {// 字段排除
            if(is_string($field)) {
                $field  =  explode(',',$field);
            }
        }
        $this->options['field']   =   $field;
        return $this;
    }

    /**
     * 调用命名范围
     * @access public
     * @param mixed $scope 命名范围名称 支持多个 和直接定义
     * @param array $args 参数
     * @return Model
     */
    public function scope($scope='',$args=NULL){
        if('' === $scope) {
            if(isset($this->_scope['default'])) {
                // 默认的命名范围
                $options    =   $this->_scope['default'];
            }else{
                return $this;
            }
        }elseif(is_string($scope)){ // 支持多个命名范围调用 用逗号分割
            $scopes         =   explode(',',$scope);
            $options        =   array();
            foreach ($scopes as $name){
                if(!isset($this->_scope[$name])) continue;
                $options    =   array_merge($options,$this->_scope[$name]);
            }
            if(!empty($args) && is_array($args)) {
                $options    =   array_merge($options,$args);
            }
        }elseif(is_array($scope)){ // 直接传入命名范围定义
            $options        =   $scope;
        }
        
        if(is_array($options) && !empty($options)){
            $this->options  =   array_merge($this->options,array_change_key_case($options));
        }
        return $this;
    }

    /**
     * 指定查询条件 支持安全过滤
     * @access public
     * @param mixed $where 条件表达式
     * @param mixed $parse 预处理参数
     * @return Model
     */
    public function where($where,$parse=null){
        if(!is_null($parse) && is_string($where)) {
            if(!is_array($parse)) {
                $parse = func_get_args();
                array_shift($parse);
            }
            $parse = array_map(array($this->db,'escapeString'),$parse);
            $where =   vsprintf($where,$parse);
        }elseif(is_object($where)){
            $where  =   get_object_vars($where);
        }
        if(is_string($where) && '' != $where){
            $map    =   array();
            $map['_string']   =   $where;
            $where  =   $map;
        }        
        if(isset($this->options['where'])){
            $this->options['where'] =   array_merge($this->options['where'],$where);
        }else{
            $this->options['where'] =   $where;
        }
        
        return $this;
    }

    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return Model
     */
    public function limit($offset,$length=null){
        if(is_null($length) && strpos($offset,',')){
            list($offset,$length)   =   explode(',',$offset);
        }
        $this->options['limit']     =   intval($offset).( $length? ','.intval($length) : '' );
        return $this;
    }

    /**
     * 查询注释
     * @access public
     * @param string $comment 注释
     * @return Model
     */
    public function comment($comment){
        $this->options['comment'] =   $comment;
        return $this;
    }

    /**
     * 参数绑定
     * @access public
     * @param string $key  参数名
     * @param mixed $value  绑定的变量及绑定参数
     * @return Model
     */
    public function bind($key,$value=false) {
        if(is_array($key)){
            $this->options['bind'] =    $key;
        }else{
            $num =  func_num_args();
            if($num>2){
                $params =   func_get_args();
                array_shift($params);
                $this->options['bind'][$key] =  $params;
            }else{
                $this->options['bind'][$key] =  $value;
            }        
        }
        return $this;
    }

    /**
     * 设置模型的属性值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return Model
     */
    public function setProperty($name,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
        return $this;
    }

    /**
     * 判断是否设置了table
     * @return bool
     */
    private function isSetTable()
    {
        return (!isset($this->options['table']) || !$this->options['table'])? false: true;
    }

}
