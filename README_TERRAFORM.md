# 🎯 Quick Start: Deploy ke GKE dengan Terraform

## 📖 Penjelasan Sederhana

Bayangkan kamu punya **resep masakan** (file Terraform) yang bisa dipakai robot (Terraform) untuk membuat infrastruktur secara otomatis. Tidak perlu klik-klik manual lagi!

**Baca penjelasan lengkap di:** [TERRAFORM_PENJELASAN.md](./TERRAFORM_PENJELASAN.md)

---

## 🚀 Quick Start (5 Langkah)

### 1️⃣ **Persiapan**

```bash
# Install tools
# - Terraform: https://www.terraform.io/downloads
# - Google Cloud SDK: https://cloud.google.com/sdk/docs/install

# Login ke Google Cloud
gcloud auth login
gcloud config set project YOUR_PROJECT_ID
```

### 2️⃣ **Setup Terraform**

```bash
cd terraform/
cp terraform.tfvars.example terraform.tfvars
# Edit terraform.tfvars dengan project ID dan config kamu

terraform init
terraform plan
```

### 3️⃣ **Buat Infrastruktur**

```bash
terraform apply
# Ketik: yes
```

### 4️⃣ **Connect kubectl**

```bash
gcloud container clusters get-credentials inventorygudang-cluster \
    --region asia-southeast2 \
    --project YOUR_PROJECT_ID
```

### 5️⃣ **Deploy Aplikasi**

```bash
# Build & push Docker image
docker build -t gcr.io/YOUR_PROJECT_ID/inventorygudang:latest .
docker push gcr.io/YOUR_PROJECT_ID/inventorygudang:latest

# Update k8s/deployment.yaml dengan image yang benar
# Lalu deploy
cd k8s/
kubectl apply -f .
```

**Selesai!** 🎉

---

## 📁 Struktur File

```
inventorygudang/
├── terraform/              # 📝 "Resep" infrastruktur
│   ├── main.tf            # Resep utama
│   ├── variables.tf        # Variabel
│   ├── outputs.tf         # Output
│   ├── gke.tf             # Resep GKE
│   └── terraform.tfvars   # Config (jangan commit!)
│
├── k8s/                   # 📦 "Resep" aplikasi
│   ├── namespace.yaml
│   ├── deployment.yaml
│   ├── service.yaml
│   ├── configmap.yaml
│   └── secret.yaml        # Jangan commit!
│
└── scripts/               # 🛠️ Script helper
    ├── deploy.sh
    ├── setup-terraform.sh
    └── cleanup.sh
```

---

## 📚 Dokumentasi Lengkap

- **Penjelasan Sederhana:** [TERRAFORM_PENJELASAN.md](./TERRAFORM_PENJELASAN.md)
- **Panduan Deploy:** [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)

---

## 🎓 Konsep Penting

### Infrastructure as Code (IaC)
Menulis "resep" untuk infrastruktur dalam bentuk kode, bukan klik-klik manual.

### Terraform
Alat yang membaca "resep" dan membuat infrastruktur secara otomatis.

### GKE (Google Kubernetes Engine)
Tempat khusus di Google Cloud untuk menjalankan aplikasi dalam container.

### Kubernetes
Sistem untuk mengatur dan menjalankan aplikasi dalam container secara otomatis.

---

## 💡 Tips

1. ✅ **Test dulu di dev** sebelum production
2. ✅ **Backup state file** (terraform.tfstate)
3. ✅ **Jangan commit secret** (secret.yaml, terraform.tfvars)
4. ✅ **Gunakan variables** untuk mudah diubah
5. ✅ **Monitor cost** di Google Cloud Console

---

## 🆘 Butuh Bantuan?

1. Baca [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md) untuk troubleshooting
2. Cek logs: `kubectl logs <pod-name> -n inventorygudang`
3. Cek status: `kubectl get pods -n inventorygudang`

---

**Selamat deploy!** 🚀

