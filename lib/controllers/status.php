<?php
    class StatusController extends Controller
    {
        public function ProfileAction()
        {
            if (!$this->IsSigned()) {
                $this->Output(false);
            }
            
            $this->Output(Array("username" => $_SESSION["username"]));
        }
    }
?>