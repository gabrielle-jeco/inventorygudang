#!/bin/bash

# ============================================
# 🧪 SCRIPT TEST PIPELINE LOKAL
# ============================================
# Script untuk test pipeline steps secara lokal
# Sebelum deploy ke Jenkins

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Config
PROJECT_ID="${GCP_PROJECT_ID:-your-project-id}"
IMAGE_NAME="inventorygudang"
IMAGE_TAG="test-$(date +%s)"
GCR_IMAGE="gcr.io/${PROJECT_ID}/${IMAGE_NAME}:${IMAGE_TAG}"

echo -e "${GREEN}🧪 Testing pipeline steps locally...${NC}"

# Step 1: Checkout (skip, kita sudah di repo)
echo -e "${YELLOW}✅ Step 1: Checkout (skipped, already in repo)${NC}"

# Step 2: Test
echo -e "${YELLOW}🧪 Step 2: Running tests...${NC}"
if [ -f "vendor/bin/phpunit" ]; then
    ./vendor/bin/phpunit || echo -e "${RED}⚠️  Tests failed (continuing anyway)${NC}"
else
    echo -e "${YELLOW}⚠️  PHPUnit not found, skipping tests${NC}"
fi

# Step 3: Build
echo -e "${YELLOW}🐳 Step 3: Building Docker image...${NC}"
docker build -t ${GCR_IMAGE} .

# Step 4: Push (opsional, bisa skip untuk test)
read -p "Push image to GCR? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}📤 Step 4: Pushing image...${NC}"
    gcloud auth configure-docker gcr.io --quiet
    docker push ${GCR_IMAGE}
    echo -e "${GREEN}✅ Image pushed: ${GCR_IMAGE}${NC}"
else
    echo -e "${YELLOW}⏭️  Step 4: Push skipped${NC}"
fi

# Step 5: Deploy (opsional)
read -p "Deploy to GKE? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}🚀 Step 5: Deploying to GKE...${NC}"
    
    CLUSTER_NAME="inventorygudang-cluster"
    ZONE="asia-southeast2"
    NAMESPACE="inventorygudang"
    
    gcloud container clusters get-credentials ${CLUSTER_NAME} \
        --zone ${ZONE} \
        --project ${PROJECT_ID}
    
    kubectl set image deployment/inventorygudang-app \
        inventorygudang=${GCR_IMAGE} \
        -n ${NAMESPACE}
    
    kubectl rollout status deployment/inventorygudang-app \
        -n ${NAMESPACE} \
        --timeout=5m
    
    echo -e "${GREEN}✅ Deployment successful!${NC}"
else
    echo -e "${YELLOW}⏭️  Step 5: Deploy skipped${NC}"
fi

echo -e "${GREEN}🎉 Pipeline test completed!${NC}"

