<?php

/**
 *
 * Database class:
 *
 * - Uses the config file for the database credentials.
 * - Connects with a database.
 * - Checks if a connection can be made.
 * - Passes values by reference.
 * - Prepares statements with bind parameters.
 *
 */

namespace application\classes\database;

use application\config;

if(!class_exists('Database')):

    class Database
    {

		protected $dbName;

		/**
		 * Database constructor.
		 * @param $dbName
         */

		public function __construct( $dbName )
		{
			$this->dbName = $dbName;
		}

		/**
         * @return \mysqli
         */
		
        public function dbConnect()
        {

            $dbArray = config\dbCredentials();

			$dbName = ( !empty( $this->dbName ) ? $this->dbName : $dbArray['dbname'] );

			try {
				$mysqli = new \mysqli($dbArray['dbhost'], $dbArray['dbuser'], $dbArray['dbpass'], $dbName);
            }
            catch (\Exception $e)
            {
				exit();
			}
			return($mysqli);
		}

		/**
		 * @return \mysqli
         */

		public function checkDbConnection()
		{
			$dbArray = config\dbCredentials();

			try {
				$mysqli = new \mysqli($dbArray['dbhost'], $dbArray['dbuser'], $dbArray['dbpass']);
			}
			catch (\Exception $e)
			{
				exit();
			}
			return($mysqli);
		}

        /**
         * @param $array
         * @return array
         */

		public function referenceValues($array)
        {
			$refs = array();

			foreach ($array as $key => $value):

				$refs[$key] = &$array[$key];

			endforeach;

			return $refs; 
		}
    }
endif;
?>