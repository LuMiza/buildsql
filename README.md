## Mysql的curd操作sql构建
Author: Rumble

Url:  https://github.com/LuMiza/sql_build


### 开发目的
* 由于很多sql构建类需要建立mysql连接，但有的时候我们只想要sql语句，不想与数据库进行连接，这样有一个纯粹的sql构建工具就尤为重要了。

* 当前sql构建工具基于thinkphp3.2进行二开，大部分sql操作和thinkphp3.2的操作方式一样

### 操作说明
* 使用该工具无需配置任何数据库信息，不会建立mysql连接
* Sql类支持单例创建对象`Sql::getInstance();`，或者直接`new Sql()`
* 详细操作说明请查看`index.php`，或者thinkphp3.2手册

### 不支持的操作方式

* 不支持对数据库字段验证，[ 不建立mysql连接，无法取得表结构信息，因此无法验证 ]
* 不支持对象创建新的数据对象，即无`creat`方法，只能用数组的形式
* 不支持连贯操作中的`cache`方法
* 在`add`方法之前调用`field`方法不支持字段过滤
* 在`save`方法之前调用`field`方法不支持字段过滤,但是支持数据过滤
* 删除数据不支持`$sql->delete('1,2,5');`这种用法，即`delete`方法不能带参数
* `select`函数不支持带参数
* 不支持表映射到类，记录映射到对象
* 不支持字段映射,即
* 不支持连贯操作中的`relation`方法

### 特别鸣谢

* 感谢thinkphp的开源
