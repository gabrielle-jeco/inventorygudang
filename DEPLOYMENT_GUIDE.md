# 🚀 Panduan Deploy ke GKE dengan Terraform

## 📋 Daftar Isi
1. [Persiapan](#persiapan)
2. [Setup Terraform](#setup-terraform)
3. [Deploy Infrastruktur](#deploy-infrastruktur)
4. [Build & Push Docker Image](#build--push-docker-image)
5. [Deploy Aplikasi ke GKE](#deploy-aplikasi-ke-gke)
6. [Setup Database](#setup-database)
7. [Verifikasi](#verifikasi)
8. [Troubleshooting](#troubleshooting)

---

## 🔧 Persiapan

### 1. Install Tools yang Diperlukan

```bash
# Install Terraform
# Download dari: https://www.terraform.io/downloads
# Atau pakai package manager:
# Windows: choco install terraform
# Mac: brew install terraform
# Linux: apt-get install terraform

# Install Google Cloud SDK
# Download dari: https://cloud.google.com/sdk/docs/install

# Install kubectl
gcloud components install kubectl
```

### 2. Setup Google Cloud

```bash
# Login ke Google Cloud
gcloud auth login

# Set project ID
gcloud config set project YOUR_PROJECT_ID

# Enable billing (wajib untuk GKE)
# Lakukan di Google Cloud Console: https://console.cloud.google.com/billing

# Enable APIs yang diperlukan
gcloud services enable container.googleapis.com
gcloud services enable compute.googleapis.com
```

### 3. Setup Authentication untuk Terraform

```bash
# Buat service account untuk Terraform
gcloud iam service-accounts create terraform \
    --display-name="Terraform Service Account"

# Beri permission
gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
    --member="serviceAccount:terraform@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/container.admin"

gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
    --member="serviceAccount:terraform@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/compute.admin"

# Buat key untuk service account
gcloud iam service-accounts keys create terraform-key.json \
    --iam-account=terraform@YOUR_PROJECT_ID.iam.gserviceaccount.com

# Set environment variable
export GOOGLE_APPLICATION_CREDENTIALS="$(pwd)/terraform-key.json"
```

---

## 🏗️ Setup Terraform

### 1. Konfigurasi Variables

```bash
cd terraform/

# Copy contoh file variables
cp terraform.tfvars.example terraform.tfvars

# Edit terraform.tfvars dengan editor favorit kamu
# Isi dengan:
# - project_id: ID project Google Cloud kamu
# - region: Region yang diinginkan (contoh: asia-southeast2 untuk Jakarta)
# - zone: Zone dalam region tersebut
# - cluster_name: Nama cluster (contoh: inventorygudang-cluster)
# - node_count: Jumlah node (contoh: 2)
# - machine_type: Tipe mesin (contoh: e2-medium)
```

### 2. Initialize Terraform

```bash
# Initialize Terraform (download provider, dll)
terraform init

# Lihat apa yang akan dibuat (tanpa membuat)
terraform plan

# Jika sudah OK, apply (buat infrastruktur)
terraform apply

# Terraform akan menanyakan konfirmasi, ketik: yes
```

### 3. Setup kubectl

```bash
# Setelah cluster dibuat, connect kubectl ke cluster
gcloud container clusters get-credentials inventorygudang-cluster \
    --region asia-southeast2 \
    --project YOUR_PROJECT_ID

# Verifikasi koneksi
kubectl get nodes
```

---

## 🐳 Build & Push Docker Image

### 1. Setup Docker untuk Google Container Registry

```bash
# Configure Docker untuk GCR
gcloud auth configure-docker

# Atau untuk Artifact Registry (lebih baru)
gcloud auth configure-docker asia-southeast2-docker.pkg.dev
```

### 2. Build Docker Image

```bash
# Dari root project
docker build -t gcr.io/YOUR_PROJECT_ID/inventorygudang:latest .

# Atau dengan tag versi
docker build -t gcr.io/YOUR_PROJECT_ID/inventorygudang:v1.0.0 .
```

### 3. Push ke Google Container Registry

```bash
# Push image
docker push gcr.io/YOUR_PROJECT_ID/inventorygudang:latest

# Atau untuk Artifact Registry
docker tag gcr.io/YOUR_PROJECT_ID/inventorygudang:latest \
    asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/inventorygudang/inventorygudang:latest
docker push asia-southeast2-docker.pkg.dev/YOUR_PROJECT_ID/inventorygudang/inventorygudang:latest
```

---

## 🚀 Deploy Aplikasi ke GKE

### 1. Setup Secret

```bash
cd k8s/

# Copy contoh secret
cp secret.yaml.example secret.yaml

# Edit secret.yaml
# Encode semua value dengan base64:
# echo -n "your-value" | base64

# Atau pakai kubectl untuk create secret langsung:
kubectl create secret generic inventorygudang-secret \
    --from-literal=APP_KEY="base64:xxxxx" \
    --from-literal=DB_PASSWORD="your-password" \
    --namespace=inventorygudang
```

### 2. Update Deployment dengan Image yang Benar

```bash
# Edit k8s/deployment.yaml
# Ganti YOUR_PROJECT_ID dengan project ID kamu
# Ganti image: gcr.io/YOUR_PROJECT_ID/inventorygudang:latest
```

### 3. Deploy Semua Resources

```bash
# Deploy semua file Kubernetes
kubectl apply -f namespace.yaml
kubectl apply -f configmap.yaml
kubectl apply -f secret.yaml
kubectl apply -f deployment.yaml
kubectl apply -f service.yaml

# Atau deploy semua sekaligus
kubectl apply -f .

# Cek status
kubectl get pods -n inventorygudang
kubectl get services -n inventorygudang
```

### 4. Setup Ingress (Opsional - untuk domain)

```bash
# Reserve static IP dulu
gcloud compute addresses create inventorygudang-ip --global

# Update ingress.yaml dengan IP dan domain kamu
# Lalu deploy
kubectl apply -f ingress.yaml
```

---

## 🗄️ Setup Database

### Opsi 1: Cloud SQL (Recommended)

```bash
# Buat Cloud SQL instance
gcloud sql instances create inventorygudang-db \
    --database-version=MYSQL_8_0 \
    --tier=db-f1-micro \
    --region=asia-southeast2

# Buat database
gcloud sql databases create inventorygudang \
    --instance=inventorygudang-db

# Buat user
gcloud sql users create inventoryuser \
    --instance=inventorygudang-db \
    --password=YOUR_PASSWORD

# Update secret.yaml dengan info database Cloud SQL
```

### Opsi 2: MySQL di GKE (Tidak Recommended untuk Production)

```bash
# Deploy MySQL sebagai StatefulSet
# (Butuh file MySQL deployment terpisah)
```

---

## ✅ Verifikasi

### 1. Cek Pod Status

```bash
# Lihat semua pod
kubectl get pods -n inventorygudang

# Lihat detail pod
kubectl describe pod <pod-name> -n inventorygudang

# Lihat logs
kubectl logs <pod-name> -n inventorygudang
```

### 2. Cek Service

```bash
# Lihat service
kubectl get svc -n inventorygudang

# Test akses dari dalam cluster
kubectl run -it --rm debug --image=busybox --restart=Never -- \
    wget -qO- http://inventorygudang-service.inventorygudang.svc.cluster.local
```

### 3. Akses Aplikasi

```bash
# Port forward untuk test lokal
kubectl port-forward svc/inventorygudang-service 8080:80 -n inventorygudang

# Buka browser: http://localhost:8080
```

---

## 🔧 Troubleshooting

### Pod Tidak Jalan

```bash
# Cek status pod
kubectl get pods -n inventorygudang

# Lihat events
kubectl get events -n inventorygudang --sort-by='.lastTimestamp'

# Lihat logs
kubectl logs <pod-name> -n inventorygudang

# Masuk ke pod untuk debug
kubectl exec -it <pod-name> -n inventorygudang -- /bin/sh
```

### Image Pull Error

```bash
# Cek apakah image sudah di-push
gcloud container images list --repository=gcr.io/YOUR_PROJECT_ID

# Cek permission service account
# Pastikan node pool punya akses ke GCR
```

### Database Connection Error

```bash
# Cek secret
kubectl get secret inventorygudang-secret -n inventorygudang -o yaml

# Test koneksi dari pod
kubectl exec -it <pod-name> -n inventorygudang -- \
    php artisan tinker
# Lalu test: DB::connection()->getPdo();
```

---

## 🧹 Cleanup

### Hapus Aplikasi

```bash
# Hapus semua resources Kubernetes
kubectl delete -f k8s/

# Atau hapus namespace (akan hapus semua di dalamnya)
kubectl delete namespace inventorygudang
```

### Hapus Infrastruktur

```bash
cd terraform/

# Hapus semua infrastruktur
terraform destroy

# Konfirmasi dengan: yes
```

---

## 📚 Referensi

- [Terraform GKE Documentation](https://registry.terraform.io/providers/hashicorp/google/latest/docs/resources/container_cluster)
- [Kubernetes Documentation](https://kubernetes.io/docs/)
- [Google Cloud GKE Documentation](https://cloud.google.com/kubernetes-engine/docs)

---

## 🎉 Selesai!

Website kamu sekarang sudah jalan di GKE! 🚀

