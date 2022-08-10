<?php
    class User
    {
        public $uuid;
        public $username;
        public $password;
    }

    class Memo
    {
        public $memoauthor;
        public $memoid;
        public $memotitle;
        public $memocontent;
        public $createtime;
        public $islocked;
        public $pincode;
    }

    class Task
    {
        public $taskauthor;
        public $taskid;
        public $taskcontent;
        public $taskpriority;
        public $taskstatus;
    }

    class Drive
    {
        public $fileauthor;
        public $filename;
        public $realpath;
    }

    class ModelHandler
    {
        protected $sql;
        protected $modelName;
        protected $modelTable;

        public function __construct()
        {
            global $sql;
            $this->sql = $sql;
        }

        public function DataToModel($queryResult, $type)
        {
            $classObj = new $this->modelName;

            if ($type == 1) {
                foreach ($queryResult as $key => $val) {
                    $classObj->$key = $val;
                }
            }
            else {
                $classObj = [];
                for ($i = 0; $i < count($queryResult); $i++) {
                    $classObj[$i] = new $this->modelName;
                    foreach ($queryResult[$i] as $key => $val) {
                        $classObj[$i]->$key = $val;
                    }
                }
            }
            return $classObj;
        }

        public function ParseCondition($conditionArr, $operator)
        {
            $conditions = [];
            foreach ($conditionArr as $key => $val) {
                $key = $this->sql->StringFilter($key);
                $val = $this->sql->StringFilter($val);
                $conditions[] = "$key='$val'";
            }
            return implode($operator, $conditions);
        }

        public function Get($getArr = [], $conditionArr = [], $limit = null, $operator = " AND ")
        {
            $columns = implode(",", $getArr);
            $conditions = $this->ParseCondition($conditionArr, $operator);
            $type = ($limit == 1) ? 1 : 2;

            if ($limit != null) {
                $limit = "LIMIT $limit";
            } 
            
            $columns = ($columns == "") ? "*" : $columns;
            $conditions = ($conditions == "") ? "TRUE" : $conditions;
            $finalQuery = "SELECT $columns FROM $this->modelTable WHERE $conditions $limit";
            $queryResult = $this->sql->SendQuery($finalQuery, $type);
            
            return ($queryResult) ? $this->DataToModel($queryResult, $type) : false;
        }

        public function Set($insertModel, $checkColumn = "")
        {
            $existModel = $this->Get([], [$checkColumn => $insertModel->$checkColumn], 1);
            if ($existModel == false) {
                $insertValues = "";
                foreach ($insertModel as $key => $val) {
                    $valType = gettype($val);
                    switch ($valType) {
                        case "double":
                        case "float":
                        case "integer":
                        case "boolean":
                            $val = intval($val);
                            $insertValues .= "$val, ";
                            break;
                        default: 
                            $insertValues .= "'$val', ";
                            break;
                    }
                }
                $insertValues = substr($insertValues, 0, -2);

                $finalQuery = "INSERT INTO $this->modelTable VALUES($insertValues)";   
            }
            else {
                $insertValues = "";

                foreach ($insertModel as $key => $val) {
                    if ($existModel->$key != $val) {
                        $valType = gettype($val);
                        switch ($valType) {
                            case "integer":
                            case "boolean":
                                $val = intval($val);
                                $insertValues .= "$key=$val, ";
                                break;
                            default: 
                                $insertValues .= "$key='$val', ";
                                break;
                        }
                    }
                }
                $insertValues = substr($insertValues, 0, -2);

                $memoId = $insertModel->$checkColumn;
                $finalQuery = "UPDATE $this->modelTable SET $insertValues WHERE $checkColumn='$memoId'";
            }
            $queryResult = $this->sql->SendQuery($finalQuery, 0);

            return $queryResult;
        }

        public function Del($conditionArr, $operator = " AND ")
        {
            $conditions = $this->ParseCondition($conditionArr, $operator);
            $finalQuery = "DELETE FROM $this->modelTable WHERE $conditions";
            $queryResult = $this->sql->SendQuery($finalQuery, 0);

            return $queryResult;
        }
    }

    class UserInfo extends ModelHandler
    {
        function __construct()
        {
            $this->modelName = "User";
            $this->modelTable = "user_table";
            ModelHandler::__construct();
        }
    }

    class MemoInfo extends ModelHandler
    {
        function __construct()
        {
            $this->modelName = "Memo";
            $this->modelTable = "memo_table";
            ModelHandler::__construct();
        }
    }

    class TaskInfo extends ModelHandler
    {
        function __construct()
        {
            $this->modelName = "Task";
            $this->modelTable = "task_table";
            ModelHandler::__construct();
        }
    }

    class DriveInfo extends ModelHandler
    {
        function __construct()
        {
            $this->modelName = "Drive";
            $this->modelTable = "drive_table";
            ModelHandler::__construct();
        }
    }
?>