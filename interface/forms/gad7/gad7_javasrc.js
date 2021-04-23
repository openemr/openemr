// GAD-7 form
// @package   OpenEMR
// @link      http://www.open-emr.org
// @author    ruth moulton
// @author    Ruth Moulton <moulton ruth@muswell.me.uk>
// @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
//
// java script routines, must be before any html lines that might trigger events.
// Keep the score  and manage question 8 - which is only visible when score > 0

var no_qs = 8; // number of questions in the form

 // php code isn't executed from an included js file, so put these in the calling file.
 // 'cause the server side doesn't parse this file and execute the php before sending to client

var all_scores = [0,0,0,0,0,0,0,0];
var q8_gone = true; // if question 8 has been removed - or on startup when it's not displayed automatically
var question = null;  //the element that holds the 8th question
var all_answered = [false, false, false, false, false, false, false];
var place = null; // where stuff should go on the form
var the_total_text = null; //where the total itself is displayed
var total_digits = null;  //element to hold the displayed digits
var view_start = true; // edit is starting up, we need to read in the previous scores from DB
var str_score_analysis = [" " + xl("No anxiety disorder"), " " + xl("Mild anxiety disorder"), " " + xl("Severe anxiety disorder")];
/**
 * manage question 8 - it is only be displayed when the score is not zero. It's answer is not
 * included in the score.
 * If necessary create and display question 8, needs to be done on startup or when score changes
 * Default value of it's menu is either 'please select answer' or previous value selected
 * If score has gone to zero then remove question 8 from the display if necessary
 * @param  int value - index of previous answer to question 8
 *                     'undef' (please select answer) if first time q8 being displayed
 * @return undefined
*/
function manage_question_8 (value) {
    if ((gad7_score > 0 ) && q8_gone){
        question = document.createElement("small");  // create the node to hold question 8
        question.class="text";
        var menue = document.createElement("select");  // create 'select' element, to hold the menue
        // set some of the parameters
        menue.name = "difficulty";
        menue.onchange = "record_score_q8(my_form.difficulty.value);" ;
        menue.length=5;
        create_q8 (question, menue); // populate question 8 and menue - do in main page as it requires php
        // set the default value - if new it's 'select answer', else it's previous value
        if (value == "undef")  {menue.options[4].defaultSelected = true;}
         // else we can use value as an index
        else {menue.options[Number(value)].defaultSelected = true;}
        //    display the question and  menue in the reservered place
        place = document.getElementById("q8_place");
        place.parentNode.appendChild( question, place);
        place.parentNode.appendChild( menue, place);
        q8_gone = false;
    }
    else if (gad7_score == 0 && !q8_gone) {    //take question 8 off the displayed form
        document.my_form.difficulty.remove();
        question.remove();
        q8_gone = true;
    }
    // nothing to do as
    //   score > 0 but the question is already there -
    // or score == 0 and this is at startup
}

// function update_score - display new total score - check if question 8 should be displayed
// @param int index  question being answered, is 'undef' if we simply want to display the score, e.g. on startup
// @param int new_score is 'undef' if it's from clicking 'please select an answer' in a new form - treat as zero.
// @return true|false
function update_score(index, new_score){  //index is the number of the question, score it's score
    var score = new_score;
    var explanation ='';
    var total_string = '';

    if (index == 'undef'){
        // display score  - called from view on startup - 'new_score' is previous total
        gad7_score = score;
    }

    if (index != "undef"){
    // replace score for each question - could just save it and add them all up again in a loop of course
       if (score != 'undef'){
            all_answered[index]=true;
       }
       else {
           score = 0; /* for the purposes of calculating total - if question reset to 'please input..' */
           all_answered[index]=false;
       }
       if (score != 'undef' ){ // undef is default value, i.e. no answer chosen - for new forms
            gad7_score = gad7_score - Number(all_scores[index]);
            all_scores[index] = Number (score);
            gad7_score = gad7_score + Number(all_scores[index]) ;
        }
    }
    // decide which explanatory string to dispay for the new score
    if (gad7_score < 5 ) explanation = str_score_analysis[0];
    else if (gad7_score <15) explanation = str_score_analysis[1];
    else explanation = str_score_analysis[2];
    // create string to be display - the score plus the explanation
    total_string = gad7_score+" - "+explanation;
    if (total_digits) {//   replace previous total with new one
        total_digits.innerText = total_string;
    }
    else{ //or create a visible total
        total_digits = document.createElement("b");
        the_total_text = document.createTextNode(total_string);
        total_digits.appendChild(the_total_text);
        exp = document.createElement("span");
        exptext = document.createTextNode(explanation);
        exp.appendChild(exptext);
        place = document.getElementById("show_gad7_score");
        place.parentNode.appendChild( total_digits, place);
    }
    //when the total is larger than zero if necessary create and display the 8th questions as well
    // - else delete it from the display
     manage_question_8("undef"); // if the question is regenerated then use the 'please select an answer' value
    return true;
}
// record the answer to question 8
// the final question (index == 7) is not included in the sccore itself and is optional
function record_score_q8 (score) {
    all_scores[7] = Number(score); // record the scores for a review/edit of the form
}

