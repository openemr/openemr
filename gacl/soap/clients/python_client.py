#!/usr/bin/python
#
#  This uses the Python ZSI SOAP Infrastructure from: http://pywebsvcs.sourceforge.net/
#

import sys;
from ZSI.client import Binding;

#soapclient = Binding(url='/phpgacl/soap/server.php', host='hubcap.netnation.com', tracefile=sys.stdout);
soapclient = Binding(url='/phpgacl/soap/server.php', host='hubcap.netnation.com');

def acl_check(aco_section_value, aco_value, aro_section_value, aro_value, axo_section_value=0, axo_value=0, root_aro_group_id=0, root_axo_group_id=0):
	return soapclient.acl_check(aco_section_value, aco_value, aro_section_value, aro_value, axo_section_value, axo_value, root_aro_group_id, root_axo_group_id);


if ( acl_check('system','login','users','john_doe') ):
	print "John Doe has been granted access to login!<br>\n";
else:
	print "John Doe has been denied access to login!<br>\n";





