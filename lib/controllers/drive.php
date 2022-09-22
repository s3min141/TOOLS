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

                if (!file_exists(__DRIVE__ . $hashedUsername . "/drive")) {
                    mkdir(__DRIVE__ . $hashedUsername . "/drive", 0777);
                }
                
                chmod(__DRIVE__ . $hashedUsername, 0777);
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
                    $driveModel[$i]->realpath = AESDecrypt($driveModel[$i]->realpath, $_SESSION["privatekey"]);
                    $realPath = $driveModel[$i]->realpath;
                    $fileMimeType = explode("/", mime_content_type($realPath));
                    if ($fileMimeType[0] != "application") {
                        $fileMimeType = $fileMimeType[0];
                    }
                    else {
                        $fileMimeType = $fileMimeType[1];
                    }

                    $resultArr[] = Array("filename"=>$driveModel[$i]->filename, "filesize"=>filesize($realPath), "filetype"=>$fileMimeType);
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
                $fileSavePath = __DRIVE__ . SecureHash($username) . "/drive/" . $tmpName . "." . $fileExt;
                $driveModel->fileauthor = $username;
                $driveModel->filename = $file["name"];
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

        function DownloadAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $filename = $this->sql->StringFilter($_GET["filename"], "sql");

            $driveInfo = new DriveInfo;
            $downloadResult = $driveInfo->Get(
                ["realpath"], 
                ["fileauthor"=>$username, "filename"=>$filename],
                1
            );

            if ($downloadResult != false) {
                $fileName = $downloadResult->filename;
                $realPath = AESDecrypt($downloadResult->realpath, $_SESSION["privatekey"]);
                header("Content-type: application/octet-stream");
                header("Content-Length: ".filesize($realPath));
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Transfer-Encoding: binary");
                header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
                header("Pragma: public");
                header("Expires: 0");
                header("Content-type: " . mime_content_type($realPath));
                $fileOpen = fopen($realPath, "rb");
                fpassthru($fileOpen);
                fclose($fileOpen);
                $this->Output(true);
            }
            else {
                $this->Output(false);
            }
        }

        function RemoveAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $filename = $this->sql->StringFilter($_GET["filename"], "sql");

            $driveInfo = new DriveInfo;
            $getResult = $driveInfo->Get(
                ["realpath"], 
                ["fileauthor"=>$username, "filename"=>$filename],
                1
            );
            $realPath = AESDecrypt($getResult->realpath, $_SESSION["privatekey"]);

            $removeResult = $driveInfo->Del(["fileauthor"=>$username, "filename"=>$filename]);

            if (!unlink($realPath)) {
                $this->Output(false);
            }

            $this->Output(true);
        }
    }
?>