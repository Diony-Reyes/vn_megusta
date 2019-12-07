<?php
    require_once 'db.php';

    class __Database
    {
        // connector 
        private static function connectorDB() {
            $db = new db();
            return $db->connectionDB();
        }

        public static function displayErrors()
        {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }


        public static function __insert($table_name, $data)
        {
            $fields = array_keys($data);
            $sql = "INSERT INTO " . $table_name . "
            (`" . implode('`,`', $fields) . "`)
            VALUES('" . implode("','", $data) . "')";

            // print_r($sql);die();
            return mysqli_query(__Database::__dbConnection(), $sql);
        }

        public static function getLastID($tbl) {
            $sql = "SELECT id FROM {$tbl} where 1 order by id desc limit 1";
            $qry = mysqli_query(__Database::__dbConnection(), $sql);
            return  mysqli_fetch_array($qry);
        }
    
        public static function exists($tbl, $id) {
            $sql = "SELECT id FROM {$tbl} where id = {$id} order by id desc limit 1";
            $qry = mysqli_query(__Database::__dbConnection(), $sql);
            $r = mysqli_fetch_array($qry);
            return  is_array($r) && !empty($r);
        }

        public static function __update($table_name, $data, $where_clause = '')
        {

            // check for optional where clause
            $whereSQL = '';
            if (!empty($where_clause)) {
                // check to see if the 'where' keyword exists
                if (substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE') {
                    // not found, add key word
                    $whereSQL = " WHERE " . $where_clause;
                } else {
                    $whereSQL = " " . trim($where_clause);
                }
            }
            // start the actual SQL statement
            $sql = "UPDATE " . $table_name . " SET ";

            // loop and build the column /
            $sets = array();
            foreach ($data as $column => $value) {
                $sets[] = "`" . trim($column) . "` = '" . $value . "'";
            }
            $sql .= implode(', ', $sets);

            // append the where statement
            $sql .= $whereSQL;


            // print_r($sql);die();
            // run and return the query result
            return mysqli_query(__Database::__dbConnection(), $sql);

        }

        public static function __delete($table_name, $where_clause = '')
        {
            // check for optional where clause
            $whereSQL = '';
            if (!empty($where_clause)) {
                // check to see if the 'where' keyword exists
                if (substr(strtoupper(trim($where_clause)), 0, 5) != 'WHERE') {
                    // not found, add keyword
                    $whereSQL = " WHERE " . $where_clause;
                } else {
                    $whereSQL = " " . trim($where_clause);
                }
            }
            // build the query
            $sql = "DELETE FROM " . $table_name . $whereSQL;

            try {
                $result = __Database::connectorDB()->query($sql);

                if($result) {
                    return $result;
                } else {
                    return $msg = ["message"=>"Error"];
                }
            } catch(PDOException $e) {
                return $error = [
                    "error"=> ["text"=>$e->getMessage()]
                ];
            }

            // run and return the query result resource
            // return mysqli_query(__Database::__dbConnection(), $sql);
        }

        public static function __select()
        {

        }

        public static function __dbConnection()
        {
            return new mysqli('10.128.0.4', 'root', '1921680809@Admintec008143', 'movil');
        }
    }
?>