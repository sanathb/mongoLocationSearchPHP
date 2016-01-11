<?php

/*
 * Class Mongo
 * 
 * @author Sanath Ballal
 * 
 * @description class to interact with data on mongodb
 * includes methods for location based search
 * 
 */

define('MONGO_URL', 'mongodb://ip:port/dbname');
define('MONGO_DB_NAME', 'dbname');

class Mongo {
	
	public $connection;
	public $database;
	public $collection;
	
	private $mongo_url = MONGO_URL;
	
	/*
	 * make connection to db
	 */
	function __construct($mongo_url = NULL) {
		
		//$connection = new Mongo('mongodb://<username>:<password>@xyz.mongolab.com:12345/dbname');

		if(empty($mongo_url)) {
			$this->connection = new Mongo($this->mongo_url);
		} else {
			$this->connection = new Mongo($mongo_url);
		}
		
		$this->selectDB(MONGO_DB_NAME);
		$this->selectCollection('collection_name');
	}
	
	
	/*
	 * select database
	 */
	function selectDB($dbname) {
		
		$this->database  = $this->connection->selectDB($dbname);		
	}
	
	/*
	 * select the collection on db 
	 */
	function selectCollection($collection_name) {
		
		$this->collection = $this->database->selectCollection($collection_name);
	}
	
	
	/*
	 * To insert documents(records)
	 */
	function insertDocument($document = array()) {
		
		$this->collection->insert($document);		
	}
	
	
	/*
	 * To insert multiple documents(records)
	 */
	function insertBatchDocuments($documents  = array()) {
		
		$this->collection->batchInsert($documents);
	}
	
	
	/*
	 * Find the documents / records
	 */
	function getDocuments($condition_array = array()) {
		
		return $this->collection->find($condition_array);
	}
	
	
	/*
	 * Update the documents/records
	 */
	function updateDocuments($condition_array = array(), $new_data = array()) {
		
		$this->collection->update($condition_array, $new_data);
		
	}
	
	
	/*
	 * Remove the documents/records
	 */
	function deleteDocuments($condition_array = array()) {
		
		$this->collection->remove($condition_array);
	}
	
	
	/*
	 * Remove only a single document / record 
	 */
	function deleteDocument($condition_array = array()) {
		
		$this->collection->remove($condition_array, array("justOne" => true));
		
	}
	
	
	/*
	 * To get documents based on locations.
	 * 
	 * @params $location the location array in the format (longitude, latitude)
	 * 
	 * @radius the radius within which the search has to be performed (max radius is set to 10000 meters)
	 * 
	 * @more_conditions more conditions if any
	 */
	function getDocumentsByLocation($location, $radius = 100, $more_conditions = array()) {
		
		$this->collection->ensureIndex(array("loc" => "2dsphere"));
		
		$location = array("loc"=>array('$near'=>array('$geometry'=>array("type"=>"Point",
                                                                         "coordinates"=>$location,                                                                 
                                                                         ),
                                                      
                                                      //'$maxDistance'=>1000,
                                                     )
                                      )
                         );
		
		
		if(!empty($radius)) {
			
			$location['loc']['$near']['$maxDistance'] = ($radius<10000?$radius:10000);
		}
		
		if(!empty($more_conditions)) {
			$more_conditions['loc'] = $location['loc'];
			$location = $more_conditions;
		}
		
		
		return $this->collection->find($location);
		
	}
	
}
