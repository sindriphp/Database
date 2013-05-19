## Query
The Query class use a fluent interface. It´s designed to be nice, logical and natural. It enforces strong typing, so you need to parse and cast your values. Internally it uses the PreparedStatement from PDO. In that combination, you should be not vulnerable to SQL Injection and type problems with your SQL ('1' != 1). Look at **Examples** if you wan´t to see usage examples.

### How to use Bifröst to query your database ###
You need a Bifröst instance and then you call the method "query" with your SQL query. Bifröst will automatically handle the rest for you (like establish connection to the database, is this a new query?, can I reuse this already prepared query, etc).

#### Example ####
    	...
    	$database = new Database($config);
    	$database->query("SELECT * FROM `users`")
    		->fetchAll();
    		
### Usage ###
You define your query with placeholders. A placeholder should be uppercase and start with a double dash like:   :ID, :START_DATE, :END_DATE, :USERNAME.

	SELECT * FROM `users` WHERE id = :ID
	
Now you can bind values to the :ID placeholder with the bind methods. **Caution**: You don´t use the double dash in the key with the bind methods! )

    	$database = new Database($config);
    	$database->query('SELECT * FROM `users` WHERE id = :ID')
    		->bindInt('ID', 1)
    		
#### Bind methods ####
	
The bind methods needs the key they should binded to and the value which should be bindet. The value must be typesafe or else it will throw an \Sindri\Database\Exception\InvalidArgumentException 

    	bindInt($key, $value)
    	bindFloat($key, $value)
    	bindString($key, $value)
    	
If your query is in prepared state (Fetch-Method, execute(), prepare(...) ) and you try to bind a key, which is not known to it, it will throw an \Sindri\Database\Exception\InvalidArgumentException.
    	
### BindArray method ###

The bind array method does some internal handling. It determines the type of the values and bind with the correct method. It´s also possible to bind an array with no elements. This will not break your SQL query if you write your query in the correct syntax. The syntax is

    	"SELECT * FROM `users` WHERE id IN (:IDS)"
    	
It´s important to understand how Array-Binding works. If you bind an array with 4 entries to the key IDS Bifröst will transform it in something like ID_0, ID_1, ID_2, ID_3 and bind its value to it. When the query get´s in prepared state (Fetch-Method, execute(), prepare(...) ), then you get an \Sindri\Database\Exception\InvalidArgumentException, if you try to reuse the query with a differend sized array!
 
    	bindArray($key, $value)

### BindDate methods ###

There are two bindDate methods. One will bind your DateTime object with **your** timezone setting the other one will bind your DateTime object **converted** to the UTC timezone. It is possible to modfy for the **complete** query the Date-Time-Format without changing the default Date-Time-Format of your Database. If you call setDateTimeFormat, **this will change the format for the whole life-cycle** (think about prepared queries!)

    	setDateTimeFormat($format)

Will use your timezone settings

    	bindDate($key, DateTime $date)

Will convert the date in UTC **(does not modify your object!)**

    	bindDateUTC($key, DateTime $date)


### Execute method ###

This method is for UPDATE, INSERST, etc. which will not fetch value from the database. This method will establish a connection. After calling execute you can´t register any new "actionAfterConnect". The query is also be prepared now. This is important for Array-Binding. See "BindArray method" for more informationen about that topic.

    	execute()

### Prepare method ###

With the prepare method, you can prepare your sql queries for later usage. The syntax may looks a bit weired at first, so.

Say we want a query which selects users which ids in the array(1,2,3,4) and a register date later then 2013-01-01. We need this query more then one time and want to prepare it.

    	$userQuery = $database->query("SELECT * FROM `users` 
    		WHERE id IN (:IDS) and `registered` > :STARTDATE")
    		->prepare(array(
    			array('IDS', 4), // Important! bindArray with key IDS and 4 entries
    			'STARTDATE' // The key STARTDATE is now known
    		));
	// now we can run the prepared query like
	$userQuery->bindArray('IDS', array(1,2,3,4)
		->bindDateUTC('STARTDATE', new Date('2013-01-01'))
		->fetchAll();

Puh... the method:

    	prepare(array $bindings)

### Fetch methods ###

How you will get your query results.

This will return all the result in assoc

    	fetchAll()

This will just return the first value of the first row. If there is no result, it will return the $nullValue (default '')

    	fetchValue($nullValue = '')
    	
Thi will return the $column as an array

    	fetchColumn($column = 0)

This will return a row in assoc

    	fetchRow()
