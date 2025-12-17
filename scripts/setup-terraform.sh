#!/bin/bash

# ============================================
# 🏗️ SCRIPT SETUP TERRAFORM
# ============================================

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}🏗️  Setting up Terraform...${NC}"

# Check if terraform is installed
if ! command -v terraform &> /dev/null; then
    echo -e "${YELLOW}❌ Terraform tidak terinstall!${NC}"
    echo "Install dulu dari: https://www.terraform.io/downloads"
    exit 1
fi

# Check if gcloud is installed
if ! command -v gcloud &> /dev/null; then
    echo -e "${YELLOW}❌ Google Cloud SDK tidak terinstall!${NC}"
    echo "Install dulu dari: https://cloud.google.com/sdk/docs/install"
    exit 1
fi

cd terraform/

# Check if terraform.tfvars exists
if [ ! -f terraform.tfvars ]; then
    echo -e "${YELLOW}⚠️  terraform.tfvars tidak ditemukan!${NC}"
    echo "Copy dari terraform.tfvars.example dan edit sesuai kebutuhan"
    cp terraform.tfvars.example terraform.tfvars
    echo -e "${GREEN}✅ File terraform.tfvars sudah dibuat, silakan edit!${NC}"
    exit 1
fi

# Initialize Terraform
echo -e "${YELLOW}📥 Initializing Terraform...${NC}"
terraform init

# Plan
echo -e "${YELLOW}📋 Planning infrastructure...${NC}"
terraform plan

echo -e "${GREEN}✅ Setup selesai!${NC}"
echo ""
echo "Untuk membuat infrastruktur, jalankan:"
echo "  terraform apply"
echo ""
echo "Untuk melihat output setelah apply:"
echo "  terraform output"

