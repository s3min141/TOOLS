<?php
    class MemoController extends Controller
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
            $memoInfo = new MemoInfo;
            $conditionArr = ["memoauthor" => $username];
            $memoModel = $memoInfo->Get([], $conditionArr, null);

            if ($memoModel != false) {
                for ($i = 0; $i < count($memoModel); $i++) {
                    $memoModel[$i]->memotitle = AESDecrypt($memoModel[$i]->memotitle, $_SESSION["privatekey"]);
                    $memoModel[$i]->memocontent = AESDecrypt($memoModel[$i]->memocontent, $_SESSION["privatekey"]);
                    $memoModel[$i]->memotitle = $this->sql->StringFilter($memoModel[$i]->memotitle, "xss");
                    $memoModel[$i]->memocontent = $this->sql->StringFilter($memoModel[$i]->memocontent, "xss");
                    
                    if ($memoModel[$i]->islocked == 1) {
                        $memoModel[$i]->memocontent = "Unlock memo to see content";
                    }
                    $_SESSION[$memoModel[$i]->memoid . "-islocked"] = $memoModel[$i]->islocked;
                }
            }

            $this->Output($memoModel);
        }

        function UnlockAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $memoid = $this->AuthFilter($_POST["memoid"]);
            $pincode = $this->AuthFilter($_POST["pincode"]);

            $memoInfo = new MemoInfo;
            $conditionArr = ["memoauthor" => $username, "memoid" => $memoid];
            $memoModel = $memoInfo->Get([], $conditionArr, 1);

            if ($memoModel != false) {
                if ($memoModel->pincode == SecureHash($pincode)) {
                    $_SESSION[$memoModel->memoid . "-islocked"] = "0";
                    $memoModel->memotitle = AESDecrypt($memoModel->memotitle, $_SESSION["privatekey"]);
                    $memoModel->memocontent = AESDecrypt($memoModel->memocontent, $_SESSION["privatekey"]);
                    $memoModel->memotitle = $this->sql->StringFilter($memoModel->memotitle, "xss");
                    $memoModel->memocontent = $this->sql->StringFilter($memoModel->memocontent, "xss");
                    $this->Output($memoModel);
                }
            }

            $this->Output(false);
        }

        function AddAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $memoInfo = new MemoInfo;
            $memoModel = new Memo;
            $memoId = $this->sql->StringFilter($_POST["memoid"]);

            if ($memoId == "") {
                $memoId = GenerateString(10);
                $memoModel->memoauthor = $this->AuthFilter($_SESSION["username"]);
                $memoModel->memoid = $memoId;
                $memoModel->islocked = filter_var($_POST["islocked"], FILTER_VALIDATE_BOOLEAN);
                $memoModel->pincode = $this->sql->StringFilter($_POST["pincode"], "num");
                if ($memoModel->islocked == true && strlen($memoModel->pincode) != 5) {
                    $this->Output(false);
                }
                $memoModel->pincode = SecureHash($memoModel->pincode);
            }
            else {
                $memoModel = $memoInfo->Get([], ["memoid" => $memoId], 1);
            }

            $memoModel->createtime = date("Y-m-d");
            $memoModel->memotitle = $this->sql->StringFilter(trim($_POST["memotitle"]), "sql");
            $memoModel->memocontent = trim($_POST["memocontent"]);
            $memoModel->memotitle = AESEncrypt($memoModel->memotitle, $_SESSION["privatekey"]);
            $memoModel->memocontent = AESEncrypt($memoModel->memocontent, $_SESSION["privatekey"]);


            if ($memoModel->memotitle == "" || $memoModel->memocontent == "") {
                $this->Output(false);
            }

            if ($_SESSION[$memoModel->memoid . "-islocked"] == "1") {
                $this->Output(false);
            }

            $createResult = $memoInfo->Set($memoModel, "memoid");
            if ($createResult == true) {
                $_SESSION[$memoid . "-islocked"] = $memoModel->islocked;
            }
            $this->Output($createResult);
        }

        function DeleteAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $memoauthor = $this->AuthFilter($_SESSION["username"]);
            $memoid = $this->sql->StringFilter($_POST["memoid"], "sql");
            $conditionArr = ["memoauthor" => $memoauthor, "memoid" => $memoid];

            $memoInfo = new MemoInfo;
            if ($_SESSION[$memoid . "-islocked"] == "0") {
                $_SESSION[$memoid . "-islocked"] = "";
                $_SESSION[$memoid . "-pincode"] = "";
                $removeResult = $memoInfo->Del($conditionArr);
                $this->Output($removeResult);
            }
            $this->Output(false);
        }
    }
?>