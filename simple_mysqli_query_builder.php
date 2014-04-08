<?php
    $mysqli = new mysqli('localhost', 'root', '', 'test');
    $mysqli->set_charset("utf8");

    class simple_query_builder
    {
        public $parms = array();
        /* Developer Mode */
        public $devMode = 0;

        /* Protect from SQL Injections */
        public function sqlProt($str)
        {
            global $mysqli;
            return "'" . $mysqli->real_escape_string($str) . "'";
        }

        /* Add field name and protected value */
        public function add($name, $value)
        {
            $this->parms[$name] = $this->sqlProt($value);
        }

        /* Add field name and non protected value (USE for example: NOW() + INTERVAL 1 DAY) */
        public function addCustom($name, $value)
        {
            $this->parms[$name] = $value;
        }

        /* Build INSERT query */
        public function insert($tableName)
        {
            global $mysqli;

            $queryCol = '';
            $queryVal = '';
            foreach ($this->parms as $key => $val) {
                if ($queryCol == '') {
                    $queryCol = "`$key`";
                } else {
                    $queryCol = $queryCol . ',' . "`$key`";
                }
                if ($queryVal == '') {
                    $queryVal = $val;
                } else {
                    $queryVal = $queryVal . "," . $val;
                }
            }
            $query = "INSERT INTO $tableName ($queryCol) VALUES ($queryVal)";
            $res = $mysqli->query($query);
            if (!$res) {
                if ($this->devMode == 1) {
                    echo "Error: <b>Wrong MySQL INSERT syntax.</b> <br>"." \r\n";
                    echo $query . "<br>" . " \r\n";
                }

                return false; exit();
            } else {
                return true;
            }
        }

        /* Build UPDATE Query */
        public function update($tableName, $where = null)
        {
            global $mysqli;

            $querySET = '';
            foreach ($this->parms as $key => $val) {
                if ($querySET == '') {
                    $querySET = "`$key`" . " = " . $val;
                } else {
                    $querySET = $querySET . ',' . "`$key`" . " = " . $val;
                }
            }
            if ($where != null) { $query = "UPDATE $tableName SET $querySET WHERE $where"; }
            else { $query = "UPDATE $tableName SET $querySET"; }

            $res = $mysqli->query($query);

            if (!$res) {
                if ($this->devMode == 1) {
                    echo "Error: <b>Wrong MySQL UPDATE syntax.</b> <br>"." \r\n";
                    echo $query . "<br>" . " \r\n";
                }
                return false; exit();
            } else {
                return true;
            }
        }

        /* Build SELECT Query with default LIMIT */
        public function select($query, $limit = 0, $offset = 50)
        {
            global $mysqli;

            $result = '';
            $res    = $mysqli->query($query . " LIMIT $limit, $offset");
            if (!$res) {
                if ($this->devMode == 1) {
                    echo "Error: <b>Wrong MySQL SELECT syntax.</b> <br>"." \r\n";
                    echo $query . "<br>" . " \r\n";
                }
                return $result; exit();
            }

            while ($row = $res->fetch_assoc()) {
                $result[] = $row;
            }
            return $result;
        }
    }