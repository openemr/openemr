#!/usr/bin/perl

#
#  SOAP Lite needs to be installed for this to work correctly.
#
use SOAP::Lite;

#
# EDIT THE BELOW URL TO MATCH YOUR SERVER.
#
sub soap_init {
        my $soap = new SOAP::Lite
        uri => 'http://localhost/phpgacl/soap/server.php',
        proxy => 'http://localhost/phpgacl/soap/server.php'
        or die "Failed SOAP connection: $! \n";
}

$soap = soap_init();

sub acl_check {
        my ($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value, $root_aro_group_id, $root_axo_group_id) = @_;

	return $soap->call('acl_check',$aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value,$axo_value, $root_aro_group_id, $root_axo_group_id)->result
}

if ( acl_check('system','login','users','john_doe') ) {
        print "John Doe has been granted access to login!<br>\n";
} else {
        print "John Doe has been denied access to login!<br>\n";
}

