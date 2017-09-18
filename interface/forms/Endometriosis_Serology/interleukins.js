function calculateEndo() 
{
	//get all the elements needed
	
	var ilb = document.getElementById('ilb');
	var il6 = document.getElementById('il6');
	var tnf = document.getElementById('tnf');
	var ast = document.getElementById('ast');
	var risk = 0;
	var status = "";
	
	// Cytokines and Interleukins
	if (tnf.value >= 15)
		{risk = risk + 1;}

	if (il6.value >= 1.3)
		{risk = risk + 1;}

	if (ilb.value >= 7)
		{risk = risk + 1;}

	//Assess probability of Endometriosis

	if (risk = 0)
		{
		status = "The patient interleuking profile is below the threshold for having endometriosis.";
		}
	else if(risk = 1)
		{
		status = "The patient has a low probability of having endometriosis.";
		}
	else if(risk = 2)
		{
		status = "The patient has a moderate probability of having endometriosis.";
		}
	else if(risk = 3)
		{
		status = "The patient has a high probability of having endometriosis.";
		}
	ast.value = status;

}

