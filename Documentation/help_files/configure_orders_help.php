<?php

/**
 * Message Center Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!DOCTYPE html>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Message Center Help");?></title>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Configure Orders Help");?></a></h2>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <p><?php echo xlt("This page lets you configure the orders and results");?>.</p>

                    <p><?php echo xlt("Read through this help file and its supporting documents before you start to configure the orders and results");?>.</p>

                    <p><?php echo xlt("If you have not already done some pre-configuration, including adding providers, do so by going to Procedures > Providers");?>.</p>

                    <p><?php echo xlt("Read the help file there to understand the initial pre-configuration steps");?>.</p>

                    <p><?php echo xlt("Orders and Results are setup in an hierarchical manner, there are four tiers in this hierarchy");?>.</p>
                    <ul>
                        <li><?php echo xlt("Group"); ?></li>
                        <li><?php echo xlt("Procedure Order"); ?></li>
                        <li><?php echo xlt("Discrete Result"); ?></li>
                        <li><?php echo xlt("Recommendation"); ?></li>
                    </ul>

                    <p><?php echo xlt("Ordering tests individually can be tedious and there are two ways of ordering multiple tests together");?>.</p>

                    <ul>
                        <li><?php echo xlt("Ordering a recognized panel of tests"); ?></li>
                        <li><?php echo xlt("Creating a Custom Favorite Group to order frequently ordered tests together "); ?></li>
                    </ul>

                    <p><?php echo xlt("This help file is divided into the following sections");?>:</p>
                    <ul>
                        <li><a href="#section1"><?php echo xlt("Create an Order for a Single Test");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Create an Order for a Panel of Tests");?></a></li>
                        <li><a href="#section3"><?php echo xlt("Creating a Custom Group of Tests as Favorites");?>  <i class="fa fa-exclamation-circle oe-text-orange" aria-hidden="true"></i></a>&nbsp;<strong><?php echo xlt("New in openEMR ver 5.0.2 "); ?></strong></li>
                        <li><a href="#section4"><?php echo xlt("Configuring Multiple Orders");?></a></li>
                        <li><a href="#section5"><?php echo xlt("Electronic orders");?></a></li>
                    </ul>

                    <p><?php echo xlt("The first four sections deal with creating procedure orders in a structured manner in order to facilitate easy ordering of tests and entering the returned results manually");?>.</p>

                    <p><?php echo xlt("The last section deals with electronic orders");?>.</p>

                    <p><?php echo xlt("It is essential that you read through the first four sections to understand the underlying principles");?>.</p>

                    <p><?php echo xlt("It will help later in troubleshooting problems that may arise with your configuration of the Procedure Orders module");?>.</p>

                    <p><?php echo xlt("We will start with the most basic unit of the Procedure Orders module, to set up a single lab test order and the ability to manually record the returned result");?>.</p>
                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Create an Order for a Single Test"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("We will go through the process of setting up the order for a single blood test - Serum Uric Acid");?>.</p>

                    <p><i class="fa fa-lightbulb-o fa-lg  oe-text-green" aria-hidden="true"></i>&nbsp <?php echo xlt("To use this help file as an instruction manual it is best to resize your browser to occupy half the screen, open another instance of the browser to fill the other half of the screen, login to openEMR and open the help file in this browser and resize it by clicking and dragging the bottom right corner so that it occupies the entire half screen");?>.</p>

                    <p><strong><?php echo xlt("CREATE A TOP LEVEL ENTRY"); ?> :</strong></p>

                    <p><?php echo xlt("The first step would be to create a top level entry. Do so by clicking on Add Top Level button");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-add oe-no-float" ><?php echo xlt('Add Top Level');?></button>
                    </p>

                    <p><?php echo xlt("It will bring up the Enter Details pop-up form");?>.</p>

                    <p><?php echo xlt("Various text fields and drop-down boxes will be displayed");?>.</p>

                    <p><?php echo xlt("As this is an infrequently performed process additional help in provided by clicking on the help icon that is revealed when you hover over the label for each box");?>.</p>

                    <p><?php echo xlt("Select Group in the drop-down box labeled Procedure Tier");?>.</p>

                    <p><?php echo xlt("Give a Name to this group, in our case it will be called Serum Chemistry");?>.</p>

                    <p><?php echo xlt("A short description of this group, Serum chemistry tests");?>.</p>

                    <p><?php echo xlt("The sequence is the order in which this top order item will be displayed on the page, 1 to denote the first top level group");?>.</p>

                    <p><?php echo xlt("If you leave it at the default of 0 then the top level entries that you make will be sorted alphabetically");?>.</p>

                    <p><?php echo xlt("Click Save");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                    </p>

                    <p><?php echo xlt("This item will be displayed on the page on a single line");?>.</p>

                    <p><?php echo xlt("The first column is the name that you gave this entry, i.e. Serum Chemistry. A vertical bar will precede the actual name. That indicates that there are no descendants or children to this entry");?>.</p>

                    <p><?php echo xlt("The second column is Category and should say Top Group to indicate its position as the top item in this particular hierarchical tree that we will be constructing");?>.</p>

                    <p><?php echo xlt("The Code column will be empty as items listed as a Group are not used in the actual reporting of results and therefore do not have a distinct code number");?>.</p>

                    <p><?php echo xlt("The Tier Column would be 1 indicating it is the Top Group");?>.</p>

                    <p><?php echo xlt("The Description column displays the short description that was entered, Serum chemistry tests");?>.</p>

                    <p><?php echo xlt("This is followed by the Edit and Add columns containing a pencil icon and a + icon");?>. &nbsp; <i class="fa fa-pencil-alt"  aria-hidden="true"></i> &nbsp; <i class="fa fa-plus"  aria-hidden="true"></i></p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("It is important to understand the function of these two icons as all subsequent steps needed to properly set up the hierarchy will depend on the correct use of these two icons");?>.</strong></p>

                    <p><?php echo xlt("Clicking on the pencil icon will enter the Edit mode and will display the Enter Details pop-up window that was used to create the entry displayed on this line");?>. &nbsp; <i class="fa fa-pencil-alt"  aria-hidden="true"></i></p>

                    <p><?php echo xlt("Any changes that you need to make to this particular line item should be made here and saved");?>.</p>

                    <p><?php echo xlt("The Add icon is used to setup a tier that will be a direct descendant of this tier");?>.  &nbsp; <i class="fa fa-plus"  aria-hidden="true"></i></p>

                    <p><strong><?php echo xlt("CREATE AN ORDER"); ?> :</strong></p>

                    <p><?php echo xlt("We are going to create the test that can be ordered called Serum Uric Acid");?>.</p>

                    <p><?php echo xlt("Click on the Add icon at the far end of the Serum Chemistry line");?>.  &nbsp; <i class="fa fa-plus"  aria-hidden="true"></i></p>

                    <p><?php echo xlt("Select Procedure Order from Procedure Tier pop-up box");?>.</p>

                    <p><?php echo xlt("Note that the pop-up form heading will now read Enter Details for Individual Procedures");?>.</p>

                    <p><?php echo xlt("Several additional drop-down and text boxes will be visible");?>.</p>

                    <p><?php echo xlt("Enter Serum Uric Acid in the Name box");?>.</p>

                    <p><?php echo xlt("Enter Serum Uric Acid order in the Description box");?>.</p>

                    <p><?php echo xlt("Enter 1 under sequence, more about sequence numbers later");?>.</p>

                    <p><?php echo xlt("Select a lab from the Order From drop-down box");?>.</p>

                    <p><?php echo xlt("In our case we will choose Local Lab that was setup previously");?>.</p>

                    <p><?php echo xlt("If you see no entries in the Order From drop-down box it means that you have not set up a lab under Procedures > Providers");?>.</p>

                    <p><?php echo xlt("You need to go back and and create a provider");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("The next two boxes denoting the Identifying Code and Standard Code are very important");?>.</strong></p>

                    <p><?php echo xlt("The Identifying Code is a vendor-specific code identifying this procedure or result. You can see examples of it in the paper lab slips that the labs use");?>.</p>

                    <p><?php echo xlt("One such lab uses 905 for Uric Acid and in our example we will enter that number in the box");?>.</p>

                    <p><?php echo xlt("If there is no vendor enter any arbitrary unique number, preferably a 5 digit zero-padded e.g. 00211");?>.</p>

                    <p><?php echo xlt("The Identifying Code is essential for the proper display and tabulation of results");?>.</p>

                    <p><?php echo xlt("The Standard Code is optional if using a local i.e. practice based lab but recommended when using an external lab");?>.</p>

                    <p><?php echo xlt("Enter the Logical Observation Identifiers Names and Codes (LOINC) code for this procedure");?>.</p>

                    <p><?php echo xlt("LOINC, rhymes with oink, is a database and universal standard for identifying medical laboratory observations");?>.
                        <a href="https://loinc.org/" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    </p>

                    <p><?php echo xlt("The LOINC code for serum uric acid is 3084-1, LOINC refers to it as Urate in Serum or Plasma");?>.</p>

                    <p><?php echo xlt("Many of the LOINC codes can be used for both tests and their results");?>.</p>

                    <p><?php echo xlt("You can download the Loinc Universal LabOrders ValueSet.csv file from here");?>.
                        <a href="https://lhncbc.nlm.nih.gov/project/top-loinc-codes-%E2%80%93-orders-and-observations" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a>
                    </p>

                    <p><?php echo xlt("Alternatively a CPT code can be used. It is 84550 for Serum Uric Acid");?>.</p>

                    <p><?php echo xlt("It is not necessary for local lab, instead you can use any arbitrary unique number");?>.</p>

                    <p><?php echo xlt("The next box is Body Site, not relevant for a blood test but may be more useful in say tracking injections or ordering X-rays");?>.</p>

                    <p><?php echo xlt("The values in the drop-down box can be edited by following the steps outlined in the Procedures > Providers help file");?>.</p>

                    <p><?php echo xlt("In our case we will leave it as Unassigned");?>.</p>

                    <p><?php echo xlt("The Specimen Type will be Blood, this drop-down list can also be edited as needed");?>.</p>

                    <p><?php echo xlt("Administer Via is not relevant in this context and can be left Unassigned");?>.</p>

                    <p><?php echo xlt("Likewise Laterality is not relevant in this context and can be left Unassigned");?>.</p>

                    <p><?php echo xlt("Click Save to save and close the pop-up");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                    </p>

                    <p><?php echo xlt("Notice how the Top Group entry that was initially created called Serum Chemistry now has a blue + sign before its name indicating the presence of descendants or children to this entry");?>.</p>

                    <p><?php echo xlt("Click on the blue + sign to see the Procedure Order that was saved");?>.</p>

                    <p><?php echo xlt("If you have followed all the above steps correctly you should see Serum Uric Acid under Name, it will be preceded by a vertical bar indicating the absence of descendants or children to this entry");?>.</p>

                    <p><?php echo xlt("The Category column will say Order indicating that it is an order");?>.</p>

                    <p><?php echo xlt("Both Name and Category will be highlighted yellow to provide an additional visual clue that this is an order. A fact that will be appreciated when several orders are entered in the system along with their respective groups and results");?>.</p>

                    <p><?php echo xlt("The Code column should have 905, the vendor specific Identifying code that was entered previously");?>.</p>

                    <p><?php echo xlt("The Tier column will be 2, indicating this is a successor or child to the first line above it having a value of 1");?>.</p>

                    <p><?php echo xlt("The Description column will reflect the description value that was entered previously");?>.</p>

                    <p><strong><?php echo xlt("CREATE A LOCATION FOR RESULTS"); ?> :</strong></p>

                    <p><?php echo xlt("An order has now been successfully created, however a place to receive and document the returned result does not exist as yet and needs to be created");?>.</p>

                    <p><?php echo xlt("For proper display of the order and results the entry indicating a result has to be the immediate successor or child of an order");?>.</p>

                    <p><?php echo xlt("To do so click on the black + sign at the far end on the Serum Uric Acid order line to bring up the Enter Details pop-up");?>.&nbsp; <i class="fa fa-plus"  aria-hidden="true"></i></p>

                    <p><?php echo xlt("Select Discrete Result from the Procedure Tier drop-down box");?>.</p>

                    <p><?php echo xlt("Note that the pop-up form heading will now read Enter Details for Discrete Results");?>.</p>

                    <p><?php echo xlt("Enter Serum Uric Acid under Name");?>.</p>

                    <p><?php echo xlt("Enter Serum Uric Acid result under Description");?>.</p>

                    <p><?php echo xlt("Enter a sequence 1");?>.</p>

                    <p><?php echo xlt("The Identifying Code has to be unique in order for the results to display correctly, you could just use R905 as an example");?>.</p>

                    <p><?php echo xlt("Select MG/DL as Default Units");?>.</p>

                    <p><?php echo xlt("If you do not find a required value in the drop-down box you will need to add it via Administration > Lists > Procedure Units. Refer to the help file in Procedures > Providers");?>.</p>

                    <p><?php echo xlt("Enter the values 3.4 - 7.2 - Men, 2.4–6.1 - Women");?>.</p>

                    <p><?php echo xlt("Click to select services to perform if this result is abnormal. This is optional");?>.</p>

                    <p><?php echo xlt("Click Save to close the pop-up window");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                    </p>

                    <p><?php echo xlt("Click Refresh and click on the blue + mark that precedes the name Serum Chemistry");?>.</p>

                    <p><?php echo xlt("If you have followed the above steps correctly the Serum Uric Acid Test should now have a + sign preceding the Name indicating the presence of successors or children");?>.</p>

                    <p><?php echo xlt("Click on the + sign adjacent to the Serum Uric Acid test Name to reveal the newly created Result line");?>.</p>

                    <p><?php echo xlt("The Result Line will begin with a Name Serum Uric Acid that will be preceded by a vertical bar indicating no successors or children");?>.</p>

                    <p><?php echo xlt("The Category will be Result");?>.</p>

                    <p><?php echo xlt("The Code will be R905, the Identifying Code value that was entered for the Discrete Result");?>.</p>

                    <p><?php echo xlt("The Tier column value will be 3, indicating this is a successor or child to the first line above it having a value of 2");?>.</p>

                    <p><?php echo xlt("The Description should say Serum Uric Acid result");?>.</p>

                    <p><?php echo xlt("Click Save to close the pop-up");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                    </p>

                    <p><?php echo xlt("The Recommendation Tier is optional");?>.</p>

                    <p><?php echo xlt("You have now successfully completed an order for a single lab test");?>.</p>

                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Create an Order for a Panel of Tests"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("In the above example an order for a single test was created, i.e. Serum Uric Acid");?>.</p>

                    <p><?php echo xlt("To recap, a Group called Serum Chemistry (Tier 1) was created, a direct descendant or child called Serum Uric Acid (Tier 2) was created beneath it as a Procedure Order which had a single direct descendant or child also called Serum Uric Acid (Tier 3) as a Discrete Result that will hold the result value");?>.</p>

                    <p><?php echo xlt("Using a fruit tree as an easier to visualize analogy will explain the concept of this hierarchy better, the trunk of the tree is the Group (Serum Chemistry), the fruit bearing branch is the actual order, Procedure Order (Serum Uric acid) and the fruit is the result, Discrete Result, also called (Serum Uric Acid) that will hold the value of the returned result. Just as the fruit is not borne on the main trunk you should not place a Discrete Result as a direct descendant or child of a Top Group. Similarly the fruit bearing branch (Procedure Order) cannot be placed as a direct descendant or child of a fruit (Discrete Result)");?>.</p>

                    <p><?php echo xlt("It is not uncommon to order several blood tests together, grouping them as a panel of tests facilitates this process");?>.</p>

                    <p><?php echo xlt("The American Medical Association has defined 9 panels that can be ordered. Each panel contains several tests grouped together");?>.</p>

                    <p><?php echo xlt("They are Electrolyte Panel, Basic Metabolic Panel, Comprehensive Metabolic Panel, Renal Function Panel, General Health Panel, Obstetric Panel, Acute Hepatitis panel, Hepatic Function Panel and Lipid Panel");?>.</p>

                    <p><?php echo xlt("We will set up the order for the Electrolyte panel that consists of Serum Sodium, Potassium, Chloride and Carbon Dioxide");?>.</p>

                    <p><?php echo xlt("Using this fruit tree analogy we can get a better understanding of the steps needed to create an order for a panel of tests");?>.</p>

                    <p><?php echo xlt("We will be basing the Electrolyte panel off the main trunk or Group (Serum Chemistry)");?>.</p>

                    <p><?php echo xlt("Start by clicking on the black plus sign at the end of the Serum Chemistry line to bring up the Enter Details pop-up");?>.</p>

                    <p><?php echo xlt("Select Group under Procedure Tier, think of it as creating a large branch off the main tree trunk, and call it Organ/Disease Panel");?>.</p>

                    <p><?php echo xlt("We will use this branch to hold all the 9 panels, here we will be just creating the order representing one panel, the Electrolyte Panel");?>.</p>

                    <p><?php echo xlt("Fill in the details for the Organ/Disease Panel as before and click Save to close the pop-up");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                    </p>

                    <p><?php echo xlt("If you Click the blue + sign on Serum Chemistry you will see a new line called Organ/Disease Panel with a Category of Sub Group");?>.</p>

                    <p><?php echo xlt("It will have a value of 2 in the Tier column indicating it is a successor or child on the first line above having a Tier value of 1 which in this case is Serum Chemistry");?>.</p>

                    <p><?php echo xlt("We will now create the order, or Procedure Order, called Electrolyte Panel that can be visualized as the fruit bearing branch in our hypothetical fruit tree");?>.</p>

                    <p><?php echo xlt("Select Procedure Order in Procedure Tier");?>.</p>

                    <p><?php echo xlt("Type in Electrolyte Panel as the Name");?>.</p>

                    <p><?php echo xlt("Give it a sequence number of 3");?>.</p>

                    <p><?php echo xlt("Select the Lab From lab name");?>.</p>

                    <p><?php echo xlt("For Identifying Code use 34392 the code used by a major lab, if using an external lab this is a vendor specific code if not you can assign any unique value");?>.</p>

                    <p><?php echo xlt("For the Standard Code use the LOINC code 24326-1");?>.</p>

                    <p><?php echo xlt("Use Blood or Serum as Specimen Type");?>.</p>

                    <p><?php echo xlt("Click Save and then Refresh");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                        <button type="button" class="btn btn-secondary btn-sm btn-refresh oe-no-float" ><?php echo xlt('Refresh');?></button>
                    </p>

                    <p><?php echo xlt("If you drill down to the Electrolyte Panel line you should see it have a Category of Order and be highlighted in yellow indicating that it is a search-able and valid order and has a Tier value of 3");?>.</p>

                    <p><?php echo xlt("The results, Discrete Result, will be the fruit on this branch and we will create one such Discrete Result for each of the component tests, Sodium, Potassium, Chloride and Carbon Dioxide");?>.</p>

                    <p><?php echo xlt("Click on the black + sign on the Electrolyte Panel line to create a direct descendant or child");?>.&nbsp; <i class="fa fa-plus"  aria-hidden="true"></i></p>

                    <p><?php echo xlt("Select Discrete Result under Procedure Tier in the Enter Details pop-up");?>.</p>

                    <p><?php echo xlt("Type Sodium under Name and Sodium result under description");?>.</p>

                    <p><?php echo xlt("The Identifying Code of R34392-1, an arbitrary number, in this case the Identifying Code preceded by R to indicate result and succeeded by 1 to indicate the first in the sequence, it could be anything unique you choose");?>.</p>

                    <p><?php echo xlt("Default Units and Range as appropriate");?>.</p>

                    <p><?php echo xlt("Leave the Followup Services blank and click Save");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                    </p>

                    <p><?php echo xlt("If you drill down to the Electrolyte Panel line and click on the + sign in the Name column you should see Sodium listed underneath it with a Category value of Result with a Tier value of 4");?>.</p>

                    <p><?php echo xlt("In a likewise fashion you will create a Discrete Result for Potassium, Chloride and Carbon Dioxide");?>.</p>

                    <p><?php echo xlt("Take care and click on the black + sign on the Electrolyte Panel line to create these Discrete Results");?>.&nbsp; <i class="fa fa-plus"  aria-hidden="true"></i></p>

                    <p><?php echo xlt("Remember these Discrete Results or fruit (Tier 4)  will have to hang off the fruit bearing branch or Procedure Order namely Electrolyte Panel (Tier 3)");?>.</p>

                    <p><?php echo xlt("If they have been configured correctly each result should have a Tier value of 4, indicating they are the successor or child to the first line above with a Tier value of 3, i.e Electrolyte Panel");?>.</p>

                    <p><?php echo xlt("You should not make Discrete Result for Potassium be a descendant of a preceding Discrete Result i.e Sodium , i.e have a Tier value of 5");?>.</p>

                    <p><?php echo xlt("Visualize this as not making a fruit hang off another fruit. Instead it should hang off the fruit bearing branch");?>.</p>

                    <p><?php echo xlt("To create another panel of tests you will repeat this process but will start by creating a Procedure Order or fruit bearing branch Tier 3, for example - Acute Hepatitis panel under the main branch or Sub Group Organ/Disease Panel (Tier 2)");?>.</p>

                    <p><?php echo xlt("An important point to remember is that the various individuals tests in each panel are listed as Results, the fruit, you cannot directly order these individual tests");?>.</p>

                    <p><?php echo xlt("You can only place an order for Electrolyte Panel and have the entire panel tested and have their results returned");?>.</p>

                    <p><?php echo xlt("If you have the need to order individual components of the panel you will have to create a separate Procedure Order for each individual component of the panel and store the returned result under a Discrete Result created in a fashion similar to that used earlier to create an order for Serum Uric Acid");?>.</p>

                    <p><?php echo xlt("You can use the above method to create orders for the rest of the Organ/Disease Panels except for the General Health Panel that consists of CBC, Comprehensive Metabolic Panel and TSH and the Obstetric Panel that has CBC as one of its components");?>.</p>

                    <p><?php echo xlt("In this case you will create a Procedure Order, the fruit bearing branch called General Health Panel (Tier 3)");?>.</p>

                    <p><?php echo xlt("When such an order is placed the returned results will contains the values for all components of CBC, Comprehensive Metabolic Panel and TSH");?>.</p>

                    <p><?php echo xlt("In order to record the values of the results returned you have to create a separate Discrete Result (Tier 4) for each of the panel's constituents, the fruit, as direct descendants of this (General Health Panel) branch (Tier 3). Examples of the results being CBC - Hemoglobin, CBC - Hematocrit, CMP - Sodium, CMP - Potassium, TSH etc. ");?>.</p>
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Creating a Custom Group of Tests as Favorites"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Before we proceed to create a Custom Group of Tests and save it as a Favorite a quick recap of the principles involved");?>.</p>

                    <p><?php echo xlt("Thus far the tests that can be ordered were placed in the Procedure Order Tier, only one test at a time can be placed in this tier, when ordering a recognized panel of tests the entire panel of tests is grouped together as a single test e.g. Sodium, Potassium, Chloride and Carbon dioxide grouped as Electrolyte Panel. The results of the individual returned tests in the panel are recorded as Discrete Results");?>.</p>

                    <p><?php echo xlt("The Discrete result Tier must be the direct descendant or child of a Procedure Order Tier for proper display of results");?>.</p>

                    <p><?php echo xlt("A new feature helps to group frequently ordered tests so that they can be ordered together");?>.</p>

                    <p><?php echo xlt("It has three tiers ");?>:</p>

                    <ul>
                        <li><?php echo xlt("Custom Favorite Group - to group individual tests "); ?> <i class="fa fa-exclamation-circle oe-text-orange" aria-hidden="true"></i>&nbsp;<strong><?php echo xlt("New in openEMR ver 5.0.2 "); ?></strong></li>
                        <li><?php echo xlt("Custom Favorite Item  - for individual orders or tests"); ?> <i class="fa fa-exclamation-circle oe-text-orange" aria-hidden="true"></i>&nbsp;<strong><?php echo xlt("New in openEMR ver 5.0.2 "); ?></strong></li>
                        <li><?php echo xlt("Discrete Results - for manual entry of returned result values"); ?></li>
                    </ul>

                    <p><?php echo xlt("We will be creating an order for three tests Blood Glucose, TSH and Vitamin D and group it in a custom group called Well Woman Tests and create a place to manually enter the returned results");?>.</p>

                    <p><strong><?php echo xlt("CREATE A TOP LEVEL CUSTOM GROUP"); ?> :</strong></p>

                    <p><?php echo xlt("Start by clicking on Add Top Level to bring up the Enter Details pop-up box in the Add Mode");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-add oe-no-float" ><?php echo xlt('Add Top Level');?></button>
                    </p>

                    <p><?php echo xlt("Select Custom Favorite Group from the Procedure Tier drop-down box");?>.</p>

                    <p><?php echo xlt("Note that the pop-up form heading will now read Enter Details for Custom Favorite Group");?>.</p>

                    <p><?php echo xlt("Enter the details - Name - Well Woman Tests, Description - Well Woman Tests, an appropriate sequence number depending on the procedure orders already entered, Order From - Local Lab");?>.</p>

                    <p><?php echo xlt("Unlike the regular Groups that were created earlier, each Custom Favorite Group has an Identifying Code that has to be unique. As this is our custom group and not a lab recognized panel we will give it our arbitrary unique Identifying Code - CFGWWT001");?>.</p>

                    <p><?php echo xlt("Click Save to create a new line with a Name of Well Womans Tests preceded by a vertical line indicating that it has no descendants or children");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                    </p>

                    <p><?php echo xlt("The Category column will be Custom Top Group and will be highlighted in pink");?>.</p>

                    <p><?php echo xlt("Unlike the regular Top Groups created thus far the Code column will have a value CFGWWT001");?>.</p>

                    <p><?php echo xlt("The Tier column value will be 1 indicating it is a top level item");?>.</p>

                    <p><strong><?php echo xlt("CREATE THE CUSTOM ORDERS"); ?> :</strong></p>

                    <p><?php echo xlt("Click on the black + sign at the far end of the Well Woman Tests line to create a direct descendant or child to this Custom Top Group");?>.</p>

                    <p><?php echo xlt("The Enter Details pop-up box will open in the Add Mode");?>.</p>

                    <p><?php echo xlt("Select Custom Favorite Item in the Procedure Tier drop-down box");?>.</p>

                    <p><?php echo xlt("The pop-up form heading will now read Enter Details for Individual Custom Favorite Item");?>.</p>

                    <p><?php echo xlt("Enter the following: Name - Blood Glucose, Description - Fasting Blood Glucose, Sequence - 1, Order From - Local Lab");?>.</p>

                    <p><?php echo xlt("The Identifying code has to be unique, if sending to an external lab the vendor supplied Identifying Code has to be entered. We will enter an arbitrary unique value - WWT01");?>.</p>

                    <p><?php echo xlt("The Standard Code will be the LOINC code if sending to an external lab or can be any unique number. We will enter the LOINC code for Fasting Glucose 1558-6");?>.</p>

                    <p><?php echo xlt("Unlike the regular Procedure Order a Diagnostic Codes box is present allowing you to enter one or more ICD10 Diagnosis Codes as default diagnoses for this order");?>.</p>

                    <p><?php echo xlt("Click on the Diagnosis Codes box to open the Select Diagnosis Codes pop-up window");?>.</p>

                    <p><?php echo xlt("If the displayed table says No matching record found it means that the ICD10 code set is not installed");?>.</p>

                    <p><?php echo xlt("Install it by going to Administration > Other > External Data Loads > ICD10 > Staged Releases and click Install");?>.</p>

                    <p><i class="fa fa-exclamation-triangle oe-text-red"  aria-hidden="true"></i> <strong><?php echo xlt("You will need Administrator Privileges to install the ICD10 code set");?>.</strong></p>

                    <p><?php echo xlt("Now when you bring up the Select Diagnosis Codes pop-up you will see all the ICD10 codes listed");?>.</p>

                    <p><?php echo xlt("You can search for and select the codes by clicking once on each line containing the code");?>.</p>

                    <p><?php echo xlt("This will copy the selected code to the Diagnosis Codes box in the underlying Enter Details for Individual Custom Favorite Item pop-up form");?>.</p>

                    <p><?php echo xlt("If you had selected multiple codes they will be separated by semi-colons");?>.</p>

                    <p><?php echo xlt("To delete any or all the selected ICD10 codes click again on the Diagnosis Codes box to bring up the Select Diagnosis Codes pop-up window");?>.</p>

                    <p><?php echo xlt("Click the Delete button to delete all selected codes or select individual codes to be deleted from the drop-down box adjacent to the delete button and then click the Delete button");?>.</p>

                    <p><?php echo xlt("It is not necessary to fill default Diagnosis Codes as ICD10 codes can be entered at the time of the actual ordering of the test");?>.</p>

                    <p><?php echo xlt("The Body Site, Administer Via and Laterality boxes can be left as Unassigned");?>.</p>

                    <p><?php echo xlt("Select Blood in the Specimen Type drop-down box");?>.</p>

                    <p><?php echo xlt("Click Save");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                    </p>

                    <p><?php echo xlt("If configured correctly the Name column should say Blood Glucose with a vertical bar before it indicating no descendants or children, the Category column should say Custom Order and both Name and Category columns will be highlighted in pink indicating a custom grouped order");?>.</p>

                    <p><?php echo xlt("In a likewise manner create a Custom Favorite Item each for TSH (Sequence 2, Identifying Code WWT02, Standard Code 30166-3) and Vitamin D (Sequence 3, Identifying Code WWT03, Standard Code 35365-6)");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("Be sure to branch it off the Well Woman Tests line (Tier 1), if properly branched both TSH and vitamin D should have a Tier value of 2");?>.</strong></p>

                    <p><strong><?php echo xlt("CREATE A LOCATION FOR RESULTS"); ?> :</strong></p>

                    <p><?php echo xlt("As we are creating a Custom Group for manual entry of returned results we need to create a place to enter these results");?>.</p>

                    <p><?php echo xlt("We will use a Discrete Result for each test to hold the returned result values");?>.</p>

                    <p><?php echo xlt("Click on the + sign at the far end of the Blood Glucose line to bring up the Enter Details pop-up in the Add Mode");?>.</p>

                    <p><?php echo xlt("Select Discrete Result from the Procedure Tier");?>.</p>

                    <p><?php echo xlt("Enter the details as follows Name - Blood Glucose, Description - Fasting Glucose result, Sequence - 1, Identifying Code - any unique value - WWT01R, Default Units - mg/dL, Default Range - 70-100, leave Followup Services blank and click Save");?>.
                        <button type="button" class="btn btn-secondary btn-sm btn-save oe-no-float" ><?php echo xlt('Save');?></button>
                    </p>

                    <p><?php echo xlt("If all the details were entered correctly you will see Blood Glucose in the Name column with a vertical bar before it indicating that it has no descendants or children, Result in the Category column, WWT01R in the Code column, the Tier column value should be 3 and the Description column should say Fasting Glucose result");?>.</p>

                    <p><?php echo xlt("In a likewise manner create a Discrete Result each for TSH (Sequence - 2, Identifying Code - WWT02R, Default Units - mU/L, Default Range - 0.4-4, leave Followup Services blank) and Vitamin D (Sequence - 3, Identifying Code - WWT03R, Default Units - ng/mL, Default Range - 0-20, leave Followup Services blank)");?>.</p>

                    <p><?php echo xlt("When you place an order you can search for Well Woman Tests under favorites and select it to automatically order Blood Glucose, TSH and Vitamin D with a single click");?>.</p>

                    <p><?php echo xlt("Unlike ordering a recognized panel as a single test and thus have all the component tests performed these three tests will be presented as three individual tests");?>.</p>

                    <p><?php echo xlt("This gives you the option to delete any unwanted individual test in the group");?>.</p>

                    <p><?php echo xlt("The custom grouping will also let you group two recognized panel of tests into a custom group and thus order them together, e.g. a custom group of Preop Labs may contain two panels, CBC and Renal Panel");?>.</p>

                    <p><?php echo xlt("In this case the Custom Favorite Group will be called Preop Labs and the two recognized panels will be direct descendants, each in a Custom Favorite Item Tier named CBC and Renal Panel respectively");?>.</p>

                    <p><?php echo xlt("The Discrete Results for each panel will contain the names of the individual tests to hold the values of the returned results - WBC, Hemoglobin, Platelets, Sodium, Potassium etc");?>.</p>

                    <p><i class="fa fa-exclamation-triangle oe-text-red"  aria-hidden="true"></i> <strong><?php echo xlt("Remember the dictum that Identifying codes have to be unique, you should not set up a custom grouping of a panel of tests as well have the panels setup as individual recognized panel, i.e. have CBC and Renal panel grouped as a Custom Favorite and list CBC and Renal Panel separately as individually ordered panels");?>.</strong></p>

                    <p><?php echo xlt("If these tests are being sent to the same external lab they will have identical vendor specific Identifying Codes causing duplicate lines to appear when results are displayed");?>.</p>

                    <p><?php echo xlt("To summarize");?>:</p>

                     <ul>
                        <li><?php echo xlt("Individual tests or even individual recognized panel of tests can be grouped together as a Custom Favorite Group"); ?></li>
                        <li><?php echo xlt("Each test or panel in this group has to be in an individual Custom Favorite Item tier"); ?></li>
                        <li><?php echo xlt("The Custom Favorite Item Tier should be the direct descendant or child of the relevant Custom Favorite Group"); ?></li>
                        <li><?php echo xlt("If you need to manually enter the returned result each test (Custom Favorite Item) should have a Discrete Result as a direct descendant"); ?></li>
                        <li><?php echo xlt("For proper display of results the Identifying Codes for each test entered in the Procedure Orders module has to be unique"); ?></li>
                        <li><?php echo xlt("These custom groups are application specific and not user specific"); ?></li>
                    </ul>
                </div>
            </div>
            <div class= "row" id="section4">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Configuring multiple orders"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>

                    <p><?php echo xlt("For the Procedure Module to be useful several tests have to be setup using the above methods");?>.</p>

                    <p><?php echo xlt("Resist the temptation to start entering data in an unorganized manner");?>.</p>

                    <p><?php echo xlt("Careful planning is essential before entering the tests to ensure subsequent ease of use and proper display of results");?>.</p>

                    <p><?php echo xlt("A useful start would be to obtain a paper lab slip from a few labs and look at the way the tests are organized on the lab slip");?>.</p>

                    <p><?php echo xlt("Based on the individual practice's need the tests can be organized into various groups");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <strong><?php echo xlt("Remember grouping of tests is only for conveniently organizing the data in the Procedure Order module in openEMR, for this process to work, configuring the Procedure Orders (the tests themselves) and the Discrete Result (the place to enter and display the returned result) are critically important");?>.</strong></p>

                    <p><?php echo xlt("Using the main headings on the lab slip will help start this process - Organ/Disease Panels, Hematology, Individual Tests, Microbiology, Other");?>.</p>

                    <p><?php echo xlt("Decide whether or not you will want to order individual tests which are a part of a panel of tests");?>.</p>

                    <p><?php echo xlt("If so you will have to create individual Procedure Orders (the orders) for these tests along with Discrete Results (the place to enter and display the returned result)");?>.</p>

                    <p><?php echo xlt("Decide on the Identifying Codes and Standard Codes, remember the Identifying code has to be unique to ensure proper display of results");?>.</p>

                    <p><?php echo xlt("You can use the codes given on the paper lab slip of a local lab of major lab as the Identifying codes");?>.</p>

                    <p><?php echo xlt("You can use LOINC codes for the Standard Codes");?>.</p>

                    <p><?php echo xlt("You can download the Loinc Universal LabOrders ValueSet.csv file from here");?>.
                        <a href="https://lhncbc.nlm.nih.gov/project/top-loinc-codes-%E2%80%93-orders-and-observations" rel="noopener" target="_blank"><i class="fa fa-external-link-alt text-primary" aria-hidden="true" data-original-title="" title=""></i></a></p>
                    </p>

                    <p><?php echo xlt("Open a spreadsheet and type in the various groups, procedure orders and discrete results, arrange according to need");?>.</p>

                    <p><?php echo xlt("Alternatively download a sample spreadsheet by clicking on the Download button"); ?>. &nbsp <a href="../../interface/orders/configure_orders_worksheet.ods" class= "btn btn-secondary btn-sm btn-download oe-no-float" download="Configure Orders Worksheet" rel="noopener" target="_blank"> <?php echo xlt("Download"); ?></a></p>

                    <p><?php echo xlt("Ensure that Identifying Codes are unique");?>.</p>

                    <p><?php echo xlt("Check the Sequence numbers");?>.</p>

                    <p><?php echo xlt("Now is the time to check and adjust the settings in openEMR for default units, sites, etc., refer to the help file in Procedures > Providers");?>.</p>

                    <p><?php echo xlt("Before you start entering data check the spreadsheet once again and make adjustments as needed");?>.</p>

                    <p><?php echo xlt("It is far easier and intuitive to make major changes and reorganize the data on the spreadsheet before you start entering the data in openEMR");?>.</p>

                    <p><?php echo xlt("It is useful to practice on a trial site to become familiar with the process before doing so on the production openEMR application");?>.</p>

                    <p><?php echo xlt("Set aside some uninterrupted time for the data entry");?>.</p>

                    <p><?php echo xlt("Start entering the data in an orderly manner, tier by tier, one top order group at a time");?>.</p>

                    <p><?php echo xlt("This is the recommended method for a non-technical user, if executed carefully will reduce configuration errors");?>.&nbsp;<i class="fa fa-smile-o fa-lg" aria-hidden="true"></i></p>
                </div>
            </div>
            <div class= "row" id="section5">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Electronic orders"); ?><a href="#"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("The system is designed to both place orders electronically and receive the results electronically");?>.</p>

                    <p><?php echo xlt("It can also just receive results electronically");?>.</p>

                    <p><?php echo xlt("Before you try out electronic ordering you need to understand the issues involved");?>.</p>

                    <p><?php echo xlt("What constitutes the order and what is its electronic format, how is it sent, how is the result received, if the system will only receive results or send orders and receive results, what labs you will be connecting to and what needs to happen at the other end, whether or not a third party will be involved in this transaction");?>.</p>

                    <p><?php echo xlt("An electronic order and the returned result is in the form of a message called the HL7 message. HL7 stands for Health Level (Application Layer) 7. HL7’s prime objective is to simplify the implementation of interfaces between healthcare software applications and various vendors so as to reduce the pain and cost involved in custom interface programming");?>.</p>

                    <p><?php echo xlt("HL7 is supported by more than 1,600 members from over 50 countries, making it a widely accepted standard");?>.</p>

                    <p><?php echo xlt("There are several versions of HL7, versions 2.x and version 3");?>.</p>

                    <p><?php echo xlt("The latest version is FHIR – Fast Healthcare Interoperability Resources – is a next generation standards framework created by HL7. This is gaining popularity but has not yet been widely implemented");?>.</p>

                    <p><?php echo xlt("Version 2.x is widely used in the healthcare industry and openEMR uses HL7 version 2.3 ");?>.</p>

                    <p><?php echo xlt("A HL7 v2.x message simply consists of several lines of text, each line is called a segment and is further divided into fields by using the pipe (|) character");?>.</p>

                    <p><?php echo xlt("Each segment is identified by a unique three letter header that constitutes the first three letters on that line");?>.</p>

                    <p><?php echo xlt("The first Segment in every HL7 Message is always the Message Header, a Segment that conveys the metadata of the message like who sent it and when. The Message header is indicated in the first three letters of the segment as MSH");?>.</p>

                    <p><?php echo xlt("The other Segments contain additional information in a strictly structured fashion");?>.</p>

                    <p><?php echo xlt("Using the details entered into the system openEMR will generate valid HL7 v2.3 messages in 3 formats, a text file that is automatically downloaded to the downloads folder of your browser, a HL7 message that is written to a folder or directory on the server running openEMR or use SFTP - Secure File Transfer Protocol, which is a network protocol that provides file access, file transfer, and file management over a secure connection and transfer this message to a lab");?>.</p>

                    <p><?php echo xlt("To enable any other form of connectivity will require writing new code");?>.</p>

                    <p><?php echo xlt("The system is also designed to parse incoming HL7 messages and place the results in the appropriate patient chart, failing which it will give you an opportunity to manually link it to a patient chart");?>.</p>

                    <p><?php echo xlt("The most important issue that needs to be addressed is who you are trying to connect to and what needs to happen at their end. Most major labs will not deal with individuals practices, in such cases a third party vendor will act an an intermediary who will be responsible for setting up a connection between the practice and the lab through their (the intermediary's) interface");?>.</p>

                    <p><?php echo xlt("Some smaller local labs may agree to deal directly with the practice");?>.</p>

                    <p><?php echo xlt("In any case establishing a connection, ensuring HIPAA compliance, data security etc will require coding, the cost of which is generally borne by the lab. They however will only approve if the volume of business justifies their investment");?>.</p>

                    <p><?php echo xlt("In short electronic orders can be done, it involves a significant amount of testing and customization to be certified by the lab, generally through a third party and can only happen if the lab agrees to give the green light to the project");?>.</p>
                </div>
            </div>
        </div><!--end of container div-->
        <script>
           $('#show_hide').click(function() {
                var elementTitle = $('#show_hide').prop('title');
                var hideTitle = '<?php echo xla('Click to Hide'); ?>';
                var showTitle = '<?php echo xla('Click to Show'); ?>';
                $('.hideaway').toggle('1000');
                $(this).toggleClass('fa-eye-slash fa-eye');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                }
                $('#show_hide').prop('title', elementTitle);
            });
        </script>
    </body>
</html>
