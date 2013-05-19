## Examples

Assumed the following table (users)

| id	| username 	| passwort 		| registered				|
|----|---------------|---------------|------------------------|
| 1	|  bill			|  god			| 2013-01-02 15:08:23 	|
| 2	|  jack 			|  love			| 2012-06-12 11:36:42	|
| 3	|  jones 		|  secret	     	| 2013-03-07 18:44:51 	|
| 4	|  seb 			|  leet  			| 2013-05-01 13:22:01 	|
| 5	|  peter 			|  foo   			| 2012-04-02 01:21:03 	|
| 6	|  hans 			|  nerd			| 2012-10-12 07:38:33 	|

SQL (saved as example.sql)

	CREATE TABLE [users] (
		[id] INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
		[username] varCHAR(50)  UNIQUE NULL,
		[password] varCHAR(32)  NULL,
		[registered] DATETIME  NULL
	);

	INSERT INTO users (id, username, password, registered) VALUES (1, 'bill', 'god', '2013-01-02 15:08:23');
	INSERT INTO users (id, username, password, registered) VALUES (2, 'jack', 'love', '2012-06-12 11:36:42');
	INSERT INTO users (id, username, password, registered) VALUES (3, 'jones', 'secret', '2013-03-07 18:44:51');
	INSERT INTO users (id, username, password, registered) VALUES (4, 'seb', 'leet', '2013-05-01 13:22:01');
	INSERT INTO users (id, username, password, registered) VALUES (5, 'peter', 'foo', '2012-04-02 01:21:03');
	INSERT INTO users (id, username, password, registered) VALUES (6, 'hans', 'nerd', '2012-10-12 07:38:33');
    	
Initialize Code
	
	use \Sindri\Database\Config;
	use \Sindri\Database\Database;

	$config = new Config();
	$config->setDsn('sqlite::memory:');
	$db = new Database($config);
	$db->run(file_get_contents(__DIR__ . '/example.sql'));
    	
Simple Query

	$result = $db->query('SELECT * FROM `users` WHERE id = :ID')
	    ->bindInt('ID', 2)
	    ->fetchRow();
	var_dump($result);

	// Result

	array(4) {
	  'id' =>
	  string(1) "2"
	  'username' =>
	  string(4) "jack"
	  'password' =>
	  string(4) "love"
	  'registered' =>
	  string(19) "2012-06-12 11:36:42"
	}


More will come =)

