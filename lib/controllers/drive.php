<?php
    class DriveController extends Controller
    {
        protected $sql;

        function __construct()
        {
            global $sql;
            $this->sql = $sql;
            $this->InitializeDrive();
        }

        function InitializeDrive()
        {
            if ($this->IsSigned()) {
                $hashedUsername = SecureHash($this->AuthFilter($_SESSION["username"]));
                if (!file_exists(__DRIVE__ . $hashedUsername)) {
                    mkdir(__DRIVE__ . $hashedUsername, 0777);
                }
            }
        }

        function ListAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $resultArr = [];
            $username = $this->AuthFilter($_SESSION["username"]);
            $driveInfo = new DriveInfo;
            $conditionArr = ["fileauthor" => $username];
            $driveModel = $driveInfo->Get([], $conditionArr, null);

            if ($driveModel != false) {
                for ($i = 0; $i < count($driveModel); $i++) {
                    $driveModel[$i]->filename = AESDecrypt($driveModel[$i]->filename, $_SESSION["privatekey"]);
                    $driveModel[$i]->realpath = AESDecrypt($driveModel[$i]->realpath, $_SESSION["privatekey"]);
                    $realPath = $driveModel[$i]->realpath;
                    $fileContent = "data:" . mime_content_type($realPath) . ";base64," . base64_encode(file_get_contents($realPath));
                    $fileMimeType = explode("/", mime_content_type($realPath));
                    if ($fileMimeType[0] != "application") {
                        $fileMimeType = $fileMimeType[0];
                    }
                    else {
                        $fileMimeType = $fileMimeType[1];
                    }

                    $resultArr[] = Array("filename"=>$driveModel[$i]->filename, "filesize"=>filesize($realPath), "filecontent"=>$fileContent, "filetype"=>$fileMimeType);
                }
            }

            $this->Output($resultArr);
        }

        function UploadAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $driveInfo = new DriveInfo;
            $driveModel = new Drive;

            foreach ($_FILES as $file) {
                $tmpName = explode("/", $file["tmp_name"])[2];
                $fileExt = pathinfo($file["name"], PATHINFO_EXTENSION);
                $fileSavePath = __DRIVE__ . SecureHash($username) . "/" . $tmpName . "." . $fileExt;
                $driveModel->fileauthor = $username;
                $driveModel->filename = AESEncrypt($file["name"], $_SESSION["privatekey"]);
                $driveModel->realpath = AESEncrypt($fileSavePath, $_SESSION["privatekey"]);

                if ($file["error"] != UPLOAD_ERR_OK) {
                    http_response_code(409);
                    $this->Output(false);
                }

                if ($driveModel->filesize > 5000) {
                    http_response_code(409);
                    $this->Output(false);
                }

                if (move_uploaded_file($file["tmp_name"], $fileSavePath)) {
                    $uploadResult = $driveInfo->Set($driveModel);
                    $this->Output($uploadResult);
                }

                http_response_code(409);
                $this->Output(false);
            }
        }

        function RemoveAction()
        {
            echo "TBD";
        }
    }
?>