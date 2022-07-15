<?php
    class StatusController extends Controller
    {
        public function ProfileAction()
        {
            if ($this->IsSigned()) {
                $this->Output(Array("username" => $_SESSION["username"]));
            }
            $this->Output(false);
        }
    }
?>