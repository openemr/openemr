#!/bin/bash

sudo apt-get update -y
sudo apt-get install -y redis-server
sudo sed -i 's/bind\ 127.0.0.1/\#bind\ 127.0.0.1/' /etc/redis/redis.conf
sudo service redis-server restart
