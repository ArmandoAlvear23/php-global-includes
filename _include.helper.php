<?php

class GlobalHelper {

    // Private settings variable
    private $SETTINGS_PRIVATE;

    // Host variable
    private $SETTINGS_MYSQL_HOST;

    // Database credential variables
    private $SETTINGS_MYSQL_TEST_DB_USER;
    private $SETTINGS_MYSQL_TEST_DB_PASSWORD;

    private $SETTINGS_MYSQL_MAINTENANCE_USER;
    private $SETTINGS_MYSQL_MAINTENANCE_PASSWORD;

    //////////////////////////////////
    // Global Helper constructor
    //////////////////////////////////
    public function __construct() {

        // Get settings from config.ini file
        $this->SETTINGS_PRIVATE = parse_ini_file('../php_config/config.ini', true);

        // Get MySQL server host
        $this->SETTINGS_MYSQL_HOST = $this->SETTINGS_PRIVATE['mysql']['host'];

        // Get MySQL server user and password credentials for databases 
        $this->SETTINGS_MYSQL_TEST_DB_USER = $this->SETTINGS_PRIVATE['mysql']['test_db_user'];
        $this->SETTINGS_MYSQL_TEST_DB_PASSWORD = $this->SETTINGS_PRIVATE['mysql']['test_db_password'];

        $this->SETTINGS_MYSQL_MAINTENANCE_USER = $this->SETTINGS_PRIVATE['mysql']['maintenance_user'];
        $this->SETTINGS_MYSQL_MAINTENANCE_PASSWORD = $this->SETTINGS_PRIVATE['mysql']['maintenance_password'];
    }

    //////////////////////////////////
    // Get MySQL connection
    //////////////////////////////////
    public function getMySqlDBConn($database_name = 'test_db') {
        
        $host = '';
        $user = '';
        $password = '';

        try {

            // Get MySQL server host
            $host = $this->SETTINGS_MYSQL_HOST;

            // Get user and password credentials for selected database
            switch($database_name) {
                case 'test_db':
                    $user = $this->SETTINGS_MYSQL_TEST_DB_USER;
                    $password = $this->SETTINGS_MYSQL_TEST_DB_PASSWORD;
                    break;
                case '_maintenance':
                    $user = $this->SETTINGS_MYSQL_MAINTENANCE_USER;
                    $password = $this->SETTINGS_MYSQL_MAINTENANCE_PASSWORD;
                    break;
                default:
                    throw new Exception('Error: Undefined database name.');
                    break;
            }

            // Initialize MySQL server connection
            $link = mysqli_init();

            // Open MySQL server connection
            $link->real_connect($host, $user, $password, $database_name, NULL, NULL, NULL);

            // Return MySQL server connection
            return $link;

        } catch(Exception $e) {

            // Print MySQL error message
            $this->printMonospace($e->getMessage());
        }
    }

    //////////////////////////////////
    // Log MySQL error to database
    //////////////////////////////////
    public function logMySQLError($error, $query) {

        try {

            // Get MySQL connection link for maintenance database
            $link = $this->getMySqlDBConn('_maintenance');

            // Get the URI where the malformed MySQL query was executed
            $sql_uri = $link->real_escape_string($_SERVER['REQUEST_URI']);

            // Get MySQL error message for malformed MySQL query
            $sql_error = $link->real_escape_string($error);

            // Get malformed MySQL query
            $sql_query = $link->real_escape_string($query);

            // Prepare MySQL error query
            $query =
            "INSERT INTO sql_errors (uri, error, query) 
            VALUES ('".$sql_uri."', '".$sql_error."', '".$sql_query."');";

            // Execute MySQL query 
            if(!$sql_result = $link->query($query)) {

                throw new Exception($link->error);
            }
        } catch(Exception $e) {

            $this->printMonospace($e->getMessage());
        }

        // Close SQL database connection
        $link->close();
    }

    //////////////////////////////////
    // Get client's IP address
    //////////////////////////////////
    public function getClientIP() {
        
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {

            return $_SERVER['HTTP_X_FORWARDED_FOR'];

        } elseif($_SERVER['REMOTE_ADDR'] != '') {

            return $_SERVER['REMOTE_ADDR'];

        }
    }

    //////////////////////////////////
    // Redirect to new url with delay
    //////////////////////////////////
    public function redirectPage($url, $delay) {

        echo('<meta http-equiv="refresh" content="'.$delay.';url='.$url.'">');
    }

    //////////////////////////////////
    // Print message in monospace format
    //////////////////////////////////
    public function printMonospace($message) {

        echo('<pre>');
        print_r($message);
        echo('</pre>');
    }

}

?>