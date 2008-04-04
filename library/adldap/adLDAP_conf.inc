<?php
/* Change the settings to suit your Active Directory installation
 * 
 * account_suffix : this is the full domain name of your Active Directory
 * base_dn: Users is the standard windows CN, replace the DC stuff with your domain
 * domain_controllers: the IP address of your domain controller(s)
 * ad_username: a username with simple 'bind' access to Active Directory no special permissions needed
 * ad_password: the password for the user
 * real_primarygroup: leave alone or read adldap.sourceforge.net docs
 * use_ssl: leave alone or read adldap.sourceforge.net docs
 * recursive_groups: leave alone or read adldap.sourceforge.net docs
 */
$adldap_options = array (
"account_suffix" => "@my.windowsdomain.com",
"base_dn" => "CN=Users,DC=my,DC=windowsdomain,DC=com",
"domain_controllers" => array ("10.1.1.1"),
"ad_username" => "myldapreader",
"ad_password" => "thepasswd",
"real_primarygroup" => true,
"use_ssl" => false,
"recursive_groups" => true
);

?>
