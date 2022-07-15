<?php
    class UserController extends Controller
    {
        protected $sql;

        function __construct()
        {
            global $sql;
            $this->sql = $sql;
        }

        function LoginAction()
        {
            if ($this->IsSigned()) {
                $this->Output(false);
            }

            $checkUsrnm = $this->AuthFilter($_POST["username"]);
            $checkPass = SecureHash($this->AuthFilter($_POST["password"]));
            $addr = $this->AuthFilter($_SERVER["REMOTE_ADDR"]);

            if ($checkUsrnm == "" || $checkPass == "" || $checkUsrnm > 36) {
                $this->Output(false);
            }

            $userInfo = new UserInfo;
            $getArr = ["uuid", "username", "password"];
            $conditionArr = ["username" => $checkUsrnm];
            $userModel = $userInfo->Get($getArr, $conditionArr, 1);

            if ($userModel->username != null) {
                if ($checkPass == $userModel->password) {
                    $_SESSION["username"] = $userModel->username;
                    $_SESSION["session"] = SecureHash($userModel->username . $addr);
                    $_SESSION["privatekey"] = SecureHash($userModel->password);
                    $this->Output(true);
                }
            }
            $this->Output(false);
        }

        function LogoutAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $_SESSION = [];
            session_destroy();
            $this->Output(true);
        }
    }
?>