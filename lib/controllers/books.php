<?php
    class BooksController extends Controller
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

                if (!file_exists(__DRIVE__ . $hashedUsername . "/books")) {
                    mkdir(__DRIVE__ . $hashedUsername . "/books", 0777);
                }
                
                chmod(__DRIVE__ . $hashedUsername, 0777);
            }
        }

        function ListAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $bookInfo = new BookInfo;
            $conditionArr = ["bookauthor" => $username];
            $getArr = ["bookid", "bookname", "category", "uploaddate"];
            $bookModel = $bookInfo->Get($getArr, $conditionArr, null);

            if ($bookModel != false) {
                for ($i = 0; $i < count($bookModel); $i++) {
                    $bookModel[$i]->bookname = AESDecrypt($bookModel[$i]->bookname, $_SESSION["privatekey"]);
                    $bookModel[$i]->category = AESDecrypt($bookModel[$i]->category, $_SESSION["privatekey"]);
                }
            }

            $this->Output($bookModel);
        }

        function UploadAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $category = $this->sql->StringFilter($_POST["category"], "sql");
            $bookFile = $_FILES["bookfile"];

            if ($category == "" || $bookFile == null) {
                $this->Output(false);
            }

            $bookTmpName = explode("/", $bookFile["tmp_name"])[2];
            $bookFileExt = pathinfo($bookFile["name"], PATHINFO_EXTENSION);
            $bookFileSavePath = __DRIVE__ . SecureHash($username) . "/books/" . $bookTmpName . "." . $bookFileExt;

            $bookInfo = new BookInfo;
            $bookModel = new Book;

            $bookModel->bookid = GenerateString(10);
            $bookModel->bookauthor = $username;
            $bookModel->bookname = AESEncrypt($bookFile["name"], $_SESSION["privatekey"]);
            $bookModel->category = AESEncrypt($category, $_SESSION["privatekey"]);
            $bookModel->uploaddate = date("Y-m-d");
            $bookModel->uploaddir = AESEncrypt($bookFileSavePath, $_SESSION["privatekey"]);

            if ($bookFile["error"] != UPLOAD_ERR_OK) {
                http_response_code(409);
                $this->Output(false);
            }

            if ($bookFileExt != "pdf") {
                http_response_code(422);
                $this->Output(false);
            }

            if (move_uploaded_file($bookFile["tmp_name"], $bookFileSavePath)) {
                $uploadResult = $bookInfo->Set($bookModel);
                $this->Output($uploadResult);
            }

            http_response_code(409);
            $this->Output(false);
        }

        function DeleteAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $bookid = $this->sql->StringFilter($_POST["bookid"], "sql");
            $conditionArr = ["bookauthor" => $username, "bookid" => $bookid];

            $bookInfo = new BookInfo;

            $getResult = $bookInfo->Get(
                ["uploaddir"], 
                ["bookauthor"=>$username, "bookid"=>$bookid],
                1
            );
            $uploadDir = AESDecrypt($getResult->uploaddir, $_SESSION["privatekey"]);

            if (!unlink($uploadDir)) {
                $this->Output(false);
            }

            $removeResult = $bookInfo->Del($conditionArr);

            $this->Output($removeResult);
        }

        function ShowAction() 
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }

            $username = $this->AuthFilter($_SESSION["username"]);
            $bookid = $this->sql->StringFilter($_POST["bookid"], "sql");
            $conditionArr = ["bookauthor" => $username, "bookid" => $bookid];
            $getArr = ["bookname", "uploaddir"];

            $bookInfo = new BookInfo;
            $bookModel = $bookInfo->Get($getArr, $conditionArr, null);

            if ($bookModel != false) {
                $bookModel[0]->uploaddir = AESDecrypt($bookModel[0]->uploaddir, $_SESSION["privatekey"]);
                $bookModel[0]->bookname = AESDecrypt($bookModel[0]->bookname, $_SESSION["privatekey"]);
                $realPath = $bookModel[0]->uploaddir;

                $type = pathinfo($realPath, PATHINFO_EXTENSION);
                $data = file_get_contents($realPath);
                $dataUri = 'data:application/' . $type . ';base64,' . base64_encode($data);

                $this->Output($dataUri);
            }

            $this->Output(false);
        }
    }
?>