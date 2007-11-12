#!/usr/bin/perl

######################################################################
# This module is compatible only with SQL-Ledger version 2.6.x.
# Copy it to your SQL-Ledger installation directory as ws_server.pl.
######################################################################

use Frontier::Responder;
use DBI;

######################################################################
# IMPORTANT - modify this to point to your SQL-Ledger installation!
######################################################################
use lib qw (/var/www/sql-ledger);

use SL::User;
use SL::Form;
use SL::CT;
use SL::HR;
use SL::IS;
use SL::IC;
use SL::AA;

require  "sql-ledger.conf";

my $add_customer = \&rpc_add_customer;
my $add_salesman = \&rpc_add_employee;
my $add_invoice = \&rpc_add_invoice;
my $customer_balance = \&rpc_customer_balance;

# In case we are running under Windows, do not strip carriage returns
# from POSTed data, otherwise Frontier::Responder may fail.
binmode(STDIN);

my $res = Frontier::Responder->new( methods => {
  'ezybiz.add_invoice' => $add_invoice,
  'ezybiz.add_salesman' => $add_salesman,
  'ezybiz.customer_balance' =>$customer_balance,
  'ezybiz.add_customer' => $add_customer
}, );

print $res->answer;

sub rpc_customer_balance {
	my ($post_hash) = @_;
	if ($$post_hash{id} > 0 ) {
		my $myconfig = new User "$memberfile", "$oemr_username";
		$myconfig->{dbpasswd} = unpack 'u', $myconfig->{dbpasswd};
		my $form = new Form;
		$form->{title} = "AR Outstanding";
		$form->{outstanding} = "1";
		$form->{customer_id} = $$post_hash{id};
		$form->{sort} = "transdate" ;
		$form->{l_due} = 1;
		$form->{nextsub} = "transaction";
		$form->{vc} = "customer" ;
		$form->{action} = 'Continue';

		AA::transactions("",\%$myconfig, \%$form);

		my ($paid,$amount) = 0;

		# Exclude invoices that are not yet due (i.e. waiting for insurance).
		# We no longer use the due date for this; instead ar.notes identifies
		# insurances used, and ar.shipvia indicates which of those are done.
		# If all insurances are done, it's due.
		#
		foreach my $resref (@{$$form{transactions}}) {
			my $inspending = 0;
			foreach my $tmp ('Ins1','Ins2','Ins3') {
				++$inspending if ($$resref{notes} =~ /$tmp/ && $$resref{shipvia} !~ /$tmp/);
			}
			if ($inspending == 0) {
				$paid   += $$resref{paid};
				$amount += $$resref{amount};
			}
		}

		my $retval = $amount - $paid;
		return($retval);
	}
}

sub rpc_add_customer
{
	use lib '/usr/lib/perl5/site_perl/5.8.3';

	my ($post_hash) = @_;

	#take struct of data and map to post data to create the customer, return the id
	my $myconfig = new User "$memberfile", "$oemr_username";
	$myconfig->{dbpasswd} = unpack 'u', $myconfig->{dbpasswd};
	my $form = new Form;
	$form->{name} = substr($$post_hash{'firstname'} . " " . $$post_hash{'lastname'}, 0, 64);
	$form->{discount} = "";
	$form->{terms} = "";
	$form->{taxincluded} = "1";
	$form->{creditlimit} = "0";
	$form->{id} = $$post_hash{'foreign_id'};
	$form->{login} = "";
	$form->{employee} = "";
	$form->{pricegroup} = "";
	$form->{business} = "";
	$form->{language} = "";
	$form->{address1} = substr($$post_hash{'address'}, 0, 32);
	$form->{address2} = substr($$post_hash{'address'}, 32, 32);
	$form->{city} = substr($$post_hash{'suburb'}, 0, 32);

	if($$post_hash{'state'}){
		$form->{state} = substr($$post_hash{'state'}, 0, 32);
	}else{
		$form->{state} = substr($$post_hash{'geo_zone_id'}, 0, 32);
	}
	$form->{zipcode} = substr($$post_hash{'postcode'}, 0, 10);
	$form->{country} = "";
	$form->{contact} = "";
	$form->{phone} = substr($$post_hash{'phone1'}, 0, 20);
	$form->{fax} = "";
	$form->{email} = $$post_hash{'email'};
	$form->{taxnumber} = substr($$post_hash{'ssn'}, 0, 32);
	$form->{curr} = "USD";
	$form->{customernumber} = $$post_hash{'customernumber'};
	@t = localtime(time);
	$dd = $t[3];
	$mm = $t[4] + 1;
	$yy = $t[5] + 1900;

	$form->{startdate} = "$mm-$dd-$yy";

	CT::save_customer('', \%$myconfig, \%$form);
	my $retVal = $form->{id};

	return($retVal);
}

sub rpc_add_employee
{
	my ($post_hash) = @_;
	my $myconfig = new User "$memberfile", "$oemr_username";
	$myconfig->{dbpasswd} = unpack 'u', $myconfig->{dbpasswd};
	my $form = new Form;
	$form->{id} = $$post_hash{'foreign_id'};
	$form->{name} = $$post_hash{'fname'} . " " . $$post_hash{'lname'};
	$form->{sales} = $$post_hash{'authorized'};
	@t = localtime(time);
	$dd = $t[3];
	$mm = $t[4] + 1;
	$yy = $t[5] + 1900;

	$form->{startdate} = "$mm-$dd-$yy";
	HR::save_employee("",\%$myconfig, \%$form);
	my $retVal = $form->{id};
	return($retVal);
}

sub rpc_add_invoice
{
	my ($post_hash) = @_;

	my $myconfig = new User "$memberfile", "$oemr_username";
	$myconfig->{dbpasswd} = unpack 'u', $myconfig->{dbpasswd};
	my $form = new Form;
	$form->{id};
	$form->{employee}    = "--" . $$post_hash{'salesman'};
	$form->{customer_id} = $$post_hash{'customerid'};
	$form->{invnumber}   = $$post_hash{'invoicenumber'};
	$form->{amount}      = $$post_hash{'total'};
	$form->{netamount}   = $$post_hash{'total'};
	$form->{notes}       = $$post_hash{'notes'};
	$form->{department}  = "";
	$form->{currency}    = "USD";
	$form->{defaultcurrency} = "USD";

	# This is the AR account number, needed by IS::post_invoice.
	$form->{AR} = $oemr_ar_acc;

	# This will use the posting date as the billing date
	@t = localtime(time);

	# $dd = $t[3];
	# $mm = $t[4] + 1;
	# $yy = $t[5] + 1900;

	$form->{transdate} = sprintf("%02u-%02u-%04u", $t[4] + 1, $t[3], $t[5] + 1900);

	# This overrides the above statement to use the date of service as the
	# invoice date, which should be preferable for most practices.  Comment
	# out the following line if you really want the billing date instead.
	#
	$form->{transdate} = $$post_hash{'dosdate'};

	# If there is insurance, set a future due date so we don't bother
	# the patient for a while.
	#
	if ($$post_hash{'payer_id'}) {
		@t = localtime(60 * 60 * 24 * $oemr_due_days + time);
		$form->{duedate} = sprintf("%02u-%02u-%04u", $t[4] + 1, $t[3], $t[5] + 1900);
	} else {
		$form->{duedate} = $form->{transdate};
	}

	# Get out if the invoice already exists.
	my $trans_id = 0;
	my $dbh = $form->dbconnect($myconfig);
	my $query = qq|SELECT id FROM ar WHERE invnumber = ?|;
	my $eth = $dbh->prepare($query) || die "Failed to prepare ar query";
	$eth->execute($$post_hash{'invoicenumber'}) || die "Failed to execute ar query";
	($trans_id) = $eth->fetchrow_array;
	$eth->finish;

	if ($trans_id) {
		print STDERR "Skipping invoice $trans_id = " . $$post_hash{'invoicenumber'} . "\n";
		$dbh->disconnect;
		return 0;
	}

	#loop through line items and add them to invoice
	my $i = 1;
	my $j = 1; #this is for copays should only be one but who knows -j
	my $count = 0;
	my $items = $$post_hash{'items'};

	foreach  my $line_item (@$items)
	{
		if ($$line_item{'itemtext'} =~ /COPAY/) {
			$form->{"datepaid_$j"} = $form->{transdate}; # "$mm-$dd-$yy";
			# For copays we use a dummy procedure code because it may be applicable
			# to multiple procedures during the visit.
			$form->{"memo_$j"} = 'Co-pay';
			# Put the payment method and check number in the source field if they are
			# present (i.e. from pos_checkout.php).
			if ($$line_item{'itemtext'} =~ /^COPAY:([A-Z].*)$/) {
				$form->{"source_$j"} = $1;
			} else {
				$form->{"source_$j"} = 'Co-pay';
			}
			$form->{"paid_$j"} =  abs($$line_item{'price'});
			$form->{"AR_paid_$j"} = "$oemr_cash_acc" . "--";
			$j++;
		}
		else{
			my $chart_id = 0;
			my $query = qq|SELECT id FROM chart WHERE accno = ?|;
			my $eth = $dbh->prepare($query) || die "Failed to prepare chart query";
			$eth->execute($$line_item{'glaccountid'}) || die "Failed to execute chart query";
			($chart_id) = $eth->fetchrow_array;
			$eth->finish;

			$form->{"qty_$i"} = $$line_item{'qty'};
			$form->{"discount_$i"} = 0;
			$form->{"sellprice_$i"} = $$line_item{'price'};

			$form->{taxincluded} = 1;
			$form->{"taxaccounts_$i"} = 0;
			$form->{"income_accno_$i"} = $$line_item{'glaccountid'};
			$form->{"income_accno_id_$i"} = $chart_id;

			$form->{"id_$i"} = add_goodsandservices(\%$myconfig, \%$form, $oemr_services_partnumber,
				'Medical Services', '');

			$form->{"description_$i"} = $$line_item{'itemtext'};
			$form->{"unit_$i"} = '';
			$form->{"serialnumber_$i"} = $$line_item{'maincode'};

			# Save the insurance company ID as the SL project ID.  This gives us a way
			# to associate each invoice item with its insurance payer.  The clinic will
			# probably want to write some reporting software taking advantage of this.
			#
			$form->{"projectnumber_$i"} = "--" . $$post_hash{'payer_id'}
				if ($$post_hash{'payer_id'});
			$i++;
		}
	}

	$dbh->disconnect;

	$form->{paidaccounts} = $j - 1;
	$form->{rowcount} = $i - 1;
	IS::post_invoice("", \%$myconfig, \%$form);
	my $retVal = $form->{id};
	return($retVal);
}

sub get_partid
{
	my ($myconfig, $form, $number) = @_;
	my $retval = 0;
	# connect to database
	my $dbh = $form->dbconnect($myconfig);

	my $query = qq|SELECT id FROM parts WHERE partnumber = ?|;
	my $eth = $dbh->prepare($query) || die "Failed to create select id from parts query";
	$eth->execute($number) || die "Failed to execute select id from parts query";
	($retval) = $eth->fetchrow_array;
	$eth->finish;
	$dbh->disconnect;
	return($retval);
}

sub add_goodsandservices
{
	my ($myconfig, $form, $code, $desc, $price) = @_;
	my $retval = 0;
	$retval = get_partid($myconfig, $form, $code);

	if($retval == 0)
	{
		# connect to database, turn off autocommit
		my $dbh = $form->dbconnect_noauto($myconfig);
		my $query = qq|insert into parts (partnumber,description,listprice,sellprice) values(?,?,?,?)|;
		my $eth = $dbh->prepare($query) || die "failed to create insert into parts query" . $dbh->errstr;
		$eth->execute($code,$desc,$price,$price) || die "failed to execute insert into parts query" . $dbh->errstr;
		$dbh->commit || die $dbh->errstr;
		$eth->finish || die "cannot finish " . $dbh->errstr;
		$dbh->disconnect;
		$retval = get_partid($myconfig, $form, $code);
	}

	return($retval);
}
