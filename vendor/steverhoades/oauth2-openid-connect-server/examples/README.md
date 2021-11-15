# OpenID Connect Example Implementations
The following examples piggyback off the PHP Leagues OAuth2 Server examples. Please follow the instructions below carefully.

## Installation

0. Run `composer install --prefer-source` in this directory to install dependencies
0. Create a private key `openssl genrsa -out private.key 2048`
0. Create a public key `openssl rsa -in private.key -pubout > public.key`
0. Change permissions of the .key files or a PHP Notice will be thrown `chmod 660 *.key`
0. `cd` into the public directory
0. Start a PHP server `php -S localhost:4444`

## Testing the client credentials grant example

Send the following cURL request:

```
curl -X "POST" "http://localhost:4444/client_credentials.php/access_token" \
	-H "Content-Type: application/x-www-form-urlencoded" \
	-H "Accept: 1.0" \
	--data-urlencode "grant_type=client_credentials" \
	--data-urlencode "client_id=myawesomeapp" \
	--data-urlencode "client_secret=abc123" \
	--data-urlencode "scope=openid email"
```

## Testing the password grant example

Send the following cURL request:

```
curl -X "POST" "http://localhost:4444/password.php/access_token" \
	-H "Content-Type: application/x-www-form-urlencoded" \
	-H "Accept: 1.0" \
	--data-urlencode "grant_type=password" \
	--data-urlencode "client_id=myawesomeapp" \
	--data-urlencode "client_secret=abc123" \
	--data-urlencode "username=alex" \
	--data-urlencode "password=whisky" \
	--data-urlencode "scope=openid email"
```
