This is to store certificates in order to support encryption.

For mysql ssl support:
1. To support mysql ssl encryption, include the `mysql-ca` here (this is the CA certificate in pem form and is mandatory for mysql ssl).
2. Can also support client based certificate if also include mysql-cert and mysql-key (these are client certificate and client key in pem form and these are optional for mysql ssl)
3. For debugging purposes, if set `$GLOBALS['debug_ssl_mysql_connection']` to `true` at top of interface/globals.php, then will send messages to php log to show if mysql connections have a cipher set up.
4. To properly create the keys and certificates, see documentation here: https://dev.mysql.com/doc/refman/8.0/en/creating-ssl-files-using-openssl.html
5. When creating the keys and certificates, vital to enter in correct information for the `Common Name` of each entity:
- `Common Name` of CA certificate: This can be anything, but needs to be different than what is used for Server and Client
- `Common Name` of Server certificate: This has to be the host name(or ip address) that the client uses to log into the mysql server.
- `Common Name` of Client certificate: Set this to the host name of the client.

For couchdb ssl support:
1. To support couchdb ssl encryption, include the `couchdb-ca` here (this is the CA certificate in pem form).
2. Can also support client based certificate if also include couchdb-cert and couchdb-key (these are client certificate and client key in pem form and these are optional for couchdb ssl)
3. To properly create the keys and certificates, see documentation here: https://dev.mysql.com/doc/refman/8.0/en/creating-ssl-files-using-openssl.html
4. When creating the keys and certificates, vital to enter in correct information for the `Common Name` of each entity:
- `Common Name` of CA certificate: This can be anything, but needs to be different than what is used for Server and Client
- `Common Name` of Server certificate: This has to be the host name(or ip address) that the client uses to log into the mysql server.
- `Common Name` of Client certificate: Set this to the host name of the client.
5. Ensure support for couchdb SLL is set to on in OpenEMR at Administration->Globals->Documents->'CouchDB Connection SSL'
6. Note can also set couchdb to use selfsigned certificates (thus don't need to place anything in this directory), which can be
   allowed by setting Administration->Globals->Documents->'CouchDB SSL Allow Selfsigned Certificate' to on. Recommend not
   doing this in production environments unless you know what you are doing.

For ldap tls support:
1. To support ldap tls encryption, include the `ldap-ca` here (this is the CA certificate in pem form).
2. Can also support client based certificate if also include ldap-cert and ldap-key (these are client certificate and client key in pem form and these are optional for ldap ssl)
3. To properly create the keys and certificates, see documentation here: https://dev.mysql.com/doc/refman/8.0/en/creating-ssl-files-using-openssl.html
4. When creating the keys and certificates, vital to enter in correct information for the `Common Name` of each entity:
- `Common Name` of CA certificate: This can be anything, but needs to be different than what is used for Server and Client
- `Common Name` of Server certificate: This has to be the host name(or ip address) that the client uses to log into the mysql server.
- `Common Name` of Client certificate: Set this to the host name of the client.

For oauth key pair support:
1. This is done automatically by OpenEMR. When oauth is used, the a oaprivate.key and oaprublic.key will be created in this directory.
