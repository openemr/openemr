Documentation for CAMOS

Installation

Basic Usage

Advanced Usage

Comments.  CAMOS supports C style comments.  Here is how they work.  A comment opens with /* and closes with */.  You cannot nest comments.  You cannot overlap comments.  Anything in a comment will be seen within CAMOS in the item window, but it will not show when you enter it into the patient's chart.  This is useful for making notes on items that are not appropriate to go in the chart.  You may want, for example, to document which insurance a particular facility accepts. 

/* Accepts BC/BS */   ok
/* rx list follows */ ok
/* /*     */ */       not right

Embedded functions.  CAMOS supports an interface for entering multiple billing codes encoded into a CAMOS item.  It works by putting the specially formatted function call within a comment.  A function is designated by a keyword followed by a '::', followed by an argument list for the function with each argument seperated by '::'.  An example should make this more clear...

If you want to enter an entry into the billing table to have, for example, a CPT4 or ICD9 code come up after entering a CAMOS form into a patient's chart, do this:

/*billing::CPT4::80054::CMP:: :: ::50.00*/ /*add a cmp for $50*/ 
/*billing::CPT4::85024::CBC:: :: ::25.00*/ /*add a cbc*/ 
/*billing::CPT4::80061::Lipid Panel:: :: ::50.00*/ 
/*billing::ICD9::V70.0::physical:: :: ::0.00*/ /*this is a diagnosis, not a procedure*/ 

The above is all legal in an item's text and may be interspersed with the regular text that is to be entered into the patient's chart.  Let's break down one of the above examples:

The whole structure of each function call is contained within /* */, the comment symbols.  The first word is a key word which is the function name.  This is followed by a '::' which is followed by more words or numbers or blank spaces, each separated by a '::'.  Each element between the '::' delimiter is an argument of the function, a piece of data to be processed.  Where you see just a blank space, this is an argument which has been left out in this case.  To really understand what one of these function calls means, you will need to know what arguments the function billing, in this example, is expecting.  Here it is:

/*billing::code_type::code::code_text::modifier::units::fee*/

One important thing to note is that CAMOS does not enforce appropriate data entry here.  Where OpenEMR, in general, restricts the user from entering bad data in many circumstances, you have a little more freedom here, so be careful.  As of right now, there are only a limited number of code types.  CPT4 and ICD9 are two common examples.  This method of inserting new entries to the billing table allows a user to create new code types which may not make sense and will not be interpreted correctly in other areas of OpenEMR.  On the other hand, if you know what you are doing, having the freedom from inside the program to create new code types might be useful.  Same goes for the code and code text.  These do not have to coincide with data in the code tables.  This flexibility can be useful if used carefully.

Regarding justification of codes:

If you want to justify an encounter code with an one or more ICD9, CAMOS has a way for you to do it with the billing function.  Just add the codes to be used in justifying as additional parameters at the end.

/*billing::code_type::code::code_text::modifier::units::fee::icd9_a::icd9_b::icd9_c*/

As you can see in the function signature above, you just add as many codes at the end as you need for justification.  Again, correctness here is not enforced.  You need to be sure that your data is correct.

For example, this would work, even though the 'codes' used to justify are not in the diagnosis codes that have been entered for the patient.  This can create wrong and confusing coding, so be careful.

/*billing::CPT4::80054::CMP:: :: ::50.00::these::words::are::not::icd9::codes::but::they::will::be::used::to::justify::80054::which::makes::no::sense*/ 


The camos function, CAMOS calling CAMOS:

This is really as confusing as it sounds.  Why would you want a CAMOS item to include function calls that inserted data into the CAMOS table, and therefore, into the patient's chart?  Suppose you had a CAMOS item which was a SOAP note template, say, for sinusitis.  Suppose that for most cases of sinusitis, you prescribe Augmentin.  You could put in the plan of the SOAP template the fact that you are prescribing Augmentin, but that defeats some of the purposes for using CAMOS in the first place.  If you were to choose your prescriptions via the category, subcategory, item menus as usual, you would be able to later do queries on the database and review how many prescriptions you write in a time period, how many antibiotics, how many Augmentin prescriptions, etc... (this depends on how you structure your categories, subcategories, and items, of course).  You could find all of the patients prescribed Augmentin if there were to be a recall or new warning.  So why not just say in your SOAP template, "see prescription orders", and then pick the prescriptions separately from the CAMOS menus?  Just one reason really, to save clicks.  If you do it all the time, why not save a few clicks here and there?  You just set it up once and it is there.  You could list a few alternative prescriptions in the same template and just delete the ones you don't want.  The possibilities are many.  Here is how you call the camos function to insert entries into the form_CAMOS table (the table in OpenEMR where CAMOS entries are stored for the patient's encounter):

/*camos::prescriptions::antibiotics::augmentin::

Augmentin XR 1000mg

#40/forty tablets.

Two tablets by mouth every 12 hours for 10 days.
::*/

At least this is how I do it in my setup.  Note the formatting of the prescription text with line endings.  This does not cause any problem with the function call.  I do it this way, because I print these as prescriptions from the report page within an encounter.  The arguments here correspond of course to category, subcategory, item, and content.  Again, no enforcement is made of these elements.  It will still work if your CAMOS menus do not contain these particular elements.  Likely, if you do not follow the structure you set up for your menus, you will limit your future ability to data mine.  Or, you may have good reason for straying from this structure.
