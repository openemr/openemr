<!--
   README_edihistory.html

   Copyright 2016 Kevin McCormick <kevin@records>

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
   MA 02110-1301, USA.


-->

<!DOCTYPE html>
<html lang="en">

<head>
	<title>EDI History for x12 files</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Bluefish 2.2.8" />
	<style>
		body { background-color: #E6EDFA; max-width: 1024px; }
		div#edih_readme { float: left; 	padding: 2em; max-width: 800px; line-height: 1.2em;	background-color: #DCECE8; }
		code { background-color: #FAF4E6; line-height: 1em; border-width: 1em; }
		pre { background-color: #FAF4E6; line-height: 1em; border-width: 1em; }
	</style>
</head>

<body>
<div id="edih_readme">
<h3>README Claim History Project</h3>
<p>
This file contains notes and hints for developing and using the files and scripts in this &quot;EDI History&quot; project.
</p>
<p>
This applies to version 2 of my edi_history project. <strong>Use at your own risk.</strong> The intended use is for health care facilities only. The scripts in this project are intended to create a system for managing x12 edi files.  These are the mysterious x12 format data files known as 837 Claim, 835 Payment, 270 Benefit Inquiry, 271 Benefit Response, 276 Claim Status Inquiry, 277/277CA Claim Status, and 278 Authorization; and do not forget 999 Acknowledgement. (The 824 type is not dealt with, but if anyone gets these, it should not be too hard to interpret.) These are the &quot;Health Care&quot; types used for billing and insurance information. The prior version had scripts for certain proprietary formats, but this version is x12 format only.
</p>
<p>
In order to best use these scripts, you must diligently upload all your edi files using the &quot;New Files&quot; tab. Of course, in order to upload you must first download.  In order to download, you must complete some registrations, obtain accounts with usernames and passwords, and configure OpenEMR with the information for your x12 partner(s).
</p>
<p>
Since the information in the EDI files is <strong>HIPAA protected</strong>, do not use these scripts on a public server! (or any computer where access is not restricted)  The files and tables are stored in the OpenEMR directory under &quot;openemr/sites/[sitedir]/edi/history/&quot;  Use the OpenEMR access control scheme to determine which users can use the scripts, probably &quot;accounting&quot;. The files and contained information will be as secure as your OpenEMR installation.
</p>
<p><em>Upgrade</em><br />
The &quot;upgrade&quot; process consists of renaming existing .csv files to <em>old_[files|claims]_[type].csv</em> and moving the existing &quot;era&quot; files to the newly created &quot;f835&quot; directory. This is handled by the <em>csv_setup()</em> function in <em>edih_csv_inc.php</em>. Otherwise, the only actons are the creation of the new directories. If you want the <em>/history/</em> directory to be clear of old cruft, then you can manually delete or move the &quot;old&quot; csv files under the <em>/csv</em> directory and also remove the <em>era</em>, <em>text</em>, <em>ibr</em>, <em>ebr</em>, and <em>dpr</em> directories, if present. Do not delete any of the following directories: archive, csv, f270, f271, f276, f277, f278, f835, f997, log, or tmp.
</p>
<p>
If for some reason the scripted upgrade does not work, you may wish to manually upgrade. I suggest you make a copy of the existing /history directory tree. Then delete the &quot;/history&quot; tree (not the /edi, just the /history -- this leaves all the batch files under /edi/[name].batch.txt). Then select the EDI History module in the OpenEMR left nav, which will create the new /history tree. Then copy all the x12 files to their new directories (note: era &gt; f835). Make sure the files ownership matches the OpenEMR ownershp. Then use the &quot;Process&quot; button on the New Files tab.  De-select the html and errors output checkboxes.  New csv tables will be created and all the files will be processed.  You may have to upload one file to activate the Process button.
<br />
The [sitedir] is often called &quot;default&quot; and [/path/to/mydir] is your choice. Directory path details may vary.<br />
<pre><code>
cd /var/www/htdocs/openemr/sites/[sitedir]/edi<br />
cp -a history [/path/to/mydir]<br />
rm -rf history<br />
</code></pre>
Click EDI History in left nav (setup script will run. Check the log under the Notes tab)<br />
Now copy your existing x12 edi files into their respective directories. Note [ftype] will be like f277, f835, f997, etc.<br />
<pre><code>
cp [/path/to/mydir]/history/[ftype]/* /var/www/htdocs/openemr/sites/[sitedir]/documents/edi/history/[ftype]/<br />
</code></pre>
Repeat for each file type, but do not copy the csv files. <br />
&nbsp;&nbsp;&ndash; the dpr, text, ebr, ibr types are no longer used, so don't bother.<br />
&nbsp;&nbsp;&ndash; the era directory is now the f835 directory<br />
Find your OpenEMR &quot;owner group&quot; with:<br />
<pre><code>
ls -l /var/www/htdocs/openemr<br />
&nbsp;&nbsp;<samp>-rw-r--r--  1 apache apache   969 Mar 24 02:25 COPYRIGHT_AND_LICENSE</samp><br />
</code></pre>
Verify file ownership and permissions. (example: owner apache, group apache)<br />
The directories permissions should be &quot;0755&quot; (&quot;drwxr-xr-x&quot;) and x12 files &quot;0400&quot; (&quot;-r--------&quot;)<br />
<pre><code>
cd /var/www/htdocs/openemr/sites/[sitedir]/documents/edi/history<br />
chown apache:apache [ftype]/*<br />
chmod 0400 [ftype]/*<br />
</code></pre>
</p>
<h3>Usage</h3>
<p><em>New Files</em><br />
&nbsp;-- Browse and Submit<br />
Select multiple files from your user directory where you have downloaded the files from your x12 partner.  If there is a limit on uploaded files, you should be notified so you can repeat the process and get all files uploaded.  Each file is checked and classified.  If a file is rejected, that is because it was not interpreted as an x12 format file or not a type the scripts can deal with (there are many types).  The scripts should be able to parse any valid Health Care category x12 file, so please submit a comment on the OpenEMR forum if you have a valid x12 file that is rejected. For .zip files, there must not be sub directories, because the parsing script does not handle sub directories.<br />
&nbsp;-- Process<br />
After new files are uploaded, click the &quot;Process&quot; button. You can (de)select html and/or errors-only, but they are on by default, so you will see a listing of summary information about the files, with particular details where the script detected a possible problem, like a rejected claim.
</p>
<p><em>CSV Tables</em><br />
On this tab, there is a select list to choose a table to view.  Also, select a period or date range to limit the response.  The tables are rows of information intended to allow you to select files or transactions of interest.  The jQuery DataTables plugin has sorting and searching capability.  The &quot;H&quot; link will open a formatted report of the data and the &quot;T&quot; link will open a rendition of the actual data segments from the edi file.  The &quot;Trace&quot; column generally has a reference to the transaction in another file, such as claim status tracing back to the submitted claim.  In the 835 &quot;Payment&quot; tables, the trace is to the payment check/eft number.  Unfortunately, in my opinion the standard OpemEMR claim generation file does not create a correct value in the BHT segment, so tracing to the exact claim submission is not reliable. (See note below) <br />
<em>Per Encounter</em><br />
Enter the claim id (xxxx-xxxxx) (from the 837 CLM01 value). The csv tables will be searched and a dialog will open with links to the corresponding 837, 277, and 835 transactions, and 997/999 if the batch file was rejected.
</p>
<p><em>Notes</em><br />
Select a log file to view, or the notes file. The notes file is just a textbox you can use to make little reminders.  The log files are daily records of actions done.  The &quot;Archive&quot; button will cause log files older than 7 days to be put in a zip file.
</p>
<p><em>Archive</em><br />
The edi files are quite fascinating when they are new, but once the billing cycle is complete they may be of little interest.  The idea of the Archive tab is to enable you to select an ageing period and put all the older files and their csv data rows into a zip archive stored under /history/archive. The batch files under the /sites/[sitedir]/edi directory are not archived, since I do not want to accidentally break anything.  You can change the available periods by editing the edih_view.php file in the Archive div. The &quot;Report&quot; button will produce a report on your edi files and the &quot;Archive&quot; button will do the deed. There is also a &quot;Restore&quot; button which will unpack the selected archive file and replace the files and csv data rows. You will have to manually delete archive files that you no longer want.  After you have performed an Archive, you will probably want to reload the EDI History page which will refresh the the select lists and clear the output under each tab.
</p>
<p><em>General Thoughts</em><br />
The 999/997 x12 type is the acknowledgment which lists submission errors.  It must include the <em>TA1</em> segment in order for the scripts to be able to match these response files with the corresponding batch file you submitted. I think it would be possible to edit the <em>billing_process.php</em> script to give a unique ID for the GS06 value, in which case the TA1 segment would probably not be required, but a few code edits would be needied in the edi_history scripts as well.
</p>
<p>
In the old version, I used file name patterns to classify files, but version 2 uses information in the GS segment for this purpose.  If that fails for some reason, the file name pattern is tried. The patterns are found in <em>edih_csv_inc.php</em> in the function <em>csv_parameters()</em>.
</p>
<p>
For 835 ERA files, I assume the internal grouping of the files is by check/eft from a particular payer. The 835 files table has a row for each check/eft number, so there are likely more rows than files.
</p>
<p>
It also happens that a clearinghouse will append several ISA--IEA envelopes into a single file.  I assume they are all of the same type.  Each ISA control number receives a line in the files table, so this is another reason there could be more rows than files in a files table.
</p>

<h3>OpenEMR Integration</h3>

<p>
Access control is entirely under the OpenEMR scheme and will likely require the access permissions of &quot;accounting.&quot;
</p>
<p>
Since the information in the EDI files is likely HIPAA protected, do not use these scripts on a public server!
</p>
<p>
If this project proves to be useful and reliable, then I would suggest modifying the ERA batch posting script so it is activated from a tab button.  A scan of database check numbers compared to a scan of files_f835.csv &quot;Trace&quot; numbers should identify all unprocessed payments and the files can be queued accordingly.<br />
</p>
<p>
Secondly, the x12 batch files could be stored under designated directories to avoid a hodgepodge situation in the /edi directory.
</p>
</p>
Thirdly, There is a rough draft script &quot;test_edih_sftp_files.php&quot; in the &quot;/openemr/library/edihistory/$quot; directory which outlines a possibility for automated file transfers using sftp.
<p>
<h3>File Locations:</h3>
<p>
The installed directory tree would be:
</p>
<ul>
	<li>/openemr/interface/billing </li>
	<li>/openemr/library/edihistory</li>
	<li>/openemr/library/css</li>
	<li>/openemr/library/js</li>
</ul>

<p>
Installed files:
</p>
<ul>
    <li>/openemr/Documentation
        <ul>
            <li>Readme_edihistory.html</li>
        </ul>
    </li>
	<li>/openemr/interface/billing </li>
		<ul>
			<li>edih_view.php</li>
			<li>edih_main.php</li>
		</ul>
	<li>/openemr/library/edihistory </li>
		<ul>
			<li>edih_archive.php</li>
			<li>edih_csv_data.php</li>
			<li>edih_csv_inc.php</li>
			<li>edih_csv_parse.php</li>
			<li>edih_io.php</li>
			<li>edih_segments.php</li>
			<li>edih_uploads.php</li>
			<li>edih_x12file_class.php</li>
			<li>edih_271_html.php</li>
			<li>edih_277_html.php</li>
			<li>edih_278_html.php</li>
			<li>edih_835_html.php</li>
			<li>edih_997_error.php</li>
		</ul>
	<li>/openemr/library/edihistory/codes</li>
		<ul>
			<li>edih_271_code_class.php</li>
			<li>edih_835_code_class.php</li>
			<li>edih_997_codes.php</li>
	   </ul>
	<li>/openemr/library/css</li>
	<ul>
      <li>edi_history_v2.css</li>
	</ul>
	<ul>
	  <li>/openemr/library/js/DataTables-1.10.11</li>
	  <ul>
		<li>datatables.min.js</li>
      </ul>
    <li>/library/js/jquery-ui-1.10.4.custom/js/</li>
      <ul>
       <li>jquery-1.10.2.min.js</li>
       <li>jquery-ui-1.10.4.custom.min.js</li>
	  </ul>
	 <li>/library/js/jquery-ui-1.10.4.custom/custom-theme/css/</li>
	   <ul>
		<li>jquery-ui-1.10.4.custom.min.css</li>
	   </ul>
	 <li>/library/js/DataTables-1.10.11/DataTables-1.10.11/css/</li>
	   <ul>
		<li>jquery.dataTables.min.css</li>
		<li>dataTables.jqueryui.min.css</li>
	   </ul>
	</ul>
</ul>
<p>
The <em>csv_setup()</em> function creates a file storage directory tree: <br />
&nbsp;&nbsp; openemr/sites/[sitedir]/documents/edi/history <br />
with subdirectories: archive csv f270 f271 f276 f277 f278 f835 f997 log tmp
</p>
<p>
and these csv files under: /openemr/sites/[sitedir]/documents/edi/history/csv<br />
&nbsp;&nbsp; claims_[ftype].csv  files_[ftype].csv tables are created for each file type on first appearance
</p>
<p>
The path to these files is set by <em>csv_edih_basedir()</em> based upon the OpenEMR directory paths.
</p>
<p>
Note:  I suggest the following edit to the OpenEMR <em>/interface/billing/billing_process.php</em> script:
<br />
&nbsp;  In the file openemr/interface/billing/billing_process.php<br />
&nbsp;  in &quot;function append_claim(&amp;$segs)&quot;<br />
 near line 82  (after the &quot;if (elems[0] == 'ST') { }&quot; block)
</p>
<pre><code>
    // add this mod
    if ($elems[0] == 'BHT') {
       // give each claim a unique BHT number,: isa-control-num and st-num are concatenated
       //
       $bat_content .= str_replace("*0123*", sprintf("*%s%04d*", $bat_icn, $bat_stcount), $seg) . "~";
       continue;
    }

</code></pre>
<p>
The EDI methods and files are cryptic and mysterious.  The formats are definitely not what I would call
user-friendly.  The contents and meaning of the various files, loops, and segments may be better understood
with serious research.  There are so called &quot;Companion Documents&quot; published by some insurance companies
and possibly by your clearinghouse.  Search for &quot;X12 835 837 277 999 Companion Document&quot; and see if you find
anything useful.
</p>
</div>
</body>

</html>
