This is to store certificates in order to support encryption.

For mysql ssl support (Do not perform below steps until after OpenEMR has been installed; this has not been tested to work with OpenEMR installation.):
1. To support mysql ssl encryption, include the `mysql-ca` here (this is the CA certificate in pem form and is mandatory for mysql ssl).
2. Can also support client based certificate if also include mysql-cert and mysql-key (these are client certificate and client key in pem form and these are optional for mysql ssl)
3. For debugging purposes, if set `$GLOBALS['debug_ssl_mysql_connection']` to `true` at top of interface/globals.php, then will send messages to php log to show if mysql connections have a cipher set up.
4. To properly create the keys and certificates, see documentation here: https://dev.mysql.com/doc/refman/5.7/en/creating-ssl-files-using-openssl.html
5. When creating the keys and certificates, vital to enter in correct information for the `Common Name` of each entity:
- `Common Name` of CA certificate: This can be anything, but needs to be different than what is used for Server and Client
- `Common Name` of Server certificate: This has to be the host name(or ip address) that the client uses to log into the mysql server.
- `Common Name` of Client certificate: Set this to the host name of the client.