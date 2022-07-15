<?php
    class Template
    {
        protected $dir;

        public function __construct()
        {
            $this->dir = __TEMPLATE__;
        }

        public function IncludeFile($templateName): bool
        {
            $fileName = preg_replace("/[^a-zA-Z0-9-_]/", "", $templateName);
            $templateDir = $this->dir . $fileName . ".php";
            if (!file_exists($templateDir)) {
                return false;
            }
            @include $templateDir;
            return true;
        }
    }
?>