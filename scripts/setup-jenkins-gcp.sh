#!/bin/bash

# ============================================
# 🚀 SCRIPT SETUP JENKINS DI GOOGLE CLOUD
# ============================================
# Script ini membantu setup Jenkins di Google Cloud Compute Engine

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Config (ubah sesuai kebutuhan)
PROJECT_ID="${GCP_PROJECT_ID:-your-project-id}"
ZONE="${GCP_ZONE:-asia-southeast2-a}"
INSTANCE_NAME="jenkins-server"
MACHINE_TYPE="e2-medium"
DISK_SIZE="30GB"

echo -e "${GREEN}🚀 Setting up Jenkins on Google Cloud...${NC}"

# Check if gcloud is installed
if ! command -v gcloud &> /dev/null; then
    echo -e "${RED}❌ gcloud CLI tidak terinstall!${NC}"
    echo "Install dari: https://cloud.google.com/sdk/docs/install"
    exit 1
fi

# Set project
echo -e "${YELLOW}📋 Setting project...${NC}"
gcloud config set project ${PROJECT_ID}

# Create VM instance
echo -e "${YELLOW}🖥️  Creating VM instance...${NC}"
gcloud compute instances create ${INSTANCE_NAME} \
    --zone=${ZONE} \
    --machine-type=${MACHINE_TYPE} \
    --image-family=ubuntu-2004-lts \
    --image-project=ubuntu-os-cloud \
    --boot-disk-size=${DISK_SIZE} \
    --tags=jenkins-server

# Create firewall rule
echo -e "${YELLOW}🔥 Creating firewall rule...${NC}"
gcloud compute firewall-rules create allow-jenkins \
    --allow tcp:8080 \
    --source-ranges 0.0.0.0/0 \
    --target-tags jenkins-server \
    --description "Allow Jenkins access"

# Get instance IP
INSTANCE_IP=$(gcloud compute instances describe ${INSTANCE_NAME} \
    --zone=${ZONE} \
    --format='get(networkInterfaces[0].accessConfigs[0].natIP)')

echo -e "${GREEN}✅ VM instance created!${NC}"
echo ""
echo "Instance IP: ${INSTANCE_IP}"
echo ""
echo "Next steps:"
echo "1. SSH ke instance:"
echo "   gcloud compute ssh ${INSTANCE_NAME} --zone=${ZONE}"
echo ""
echo "2. Di dalam instance, jalankan:"
echo "   sudo apt update"
echo "   sudo apt install openjdk-17-jdk -y"
echo "   curl -fsSL https://pkg.jenkins.io/debian-stable/jenkins.io-2023.key | sudo tee /usr/share/keyrings/jenkins-keyring.asc > /dev/null"
echo "   echo deb [signed-by=/usr/share/keyrings/jenkins-keyring.asc] https://pkg.jenkins.io/debian-stable binary/ | sudo tee /etc/apt/sources.list.d/jenkins.list > /dev/null"
echo "   sudo apt-get update"
echo "   sudo apt-get install jenkins -y"
echo "   sudo systemctl start jenkins"
echo "   sudo systemctl enable jenkins"
echo ""
echo "3. Akses Jenkins di browser:"
echo "   http://${INSTANCE_IP}:8080"
echo ""
echo "4. Get initial password:"
echo "   sudo cat /var/lib/jenkins/secrets/initialAdminPassword"

