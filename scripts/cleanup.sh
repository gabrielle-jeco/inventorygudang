#!/bin/bash

# ============================================
# 🧹 SCRIPT CLEANUP
# ============================================
# Hapus semua resources yang dibuat

set -e

# Colors
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${RED}⚠️  WARNING: Script ini akan menghapus semua resources!${NC}"
read -p "Apakah kamu yakin? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Dibatalkan."
    exit 0
fi

# Hapus Kubernetes resources
echo -e "${YELLOW}🗑️  Deleting Kubernetes resources...${NC}"
kubectl delete -f k8s/ --ignore-not-found=true || true
kubectl delete namespace inventorygudang --ignore-not-found=true || true

# Hapus Terraform infrastructure
echo -e "${YELLOW}🗑️  Deleting Terraform infrastructure...${NC}"
cd terraform/
terraform destroy -auto-approve || true

echo -e "${GREEN}✅ Cleanup selesai!${NC}"

