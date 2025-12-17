# ⚡ Quick Start - Deploy ke GKE dalam 5 Menit!

## 🎯 Untuk Pemula yang Ingin Cepat Jalan

### Prasyarat
- ✅ Akun Google Cloud (dengan billing enabled)
- ✅ Terraform terinstall
- ✅ Google Cloud SDK terinstall
- ✅ Docker terinstall

---

## 🚀 Langkah Cepat

### 1. Setup Project Google Cloud

```bash
# Login
gcloud auth login

# Set project
gcloud config set project YOUR_PROJECT_ID

# Enable APIs
gcloud services enable container.googleapis.com
gcloud services enable compute.googleapis.com
```

### 2. Setup Terraform

```bash
cd terraform/

# Copy dan edit config
cp terraform.tfvars.example terraform.tfvars
# Edit terraform.tfvars: isi project_id, region, dll

# Initialize
terraform init
```

### 3. Buat GKE Cluster

```bash
# Lihat dulu apa yang akan dibuat
terraform plan

# Buat cluster (akan memakan waktu ~5-10 menit)
terraform apply
# Ketik: yes
```

### 4. Connect kubectl

```bash
# Ganti YOUR_PROJECT_ID dan region sesuai config kamu
gcloud container clusters get-credentials inventorygudang-cluster \
    --region asia-southeast2 \
    --project YOUR_PROJECT_ID
```

### 5. Build & Push Docker Image

```bash
# Kembali ke root project
cd ..

# Build image
docker build -t gcr.io/YOUR_PROJECT_ID/inventorygudang:latest .

# Push ke GCR
docker push gcr.io/YOUR_PROJECT_ID/inventorygudang:latest
```

### 6. Setup Secret

```bash
cd k8s/

# Buat secret (ganti dengan value yang benar)
kubectl create secret generic inventorygudang-secret \
    --from-literal=APP_KEY="base64:xxxxx" \
    --from-literal=DB_CONNECTION="mysql" \
    --from-literal=DB_HOST="your-db-host" \
    --from-literal=DB_DATABASE="inventorygudang" \
    --from-literal=DB_USERNAME="your-user" \
    --from-literal=DB_PASSWORD="your-password" \
    --namespace=inventorygudang
```

### 7. Update & Deploy

```bash
# Edit deployment.yaml: ganti YOUR_PROJECT_ID dengan project ID kamu
# Lalu deploy
kubectl apply -f namespace.yaml
kubectl apply -f configmap.yaml
kubectl apply -f deployment.yaml
kubectl apply -f service.yaml
```

### 8. Cek Status

```bash
# Lihat pod
kubectl get pods -n inventorygudang

# Lihat service
kubectl get svc -n inventorygudang

# Port forward untuk test
kubectl port-forward svc/inventorygudang-service 8080:80 -n inventorygudang
```

**Buka browser: http://localhost:8080** 🎉

---

## 📚 Butuh Penjelasan Lebih Detail?

- **Penjelasan Sederhana:** [TERRAFORM_PENJELASAN.md](./TERRAFORM_PENJELASAN.md)
- **Panduan Lengkap:** [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)
- **README Terraform:** [README_TERRAFORM.md](./README_TERRAFORM.md)

---

## 🆘 Troubleshooting

### Error: "Image pull failed"
```bash
# Pastikan image sudah di-push
gcloud container images list --repository=gcr.io/YOUR_PROJECT_ID

# Cek permission
gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
    --member="serviceAccount:YOUR_NODE_SA@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/storage.objectViewer"
```

### Error: "Pod tidak jalan"
```bash
# Lihat logs
kubectl logs <pod-name> -n inventorygudang

# Lihat events
kubectl describe pod <pod-name> -n inventorygudang
```

### Error: "Database connection failed"
```bash
# Cek secret
kubectl get secret inventorygudang-secret -n inventorygudang -o yaml

# Test dari pod
kubectl exec -it <pod-name> -n inventorygudang -- php artisan tinker
```

---

## 🎉 Selesai!

Website kamu sekarang sudah jalan di GKE! 🚀

