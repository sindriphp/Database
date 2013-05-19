## Database

Bifröst is **not an ORM** and it will **never be one**! It´s usage is to have a nice and secure way to write queries for an PDO supported database. If you don´t use database specific commands it will be, thanks to pdo, database independent.

### How Create a new database instance
To create a new Bifröst instance you need a Config object.
The Config object have different options like username, password, the DSN for the connection and the default DateTimeFormat for your Database. You will use the default PDO DSN string without username and password! You also can add PDO options with the method

**Don´t overwrite the ErrorMode (PDO::ATTR_ERRMODE)!**

    $config->addPdoOption('PDO::MYSQL_ATTR_INIT_COMMAND', 'SET NAMES utf8');

### Examples for PDO DSN: ###

*mysql ([PHP Documentation](http://php.net/manual/en/ref.pdo-mysql.connection.php))*

* mysql:host=localhost;dbname=testdb
* mysql:host=localhost;port=3307;dbname=testdb
* mysql:unix_socket=/tmp/mysql.sock;dbname=testdb

*sqlite ([PHP Documentation](http://www.php.net/manual/en/ref.pdo-sqlite.connection.php))*

* sqlite:/opt/databases/mydb.sq3
* sqlite::memory:

### Usage: ###

    	$config = new Config();
    	$config->setUsername('username');
    	$config->setPassword('secrect');
    	$config->setDsn('mysql:host=localhost;dbname=testdb');
    	$config->setDateTimeFormat('Y-m-d H:i:s'); // This is the default
    	$config->addPdoOption('PDO::MYSQL_ATTR_INIT_COMMAND', 'SET NAMES utf8');
    	$database = new Database($config);

**You should not switch between databases with a SQL command. This will cause troubles! Bifröst ist designed to be used with multiple database instances.**

Bifröst will not establish a connection to the database until it is really required to. That means it´s cheap to produce all your database objects -but still you should use a factory or DI-Container. I tried also to look at detail on the object count references to be long running process compatible without memory leaks.

For Example:

    	$masterConfig = new Config();
    	...
    	$slaveConfig = new Config();
    	...
    	$masterDatabase = new Database($masterConfig);
    	$slaveDatabase = new Database($slaveConfig);
    	
    	// or

    	$product1Config = new Config();
    	...
    	$product2Config = newConfig();
    	...
    	$product1Database = new Database($product1Config);
    	$product2Database = new Database($product2Config);
    	
### Run SQL ###
You can run directly SQL with the Bifröst instance. This is usefully for fixtures, init scripts and UnitTests which will create a new database or alter some tables, etc. You should use this method just for that case!

	...
	$db = new Database($config);
    	$createSQL = file_get_contents(__DIR__ . '/fixtures.sql');
    	$db->run($createSQL);
    	
### AddActionAfterConnect ###
Some PDO drivers (namely SQLite) support the possibility to [add](http://www.php.net/manual/de/pdo.sqlitecreateaggregate.php) functions which are available to SQL. This feature is currently marked as experimental but Bifröst supports it. It´s important to know, that the actions will be added after a connection is established and just can be added **before** a connection is established. The Method addActionAfterConnect awaits a Closure (PHP 5.3 compatible) which gets a PDO instance as first param.

Example:

    	$config = new Config();
    	$config->setUsername('user');
    	$config->setDsn('sqlite::memory:');
    	$sqliteDb = new Database($config);
    	
    	$sqliteDb->addActionAfterConnect(function (PDO $pdo) {
    		$pdo->sqliteCreateFunction('md5rev', function ($string) {
    			return strrev(md5($string));
    	    	}, 1);
    	});
    	
    	$sqliteDb->run(self::$createSQL);
    	
    	$row = $sqliteDb->query("SELECT username, md5rev(username) FROM  users 
								WHERE id = :ID")
    		->bindInt('ID', 1)
    		->fetchRow();



