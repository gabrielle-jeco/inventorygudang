#!/bin/bash

# ============================================
# 🔐 SCRIPT BUAT SERVICE ACCOUNT UNTUK JENKINS
# ============================================

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Config
PROJECT_ID="${GCP_PROJECT_ID:-your-project-id}"
SA_NAME="jenkins"
SA_EMAIL="${SA_NAME}@${PROJECT_ID}.iam.gserviceaccount.com"

echo -e "${GREEN}🔐 Creating service account for Jenkins...${NC}"

# Set project
gcloud config set project ${PROJECT_ID}

# Create service account
echo -e "${YELLOW}👤 Creating service account...${NC}"
gcloud iam service-accounts create ${SA_NAME} \
    --display-name="Jenkins Service Account" \
    --description="Service account for Jenkins CI/CD" || true

# Grant permissions
echo -e "${YELLOW}🔑 Granting permissions...${NC}"

# Container Registry & Artifact Registry
gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member="serviceAccount:${SA_EMAIL}" \
    --role="roles/storage.admin" \
    --condition=None

# Kubernetes Engine
gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member="serviceAccount:${SA_EMAIL}" \
    --role="roles/container.developer" \
    --condition=None

gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member="serviceAccount:${SA_EMAIL}" \
    --role="roles/container.clusterAdmin" \
    --condition=None

# Compute Engine (untuk akses VM jika perlu)
gcloud projects add-iam-policy-binding ${PROJECT_ID} \
    --member="serviceAccount:${SA_EMAIL}" \
    --role="roles/compute.viewer" \
    --condition=None

# Create key
echo -e "${YELLOW}🔑 Creating key file...${NC}"
gcloud iam service-accounts keys create jenkins-key.json \
    --iam-account=${SA_EMAIL}

echo -e "${GREEN}✅ Service account created!${NC}"
echo ""
echo "Key file: jenkins-key.json"
echo ""
echo "Next steps:"
echo "1. Upload jenkins-key.json ke Jenkins:"
echo "   Manage Jenkins → Credentials → Add"
echo "   Kind: Google Service Account from private key"
echo "   Upload: jenkins-key.json"
echo ""
echo "2. JANGAN commit jenkins-key.json ke Git!"
echo "   File ini sudah ada di .gitignore"

