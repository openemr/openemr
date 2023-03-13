<?php 
if($benefit->procedureInfo != null )
{
    $procedureInfo = $benefit->procedureInfo;
?>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h6>Procedure Information</h6>
                    <div class="row">
                        <div class="col">
                            <?php echo($procedureInfo->serviceIdQualifier); ?> : <?php echo($procedureInfo->procedureCode); ?>
                        </div>
                        <div class="col">
                            <ol>
                                <?php 
                                    if($procedureInfo->modifier1 != "")
                                    {
                                        echo("<li>" . $procedureInfo->modifier1 ."</li>");
                                    }
                                    if($procedureInfo->modifier2 != "")
                                    {
                                        echo("<li>" . $procedureInfo->modifier2 ."</li>");
                                    }
                                    if($procedureInfo->modifier3 != "")
                                    {
                                        echo("<li>" . $procedureInfo->modifier3 ."</li>");
                                    }
                                    if($procedureInfo->modifier4 != "")
                                    {
                                        echo("<li>" . $procedureInfo->modifier4 ."</li>");
                                    }
                                ?>
                            </ol>
                        </div>
                        <div class="col">
                            <ol>
                                <?php 
                                    if($procedureInfo->pointer1 != "")
                                    {
                                        echo("<li>" . $procedureInfo->pointer1 ."</li>");
                                    }
                                    if($procedureInfo->pointer2 != "")
                                    {
                                        echo("<li>" . $procedureInfo->pointer2 ."</li>");
                                    }
                                    if($procedureInfo->pointer3 != "")
                                    {
                                        echo("<li>" . $procedureInfo->pointer3 ."</li>");
                                    }
                                    if($procedureInfo->pointer4 != "")
                                    {
                                        echo("<li>" . $procedureInfo->pointer4 ."</li>");
                                    }
                                ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>