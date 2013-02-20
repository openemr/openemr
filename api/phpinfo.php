<?php

if(mail("hkonnet@gmail.com", "subject", "This is testing email")){
    echo "success";
}else{
    echo "failed";
}
?>
