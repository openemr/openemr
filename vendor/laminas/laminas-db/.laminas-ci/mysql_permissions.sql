CREATE USER 'gha'@'%' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL PRIVILEGES ON *.* TO 'gha'@'%';
FLUSH PRIVILEGES;
