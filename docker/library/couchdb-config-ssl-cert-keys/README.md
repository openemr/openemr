The local.ini is the local.ini config file that is used in the couchdb docker that is used in the development docker environments.

The keys and certificates within these subdirectories are only meant for testing purposes in the couchdb dev docker. Do NOT use these in production settings.

These were created via instructions from:
https://dev.mysql.com/doc/refman/8.0/en/creating-ssl-files-using-openssl.html

CN assignments in certs/keys:
- `easy` directory holds certs/keys for the easy docker development environment
  - CA: openemr-ca
  - Server: couchdb (server-cert.pem, server-key.pem)
  - Client: openemr (client-cert.pem, client-key.pem)
- `insane` directory holds certs/keys for the insane docker development environment
  - CA: openemr-ca
  - Server: couchdb (server-cert.pem, server-key.pem)
  - Client: openemr-7-3-312 (client-cert.pem, client-key.pem)

files in `easy`/`insane` directories:
- CA
  - ca.pem -> (couchdb-ca when copied into sites/default/documents/certificates/)
  - ca-key.pem
- Server
  - server-cert.pem
  - server-key.pem
- Client
  - client-cert.pem -> (couchdb-cert when copied into sites/default/documents/certificates/)
  - client-key.pem -> (couchdb-key when copied into sites/default/documents/certificates/)
