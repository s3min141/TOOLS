<?php
    class TaskController extends Controller
    {
        protected $sql;

        function __construct()
        {
            global $sql;
            $this->sql = $sql; 
        }

        function ListAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $taskInfo = new TaskInfo;
            $conditionArr = ["taskauthor" => $username];
            $taskModel = $taskInfo->Get([], $conditionArr, null);

            if ($taskModel != false) {
                for ($i = 0; $i < count($taskModel); $i++) {
                    $taskModel[$i]->taskcontent = AESDecrypt($taskModel[$i]->taskcontent, $_SESSION["privatekey"]);
                    $taskModel[$i]->taskcontent = $this->sql->StringFilter($taskModel[$i]->taskcontent, "xss");

                    switch ($taskModel[$i]->taskstatus) {
                        case "0":
                            $taskModel[$i]->taskstatus = "Not done";
                            break;
                        default:
                        $taskModel[$i]->taskstatus = "Done";
                            break;
                    }

                    switch ($taskModel[$i]->taskpriority) {
                        case "1":
                            $taskModel[$i]->taskpriority = "Medium";
                            break;
                        case "2":
                            $taskModel[$i]->taskpriority = "High";
                            break;
                        default:
                            $taskModel[$i]->taskpriority = "Low";
                            break;
                    }
                }
            }

            $this->Output($taskModel);
        }

        function StatusAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $taskInfo = new TaskInfo;
            $taskid = $this->AuthFilter($_POST["taskid"]);
            $taskstatus = $this->sql->StringFilter($_POST["taskstatus"], "num");
            $conditionArr = ["taskid" => $taskid];
            $taskModel = $taskInfo->Get([], $conditionArr, 1);

            $taskModel->taskstatus = filter_var($taskstatus, FILTER_VALIDATE_BOOLEAN);
            $setResult = $taskInfo->Set($taskModel, "taskid");
            $this->Output($taskid . "/" . $taskstatus);
        }

        function AddAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $taskInfo = new TaskInfo;
            $taskModel = new Task;

            $taskModel->taskauthor = $this->AuthFilter($_SESSION["username"]);
            $taskModel->taskid = GenerateString(10);
            $taskModel->taskcontent = $this->sql->StringFilter(trim($_POST["taskcontent"]), "sql");
            $taskModel->taskcontent = AESEncrypt($taskModel->taskcontent, $_SESSION["privatekey"]);
            $taskModel->taskpriority = $this->sql->StringFilter($_POST["taskpriority"], "num");
            $taskModel->taskstatus = false;

            if ($taskModel->taskcontent == "") {
                $this->Output(false);
            }

            $createResult = $taskInfo->Set($taskModel, "taskid");
            $this->Output($createResult);
        }

        function DeleteAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $taskauthor = $this->AuthFilter($_SESSION["username"]);
            $taskid = $this->sql->StringFilter($_POST["taskid"], "sql");
            $conditionArr = ["taskauthor" => $taskauthor, "taskid" => $taskid];

            $taskInfo = new TaskInfo;
            $removeResult = $taskInfo->Del($conditionArr);
            $this->Output($removeResult);
        }

        function CleanupAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $taskInfo = new TaskInfo;
            $conditionArr = ["taskauthor" => $username];
            $taskModel = $taskInfo->Get([], $conditionArr, null);

            if ($taskModel != false) {
                for ($i = 0; $i < count($taskModel); $i++) {
                    if ($taskModel[$i]->taskstatus == "1") {
                        $taskInfo->Del(["taskauthor" => $taskModel[$i]->taskauthor, "taskid" => $taskModel[$i]->taskid]);
                    }
                }
                $this->Output(true);
            }
            $this->Output(false);
        }
    }
?>