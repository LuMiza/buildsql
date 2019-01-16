## SQL statement for constructing operation database [ CURD ]
Author: Rumble
Url:  https://github.com/lumiza/buildsql

### About Language
* Chinese Readme File: CHINESE.md


### Package Install
* `composer require lumiza/buildsql`

### Development purpose
* Because many SQL build classes need to establish MySQL connections, but sometimes we just want SQL statements, do not want to connect to the database, so it is particularly important to have a pure SQL build tool.

* Current SQL building tools are based on ThinkPHP 3.2, and most SQL operations are the same as ThinkPHP 3.2 model operations.

### Instructions
* Using this tool, you do not need to configure any database information, and you will not establish MySQL connections.
* The Sql class supports the singleton creation of objects `Sql:: getInstance ();`, or directly `new Sql ()`.

* For detailed instructions, see `demo.php`, or ThinkPHP 3.2 operation model instructions


### Unsupported Operations

* Validation of database fields is not supported. [ Table structure information cannot be obtained without establishing MySQL connections, and therefore cannot be validated ]
* Objects are not supported to create new data objects, that is, there is no `create` method, only in the form of arrays.

* The `cache` method in coherent operations is not supported.

* Calling the `field`method before the `add` method does not support field filtering.
* Calling the `field`method before the `save` method does not support field filtering, but does support data filtering.
* Deleting data does not support `sql-> delete ('1,2,5'); `This usage means that the `delete`method cannot take parameters
* The `select` function does not support parameters
* Mapping tables to classes and records to objects is not supported
* Field mapping is not supported
* The `relation` method in coherent operations is not supported

### Special Thanks


* Thank you for thinkphp's open source
