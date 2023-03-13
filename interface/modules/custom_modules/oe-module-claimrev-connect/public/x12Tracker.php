<?php
    namespace OpenEMR\Modules\ClaimRevConnector;
    $tab="x12";
    require_once "../../../../globals.php";

    use OpenEMR\Modules\ClaimRevConnector\Bootstrap;
    use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;

    class X12TrackerPage
    {
        public static function SearchX12Tracker($postData)
        {
            $startDate = $_POST['startDate']; 
            $endDate = $_POST['endDate']; 

            $sql = "SELECT * FROM x12_remote_tracker where created_at BETWEEN ? AND ?";
            $files = sqlStatementNoLog($sql,array($startDate,$endDate));
            return $files;
        }
    }
?>

<html>
    <head>
        <link rel="stylesheet" href="../../../../../public/assets/bootstrap/dist/css/bootstrap.min.css">
    </head>
    <title>ClaimRev Connect - X12 Tracker</title>
    <body>
        <div class="row">
            <div class="col">
                <?php include '../templates/navbar.php'; ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                This tab helps give visibility to files that are in the x12 Tracker table.
            </div>       
        </div>
        <div class="row">
            <div class="col">
                <form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
                    <div class="card">  
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="startDate">Created Date Start</label>
                                    <input type="date" class="form-control"  id="startDate" name="startDate"  value="<?php echo isset($_POST['startDate']) ? $_POST['startDate'] : '' ?>" placeholder="yyyy-mm-dd"/>
                                </div>
                            </div>                    
                            <div class="col">
                                <div class="form-group">
                                    <label for="endDate">Created Date End</label>
                                    <input type="date" class="form-control"  id="endDate" name="endDate"  value="<?php echo isset($_POST['endDate']) ? $_POST['endDate'] : '' ?>" placeholder="yyyy-mm-dd"/>
                                </div>
                            </div>
                            <div class="col">
                            
                            </div>                    
                            <div class="col">
                                
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <button type="submit" name="SubmitButton" class="btn btn-primary">Submit</button>
                            </div>
                            <div class="col-10">
                            
                            </div>
                        </div>        
                    </div> 
                </form>
                <?php
                    $datas = null;
                    if(isset($_POST['SubmitButton'])) { //check if form was submitted

                        $datas = X12TrackerPage::SearchX12Tracker($_POST);     
                    }
                    if($datas != null) { ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Filename</th>
                                <th scope="col">Messages</th>
                                <th scope="col">Status</th>                              
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            foreach($datas as $data) 
                            { 
                        ?>  
                            <tr>
                                <td>
                                    <?php echo($data["x12_filename"]); ?>
                                </td>
                                <td>
                                    <?php echo($data["status"]); ?>
                                </td>
                                <td>
                                    <?php echo($data["messages"]); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>







                    <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <a href="index.php">Back to index</a>
            </div>
        </div>

    </body>



</html>
