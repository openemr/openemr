#!/bin/bash

sudo -u ubuntu bash -c '\''echo -e \"${{ env.DOCKER_COMPOSE_BASE64 }}\" | base64 -d > /home/ubuntu/docker-compose.yml && 
echo \"# TFA Residen EMR Secrets
MYSQL_HOST=mysql
MYSQL_USER=${{ secrets.MYSQL_USER }}
MYSQL_PASS=${{ secrets.MYSQL_PASS }}
MYSQL_ROOT_PASS=${{ secrets.MYSQL_ROOT_PASS }}
MYSQL_ROOT_PASSWORD=${{ secrets.MYSQL_ROOT_PASS }}
OE_USER=${{ secrets.OE_USER }}
OE_PASS=${{ secrets.OE_PASS }}
DOMAIN=${{ env.DOMAIN_NAME }}
EMAIL=mircea.ene@assist.ro\" > /home/ubuntu/.env && 
mkdir -p /home/ubuntu/openemr_logs && 
mkdir -p /home/ubuntu/openemr_sites && 
mkdir -p /home/ubuntu/openemr_documents && 
aws ecr get-login-password --region ${{ vars.AWS_REGION }} | docker login --username AWS --password-stdin ${{ env.ECR_URL }} && 
cd /home/ubuntu && docker compose pull && docker compose up -d && docker system prune -af'\''"]}'
