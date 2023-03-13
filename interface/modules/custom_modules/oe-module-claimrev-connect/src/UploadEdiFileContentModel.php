<?php
    namespace OpenEMR\Modules\ClaimRevConnector;
    class UploadEdiFileContentModel
    {

        public $AccountNumber = "";
        public $EdiFileContent = "";
        public $FileName = "";
       

        public function __construct($acct, $ediFileContent, $fileName) {
            $this->AccountNumber = $acct;
            $this->EdiFileContent = $ediFileContent;
            $this->FileName = $fileName;
           
    }
}