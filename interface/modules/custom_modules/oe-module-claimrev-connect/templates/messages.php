<?php
if($benefit->messages != null && $benefit->messages )
{
?>
    <div class="row">
        <div class="col">
            Messages
        </div>
        <div class="col">
<?php
            foreach($benefit->messages as $message)
            {
?>
                <div class="row">
                    <div class="col">
                        <?php echo($message); ?>
                    </div>
                </div>
<?php
            }
?>
        </div>
    </div>
<?php
}
?>