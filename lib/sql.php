<?php
    class SQL 
    {
        private $conn;

        public function __construct()
        {
            if (!function_exists("mysqli_connect")) {
                die("Can't find [mysqli_connect] function");
            }
        }

        public function __destruct()
        {
            if ($this->conn) {
                mysqli_close($this->conn);
            }
        }

        public function CheckConn()
        {
            return ($this->conn) ? true : false;
        }

        public function ConnectDB($host, $username, $password, $db="")
        {
            $this->conn = mysqli_connect($host, $username, $password, $db);
            if (!$this->conn) {
                return mysqli_connect_errno();
            }
            mysqli_set_charset($this->conn, "utf-8");
        }

        public function SendQuery($query, $resultType = 0)
        {
            if (!$this->conn) {
                return false;
            }

            $queryResult = mysqli_query($this->conn, $query);
            if (!$queryResult) {
                return false;
            }

            switch ($resultType) {
                case 1:
                    return mysqli_fetch_assoc($queryResult);
                case 2:
                    $finalResult = Array();
                    while ($tempResult = mysqli_fetch_assoc($queryResult)) {
                        $finalResult[] = $tempResult;
                    }
                    return $finalResult;
                default:
                    return true;
            }
        }

        public function StringFilter($string, $type = "sql")
        {
            switch ($type) {
                case "sql":
                    if($this->conn) {
                        $filterResult = preg_replace("/[^a-zA-Z0-9-_:+!@#$.%^+&*(){}:\/\.\ <>가-힣]/", "", $string);
                        return mysqli_real_escape_string($this->conn, $filterResult);
                    }
                case "url":
                    return preg_replace("/[^a-zA-Z0-9-_&\/]/", "", $string);
                case "content":
                    if($this->conn) {
                        $filterResult = htmlspecialchars(preg_replace("/[^a-zA-Z0-9-_:+!@#$.%^&*(){}:\/.\ <>가-힣]/", "", $string));
                        return mysqli_real_escape_string($this->conn, $filterResult);
                    }
                case "xss":
                    $filterResult = stripcslashes($string);
                    $filterResult = htmlspecialchars($filterResult);
                    return $filterResult;
                case "auth":
                    return @preg_replace("/[^a-zA-Z0-9-_!@$\.%^&*(){}가-힣]/", "", $string);
                case "num":
                    return preg_replace("/\D/", "", $string);
            }
        }
    }
?>