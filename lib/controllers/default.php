<?php
    class DefaultController extends Controller
    {
        public function DefaultAction()
        {
            $template = new Template();
            $template->IncludeFile("index");
        }
    }
?>