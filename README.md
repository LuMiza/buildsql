## Mysql的curd操作sql构建

* 当前sql构建工具基于thinkphp3.2进行二开，大部分sql操作和thinkphp3.2的操作方式一样

### 操作说明

* `Sql::getInstance('think');`在类的创建时候不支持表名前缀，因此，操作前缀操作无效
* Sql类支持单例创建对象`Sql::getInstance();`，或者直接`new Sql()`

### 不支持的操作方式

* 不支持对象创建新的数据对象，即无`creat`方法，只能用数组的形式
* 不支持连贯操作中的`cache`方法
* 在`add`方法之前调用`field`方法不支持字段过滤
* 在`save`方法之前调用`field`方法不支持字段过滤,但是支持数据过滤
