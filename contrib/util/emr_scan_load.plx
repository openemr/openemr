#!/usr/bin/perl
use strict;

use Time::Local;
use DBI;

#######################################################################
# Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#######################################################################
#   This program is to be run frequently via the system crontab.  On
# each run it will move scanned-in documents from a shared directory
# into matching locations in the openemr/documents directory, and also
# update the database accordingly.
#  Each scanned-in file must be placed into a directory corresponding
# to its category, and its name must begin with the patient's pubpid
# followed by any non-alphanumeric character.  For example:
#   <shared-directory>/Categories/XRay/1234-knee-xray-20060131.jpg
#######################################################################
# NOTE: This is contributed as-is for the possible benefit of those who
# may wish to build on it.  As of this writing it is not known how well
# it works, if at all, with current OpenEMR.
#######################################################################

#######################################################################
#                Parameters that you must customize                   #
#######################################################################

# Parameters for MySQL database connections:
#
my $DBNAME = "openemr"; # database name
my $DBUSER = "openemr"; # database user name
my $DBPASS = "secret";  # database user's password

# Log file location:
#
my $INSLOG = "/mnt/drive2/emr_scan_load.log";

# Shared directory base where the scanners deposit files:
#
my $INPATH = "/mnt/drive2/scan_docs";

# Base directory for OpenEMR documents:
#
# my $OUTPATH = "/usr/local/apache2/htdocs/openemr/documents";
my $OUTPATH = "/mnt/drive2/documents";

# This should specify the user and group that the web server runs as:
#
my $CHOWN_COMMAND = "chown nobody:nogroup";

# This is the user from whom patient notes are addressed:
#
my $SCANNER_OPERATOR = 'ksears';

# This person gets patient notes if the doctor cannot be determined or is
# out of the office:
#
my $DEFAULT_PRACTITIONER = 'candroney';

# We need a SQL condition to identify encounter forms that will only be
# entered by practitioners.  Yeah there's probably a better way.
#
my $PRACTITIONER_FORM =
  "formdir = 'soap' OR formdir = 'reviewofs' OR formdir = 'ros'";

# For each day of the week (Sun-Sat), if the office is open:
#
my @open_days = (0, 1, 1, 1, 1, 1, 0);

# Office closing time:
#
my $closing_time = '17:00:00';

# Set this to 0 for production use:
#
my $DEBUG = 0;

#######################################################################
#                          Initialization                             #
#######################################################################

my $dbh = DBI->connect("dbi:mysql:dbname=$DBNAME", $DBUSER, $DBPASS)
  or die $DBI::errstr;

$| = 1; # Turn on autoflushing of stdout.

#######################################################################
#                            Functions                                #
#######################################################################

# Write a log message.
#
sub tolog($$) {
  my ($msg, $error) = @_;
  my @tm = localtime; $tm[5] += 1900; $tm[4] += 1;
  my $ts = sprintf "%04u-%02u-%02u %02u:%02u:%02u",
    $tm[5], $tm[4], $tm[3], $tm[2], $tm[1], $tm[0];
  if ($error) {
    $msg = '***ERROR: ' . $msg;
  }
  if ($DEBUG) {
    $msg = '*DEBUGGING* ' . $msg;
  }
  open LOG, ">> $INSLOG" or die "Cannot open $INSLOG: $!";
  print LOG "$ts $msg\n";
  close LOG;
}

# Determine if the designated doc is in the office at the specified time,
# or will be later that day.
#
sub is_doc_available($$) {
  my ($practitioner, $now) = @_;

  $now = time() if (! $now);

  my @tm = localtime $now; $tm[5] += 1900; $tm[4] += 1;
  my $current_date = sprintf "%04u-%02u-%02u", $tm[5], $tm[4], $tm[3];
  my $current_time = sprintf "%02u:%02u:%02u", $tm[2], $tm[1], $tm[0];
  my $daynow = int($now / (24 * 60 * 60));
  my $docid = $dbh->selectrow_array("SELECT id FROM users WHERE " .
      "username = '$practitioner'");

	my $query = "SELECT " .
    "pc_catid, pc_eventDate, pc_endDate, pc_recurrtype, pc_recurrspec, " .
    "pc_startTime, pc_endTime, pc_alldayevent " .
    "FROM openemr_postcalendar_events " .
    "WHERE pc_aid = '$docid' AND " .
    "( pc_catid = 2 OR pc_catid = 3 OR pc_duration >= 21600 ) AND " .
    "pc_eventDate <= '$current_date' AND pc_endDate >= '$current_date' " .
    "ORDER BY pc_startTime";
  my $esth = $dbh->prepare($query)
    or die $dbh->errstr;
  $esth->execute() or die $esth->errstr;

  # &tolog($query, 0); # debugging

  my $vacation  = 0;
  my $in_active = 0;
  my $in_until  = '';

  # Look at each event selected.
  #
  while (my @erow = $esth->fetchrow_array()) {
    my ($pc_catid, $pc_eventDate, $pc_endDate, $pc_recurrtype, $pc_recurrspec,
        $pc_startTime, $pc_endTime, $pc_alldayevent) = @erow;
    my $repeattype = '0';
    my $repeatfreq = '0';
    if ($pc_recurrspec =~ /"event_repeat_freq_type";s:1:"(\d)"/) {
      # 0 = day, 1 = week, 2 = month, 3 = year, 4 = workday
      $repeattype = $1;
    }
    if ($pc_recurrspec =~ /"event_repeat_freq";s:1:"(\d)"/) {
      # 1 = every, 2 = every other, etc.
      $repeatfreq = $1;
    }

    # If this is a repeating event, determine if it applies to today.
    #
    if ($pc_recurrtype) {
      $pc_eventDate =~ /^(\d+)\D(\d+)\D(\d+)/;
      my $time0 = timelocal(1, 0, 0, $3, $2 - 1, $1 - 1900);
      my $day0 = int($time0 / (24 * 60 * 60));
      my $elapsed_days = $daynow - $day0;
      my @tm0 = localtime $time0; $tm0[5] += 1900; $tm0[4] += 1;

      if ($repeattype == 0) {    # day
        if ($repeatfreq > 1) {
          my $quotient = sprintf('%.4f', $elapsed_days / $repeatfreq);
          next if ($quotient != int($quotient));
        }
      }
      elsif ($repeattype == 1) { # week
        my $repdays = $repeatfreq * 7;
        if ($repdays > 0) {
          my $quotient = sprintf('%.4f', $elapsed_days / $repdays);
          next if ($quotient != int($quotient));
        }
      }
      elsif ($repeattype == 2) { # month
        next if ($tm[3] != $tm0[3]); # if not same day of month
        if ($repeatfreq > 1) {
          my $elapsed_months = ($tm[5] - $tm0[5]) * 12 + $tm[4] - $tm0[4];
          my $quotient = sprintf('%.4f', $elapsed_months / $repeatfreq);
          next if ($quotient != int($quotient));
        }
      }
      elsif ($repeattype == 3) { # year
        next if ($tm[3] != $tm0[3] || $tm[4] != $tm0[4]);
        if ($repeatfreq > 1) {
          my $elapsed_years = $tm[5] - $tm0[5];
          my $quotient = sprintf('%.4f', $elapsed_years / $repeatfreq);
          next if ($quotient != int($quotient));
        }
      }
      elsif ($repeattype == 4) {    # work day (M-F)
        next if ($tm0[6] == 0 || $tm0[6] == 6); # if today is not a work day
        if ($repeatfreq > 1) {
          my $dowdiff = $tm[6] - $tm0[6];
          my $elapsed_workdays = ($elapsed_days - $dowdiff) * 5 / 7 + $dowdiff;
          my $quotient = sprintf('%.4f', $elapsed_workdays / $repeatfreq);
          next if ($quotient != int($quotient));
        }
      }
    }

    # Phew.  Now we know that this event is applicable to this day.

    if ($pc_catid == 2) {    # In Office
      $in_active = 1;
      $in_until = '23:59:59';
    }
    elsif ($pc_catid == 3) { # Out of Office
      if ($in_active) {
        $in_until = $pc_startTime;
        $in_active = 0;
      }
    }
    else {                   # Vacation or equivalent
      $vacation = 1;
    }
  }

  return 1 if ($in_until && ! $vacation && $in_until gt $current_time);
  return 0;
}

# Generate a patient note if appropriate for this top-level category.
#
sub generate_note($$$) {
  # my ($pid, $path, $topcategory, $docid) = @_;
  my ($pid, $path, $docid) = @_;

  # if ($NO_NOTE_CATEGORIES{$topcategory}) {
  #   return;
  # }

  # Get the login name of the user who entered the last clinical form for
  # this patient.  That's who we'll send the note to.
  #
  my $fsth = $dbh->prepare("SELECT user, groupname FROM forms WHERE " .
    "pid = '$pid' AND ( $PRACTITIONER_FORM ) ORDER BY date DESC LIMIT 1")
    or die $dbh->errstr;
  $fsth->execute() or die $fsth->errstr;
  my @frow = $fsth->fetchrow_array();
  #
  my $assigned_to = $DEFAULT_PRACTITIONER;
  my $groupname   = '';
  if (@frow) {
    $assigned_to = $frow[0];
    $groupname   = $frow[1];

    # Check the schedule to see if this doc is in today (or will be in the
    # next working day if it's after hours now); if not, assign the default.
    #
    my $now = time();
    my @tm = localtime $now;
    my $current_time = sprintf "%02u:%02u:%02u", $tm[2], $tm[1], $tm[0];
    while ($open_days[$tm[6]] == 0 || $current_time gt $closing_time) {
      $current_time = '00:00:00';
      $now = timelocal(0, 0, 0, $tm[3], $tm[4], $tm[5]) + (24 * 60 * 60);
      @tm = localtime $now;
    }
    if (! &is_doc_available($assigned_to, $now)) {
      &tolog("$assigned_to not available, using default practitioner", 0);
      $assigned_to = $DEFAULT_PRACTITIONER;
    }
  } else {
    &tolog("Patient $pid has no clinical forms, using default practitioner", 0);
  }

  # Build the text of the note including timestamp and addressing.
  # The document ID is also included, so that OpenEMR can easily
  # look up and display the document when the note is viewed.
  #
  my @tm = localtime; $tm[5] += 1900; $tm[4] += 1;
  my $body = sprintf "%04u-%02u-%02u %02u:%02u",
    $tm[5], $tm[4], $tm[3], $tm[2], $tm[1];
  $body .= " ($SCANNER_OPERATOR to $assigned_to) ";
  $body .= "New scanned document $docid: $path";

  # Write it to the database.
  #
  my $query = "INSERT INTO pnotes ( date, body, pid, user, groupname, " .
    "authorized, activity, title, assigned_to ) VALUES ( " .
    "NOW(), '$body', '$pid', '$SCANNER_OPERATOR', '$groupname', '1', '1', " .
    "'New Document', '$assigned_to')";
  if (! $DEBUG) {
    $dbh->do($query) or die $query;
  }

  &tolog("Patient note assigned to $assigned_to", 0);
}

# Process a document file.
#
sub process_file($$) {
  my ($path, $notify) = @_;

  # Extract the ending filename from the path.  Clean it up a bit for
  # use as an output filename.  Return if it's a leftover problem.
  #
  my $dname = '';
  my $fname = $path;
  if ($path =~ m'^(.*)/([^/]+)$') {
    $dname = $1;
    $fname = $2;
  }
  return if ($fname =~ /^ERR/);
  $fname =~ s/[^a-zA-Z0-9_.]/_/g;
  while ($fname =~ s/__/_/g) {}

  # Get out if the source file is open by any other process.  This
  # normally means that it's still being written via smbd.
  #
  if (my $pr = `lsof -t '$INPATH/$path'`) {
    &tolog("Temporarily skipping '$path' which is open by process $pr", 0);
    return;
  }

  # Get the chart number and look up the patient's pid.
  #
  my $pubpid = '';
  if ($fname =~ /^([A-Za-z0-9]+)/) {
    $pubpid = $1;
  }
  #
  my $psth = $dbh->prepare("SELECT pid FROM patient_data " .
    "WHERE pubpid = '$pubpid' LIMIT 1")
    or die $dbh->errstr;
  $psth->execute() or die $psth->errstr;
  my @prow = $psth->fetchrow_array();
  #
  if (! @prow) {
    &tolog("$path: there is no patient with chart id '$pubpid'", 1);
    rename "$INPATH/$path", "$INPATH/$dname/ERR-$fname" if (! $DEBUG);
    return;
  }
  if ($psth->fetchrow_array()) {
    &tolog("$path: there are multiple patients with chart id '$pubpid'", 1);
    rename "$INPATH/$path", "$INPATH/$dname/ERR-$fname" if (! $DEBUG);
    return;
  }
  my $pid = $prow[0];

  # Look up the document category and get its ID.
  #
  my @catpath = split /\//, $dname;
  my $catid = 0;
  my $catname = '';
  for (my $i = 0; $i < scalar @catpath; ++$i) {
    $catname = $catpath[$i];
    $catid = $dbh->selectrow_array("SELECT id FROM categories WHERE " .
      "name = '$catname' AND parent = $catid");
  }
  if (! $catid) {
    &tolog("Category '$dname' does not exist", 1);
    rename "$INPATH/$path", "$INPATH/$dname/ERR-$fname";
    return;
  }

  # Get the source file size; if zero, skip it.  It appears this
  # can be a case where the scanner software has created the
  # directory entry but has not yet written the data, so we do
  # not want to delete the file.
  #
  my $fsize = (stat("$INPATH/$path"))[7];
  if (! $fsize) {
    # &tolog("Deleting and skipping empty file '$path'", 1);
    # unlink "$INPATH/$path";
    &tolog("Skipping empty file '$path'", 1);
    return;
  }

  # Make sure the target directory exists.
  #
  system "mkdir -p '$OUTPATH/$pid'; $CHOWN_COMMAND '$OUTPATH/$pid'" if (! $DEBUG);

  # If the target filename exists, modify it until it doesn't.
  #
  my $count = 0;
  while (-e "$OUTPATH/$pid/$fname") {
    my $oldfname = $fname;
    my $fsuff = '';
    if ($fname =~ /^(.*)(\..+)$/) {
      $fname = $1;
      $fsuff = $2;
    }
    if ($count++) {
      $fname =~ s/_\d+$//;
    }
    $fname .= '_' . $count . $fsuff;
    &tolog("File '$pid/$oldfname' already exists; trying '$pid/$fname' ...", 0);
  }
  my $target = "$OUTPATH/$pid/$fname";

  # Move the file to its destination and set its owner and group.
  #
  my $movecmd = "mv '$INPATH/$path' '$target'";
  if (! $DEBUG) {
    my $rc = system $movecmd;
    if ($rc != 0) {
      &tolog("Command '$movecmd' failed with return code $rc", 1);
      return;
    }
    system "$CHOWN_COMMAND '$target'";
  }

  # Compute assorted values for the documents table.
  #
  $dbh->do("update sequences set id = id + 1") if (! $DEBUG);
  my $newid = $dbh->selectrow_array("SELECT id FROM sequences");
  my @tm = localtime; $tm[5] += 1900; $tm[4] += 1;
  my $ts1 = sprintf "%04u-%02u-%02u %02u:%02u:%02u", $tm[5], $tm[4], $tm[3], $tm[2], $tm[1], $tm[0];
  my $ts2 = $ts1;
  $ts2 =~ s/\D//g;
  my $mimetype = $DEBUG ? '' : `file -i $target`;
  $mimetype =~ s/;.*$//;    # remove trailing "; charset=..." if present
  $mimetype =~ s/^.*:\s*//; # remove everything preceding the mime type
  $mimetype =~ s/\s*$//;    # remove any trailing line feed or other whitespace
  if (! $mimetype) {
    &tolog("Unable to determine MIME type using 'file -i $target'; proceeding with empty type", 1);
  }

  if (! $DEBUG) {
    # Update the database.
    #
    my $query = "INSERT INTO documents ( " .
      "id, type, size, date, url, mimetype, revision, foreign_id" .
      " ) VALUES ( " .
      "'$newid', 'file_url', '$fsize', '$ts1', 'file://$target', '$mimetype', '$ts2', $pid " .
      ")";
    $dbh->do($query) or die $query;
    #
    my $query = "INSERT INTO categories_to_documents ( " .
      "category_id, document_id" .
      " ) VALUES ( " .
      "'$catid', '$newid' " .
      ")";
    $dbh->do($query) or die $query;
  }

  &tolog("Loaded '$path' as $mimetype", 0);

  # Generate the patient note if appropriate for this category.
  #
  # &generate_note($pid, "$dname/$fname", $catpath[1], $newid);
  &generate_note($pid, "$dname/$fname", $newid) if $notify;
}

# Scan the source directory recursively to invoke processing of each
# document file.
#
sub scan_dir($) {
  my $path = shift;
  my $notify = -f "$INPATH/$path/.notify";
  opendir my $dh, "$INPATH/$path";
  while (my $dirent = readdir $dh) {
    next if ($dirent =~ /^\./);
    my $thispath = $path ? "$path/$dirent" : $dirent;
    if (-d "$INPATH/$thispath") {
      &scan_dir($thispath);
    } else {
      &process_file($thispath, $notify);
    }
  }
  closedir $dh;
}

#######################################################################
#                            Processing                               #
#######################################################################

&scan_dir('Categories'); # This makes everything happen.

#######################################################################
#                             Shutdown                                #
#######################################################################

$dbh->disconnect;
