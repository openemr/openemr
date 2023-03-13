<?php
    namespace OpenEMR\Modules\ClaimRevConnector;
    $tab="connectivity";
    require_once "../../../../globals.php";

    use OpenEMR\Modules\ClaimRevConnector\Bootstrap;
    use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;
    
    class ConnectivityInfo
    {
        public $client_authority;
        public $clientId;
        public $client_scope;
        public $client_secret;
        public $api_server;
        public $hasToken;
        public $defaultAccount;
        function __construct() {
            $bootstrap = new Bootstrap($GLOBALS['kernel']->getEventDispatcher());
            $globalsConfig = $bootstrap->getGlobalConfig();
            $this->client_authority = $globalsConfig->getClientAuthority();     
            $this->clientId = $globalsConfig->getClientId();
            $this->client_scope = $globalsConfig->getClientScope();
            $this->client_secret = $globalsConfig->getClientSecret();       
            $this->api_server = $globalsConfig->getApiServer();     
            $this->token = ClaimRevApi::GetAccessToken(); 
            $this->hasToken = ClaimRevApi::CanConnectToClaimRev();
            $this->defaultAccount = ClaimRevApi::getDefaultAccount($this->token); 
        }
    }

?>
<html>
    <head>
        <link rel="stylesheet" href="../../../../../public/assets/bootstrap/dist/css/bootstrap.min.css">
    </head>
    <title>ClaimRev Connect - Account</title>
    <body>
        <div class="row">
            <div class="col">
                <?php include '../templates/navbar.php'; ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
            <?php  $connectivityInfo = new ConnectivityInfo(); ?>
                <h3>Client Connection Information</h3>              
                <ul>
                    <li>Authority: <?php echo $connectivityInfo->client_authority ?></li>
                    <li>Client ID: <?php echo $connectivityInfo->clientId; ?></li>
                    <li>Client Scope: <?php echo $connectivityInfo->client_scope; ?></li>
                    <li>API Server: <?php echo $connectivityInfo->api_server; ?></li>
                    <li>Default Account <?php echo $connectivityInfo->defaultAccount; ?>  </li>
                    <li>Token <?php echo $connectivityInfo->hasToken; ?>  </li>
                </ul>
            </div>       
        </div>
        <div class="row">
            <div class="col">
                <a href="index.php">Back to index</a>
            </div>
        </div>

    </body>



</html>

