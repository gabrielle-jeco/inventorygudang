#!/bin/bash

# ============================================
# 🚀 SCRIPT DEPLOY OTOMATIS
# ============================================
# Script ini membantu deploy aplikasi ke GKE dengan mudah

set -e  # Stop jika ada error

# Colors untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Config (ubah sesuai kebutuhan)
PROJECT_ID="your-project-id"
REGION="asia-southeast2"
CLUSTER_NAME="inventorygudang-cluster"
IMAGE_NAME="inventorygudang"
IMAGE_TAG="latest"
GCR_IMAGE="gcr.io/${PROJECT_ID}/${IMAGE_NAME}:${IMAGE_TAG}"

echo -e "${GREEN}🚀 Starting deployment...${NC}"

# Step 1: Build Docker Image
echo -e "${YELLOW}📦 Step 1: Building Docker image...${NC}"
docker build -t ${GCR_IMAGE} .

# Step 2: Push to GCR
echo -e "${YELLOW}📤 Step 2: Pushing image to GCR...${NC}"
docker push ${GCR_IMAGE}

# Step 3: Update deployment dengan image baru
echo -e "${YELLOW}🔄 Step 3: Updating deployment...${NC}"
cd k8s/
sed -i.bak "s|gcr.io/YOUR_PROJECT_ID/inventorygudang:latest|${GCR_IMAGE}|g" deployment.yaml
rm -f deployment.yaml.bak

# Step 4: Apply Kubernetes manifests
echo -e "${YELLOW}⚙️  Step 4: Applying Kubernetes manifests...${NC}"
kubectl apply -f namespace.yaml
kubectl apply -f configmap.yaml

# Cek apakah secret sudah ada
if ! kubectl get secret inventorygudang-secret -n inventorygudang &> /dev/null; then
    echo -e "${RED}❌ Secret belum dibuat! Buat dulu dengan:${NC}"
    echo "kubectl create secret generic inventorygudang-secret --from-file=secret.yaml -n inventorygudang"
    exit 1
fi

kubectl apply -f deployment.yaml
kubectl apply -f service.yaml

# Step 5: Wait for rollout
echo -e "${YELLOW}⏳ Step 5: Waiting for deployment to be ready...${NC}"
kubectl rollout status deployment/inventorygudang-app -n inventorygudang --timeout=5m

# Step 6: Show status
echo -e "${GREEN}✅ Deployment completed!${NC}"
echo ""
echo "Status:"
kubectl get pods -n inventorygudang
echo ""
echo "Services:"
kubectl get svc -n inventorygudang
echo ""
echo -e "${GREEN}🎉 Done!${NC}"

