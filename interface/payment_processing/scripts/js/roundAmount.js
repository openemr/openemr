function roundAmount(){
			var decimal = '<?php echo $decimal; ?>';
			var chargeAmount  = document.getElementById("amount").value;
			if(chargeAmount > 0 && !isNaN(chargeAmount)){
                // to support zero decimal currencies - prevent adding zeros after decimal
				if decimal == "TD"{
                    document.getElementById('amount').value = Number(chargeAmount).toFixed(2);
				} else if decimal == "ZD" {
                    document.getElementById('amount').value = Number(chargeAmount).toFixed(0);
                }
			}
		}
	            
	  