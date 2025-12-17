# ============================================
# 🚀 SCRIPT DEPLOY OTOMATIS (Windows PowerShell)
# ============================================

$ErrorActionPreference = "Stop"

# Config (ubah sesuai kebutuhan)
$PROJECT_ID = "your-project-id"
$REGION = "asia-southeast2"
$CLUSTER_NAME = "inventorygudang-cluster"
$IMAGE_NAME = "inventorygudang"
$IMAGE_TAG = "latest"
$GCR_IMAGE = "gcr.io/$PROJECT_ID/$IMAGE_NAME`:$IMAGE_TAG"

Write-Host "🚀 Starting deployment..." -ForegroundColor Green

# Step 1: Build Docker Image
Write-Host "📦 Step 1: Building Docker image..." -ForegroundColor Yellow
docker build -t $GCR_IMAGE .

# Step 2: Push to GCR
Write-Host "📤 Step 2: Pushing image to GCR..." -ForegroundColor Yellow
docker push $GCR_IMAGE

# Step 3: Update deployment dengan image baru
Write-Host "🔄 Step 3: Updating deployment..." -ForegroundColor Yellow
Set-Location k8s
(Get-Content deployment.yaml) -replace 'gcr.io/YOUR_PROJECT_ID/inventorygudang:latest', $GCR_IMAGE | Set-Content deployment.yaml

# Step 4: Apply Kubernetes manifests
Write-Host "⚙️  Step 4: Applying Kubernetes manifests..." -ForegroundColor Yellow
kubectl apply -f namespace.yaml
kubectl apply -f configmap.yaml

# Cek apakah secret sudah ada
$secretExists = kubectl get secret inventorygudang-secret -n inventorygudang 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Secret belum dibuat! Buat dulu dengan:" -ForegroundColor Red
    Write-Host "kubectl create secret generic inventorygudang-secret --from-file=secret.yaml -n inventorygudang"
    exit 1
}

kubectl apply -f deployment.yaml
kubectl apply -f service.yaml

# Step 5: Wait for rollout
Write-Host "⏳ Step 5: Waiting for deployment to be ready..." -ForegroundColor Yellow
kubectl rollout status deployment/inventorygudang-app -n inventorygudang --timeout=5m

# Step 6: Show status
Write-Host "✅ Deployment completed!" -ForegroundColor Green
Write-Host ""
Write-Host "Status:"
kubectl get pods -n inventorygudang
Write-Host ""
Write-Host "Services:"
kubectl get svc -n inventorygudang
Write-Host ""
Write-Host "🎉 Done!" -ForegroundColor Green

