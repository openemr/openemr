/*
@package       OpenEMR
@author        Rachel Ellison <ellison.rachel.e@gmail.com>
@copyright (C) 2020 Rachel Ellison <ellison.rachel.e@gmail.com>
@link          http://www.open-emr.org
@license       https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3


This function is used to calculate an approximate body surface area for a person given their height and weight by implementing the Mosteller formula.
It is the square root of the height(in centimeters) multiplied by the weight(in kilograms) divided by 3600.

This function supports heights in units of centimeters, meters, inches, and feet + inches.
This function supports weights in units of pounds, kilograms, and stone.

You need to specify the units and provide them according to the Unified Code for Units of Measure (UCUM) standard.
https://ucum.nlm.nih.gov/
https://loinc.org/usage/units/

This link is pretty dated but I believe the codes are still accurate.
https://ucum.nlm.nih.gov/example-UCUM-Codes-v1.4.pdf
*/



function calculateBodySurfaceAreaMosteller(height, height2, heightUnit, weight, weightUnit) {

/*
height2 should not be used unless you're entering in feet and inches separately.
I always set it to 0 so I don't forget about it shift everything to the left accidentally.
*/

//determine unit of measure provided for height and convert to centimeters.
	switch(heightUnit){
    	case "cm":
        	//This is the unit we want, do nothing.
        break;
        case "[in_i]":
        	//in is not valid, you need the underscore i to indicate that it's international inches.
            height=height*2.54;
        break;
        case "m":
        	height=height*100;
            break;
        case "[ft_i]":
        	//assume the other height is inches
            //convert to inches
            height=height*12+height2;
            
           
            //convert to centimeters
            height = height*2.54;
        break;
    	default:
            height=0;
       		alert("error, check the input height parameter and identify their supported and valid UCUM units.  cm, m [in_i], [ft_i]");
     
    }//end height switch
    
    
    //Determine units of measure provided for weight and convert to kilograms.
      switch(weightUnit){
      	case "kg":
        	//This is the unit we want, do nothing
        break;
        case "[lb_av]":
        	//The av is required, this is a US and british pound
            weight=weight/2.2046226218;
        break;
        case "[stone_av]":
      		//I don't know that this is used outside Britain but it's on the list of supported UCUM units.
            weight=weight*6.35029318;
        break;
        default:
        	weight=0;
            alert("error, check the input weight parameter and identify supported and valid ucum units");
            
      }//end weight switch
    
    
    BSA_Mosteller=Math.sqrt(height*weight/3600);
    

    //This will make the calculated value pop up in an alert before the html page even loads.  Comment this out unless you're troubleshooting
    //alert(BSA_Mosteller);


    //Let's discuss what LOINC value to return, there's this one 3140-1 Body Surface Area Derived From Formula
    //https://loinc.org/3140-1/

    //This one that doesn't specify how it was measured.  https://loinc.org/8277-6/

    //There's also this code that is still in trial status that specifies how you obtained BSA
    //https://loinc.org/70953-5/


    //Let's go with the derived one, 3140-1, for now since it references the formula we used exactly.


    //return an object containing the calculated value, ucum units, and LOINC code for derived body surface area.
    
    //Got rid of the return unit field and hard coded to meters squared.  Might add different return unit options, like cm^2 later if requested.
    return results={BodySurfaceArea: BSA_Mosteller, units: m2, LOINCcode: "3140-1"};


    
    //end function
}
