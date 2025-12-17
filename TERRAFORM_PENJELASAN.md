# 🎈 Penjelasan Sederhana: Infrastructure as Code (IaC) dengan Terraform

## 📖 Analogi Sederhana (Seperti Anak TK)

### 🏗️ Bayangkan Kamu Mau Bangun Rumah Mainan...

#### **Tanpa IaC (Cara Lama) - Manual:**
```
Kamu harus:
1. Ambil balok satu-satu dengan tangan
2. Susun balok satu per satu
3. Kalau salah, harus bongkar semua
4. Kalau mau rumah yang sama lagi, harus susun ulang dari awal
5. Susah diingat susunannya
```

#### **Dengan IaC (Cara Baru) - Otomatis:**
```
Kamu punya "Resep Masakan" (file Terraform):
1. Tulis sekali di kertas: "Ambil 10 balok merah, 5 balok biru"
2. Robot baca resep → langsung susun rumah
3. Kalau mau rumah yang sama, tinggal kasih resep ke robot lagi
4. Resep bisa disimpan dan dibagikan ke teman
5. Kalau salah, edit resep → robot perbaiki sendiri
```

---

## 🎯 Apa Itu Infrastructure as Code (IaC)?

**Infrastructure as Code** = Menulis "resep" untuk membuat infrastruktur (server, database, dll) dalam bentuk **kode/file**, bukan klik-klik manual.

### Contoh Sederhana:
```
❌ CARA LAMA (Manual):
1. Buka Google Cloud Console
2. Klik "Create Cluster"
3. Isi form satu-satu
4. Klik "Create"
5. Tunggu...
6. Kalau mau buat lagi, ulang dari awal 😫

✅ CARA BARU (IaC dengan Terraform):
1. Tulis file "cluster.tf"
2. Ketik: terraform apply
3. Selesai! 🎉
4. Kalau mau buat lagi, tinggal ketik terraform apply lagi
```

---

## 🛠️ Apa Itu Terraform?

**Terraform** = Alat yang membaca "resep" (file .tf) dan membuat infrastruktur sesuai resep.

### Analogi:
- **Terraform** = Robot yang baca resep
- **File .tf** = Resep masakan
- **terraform apply** = Perintah "Buat sekarang!"
- **terraform destroy** = Perintah "Hancurkan semua!"

---

## ☁️ Apa Itu GKE (Google Kubernetes Engine)?

**GKE** = Tempat khusus di Google Cloud untuk menjalankan aplikasi dalam "kontainer" (seperti kotak-kotak yang berisi aplikasi).

### Analogi:
```
🏠 Rumah = Server/Computer
📦 Kotak = Container (berisi aplikasi Laravel)
🚚 Truk = Kubernetes (mengatur kotak-kotak)
🏭 Pabrik = GKE (tempat semua truk dan kotak)
```

**Kenapa Pakai GKE?**
- ✅ Bisa naik/turun jumlah kotak otomatis (kalau banyak pengunjung, tambah kotak)
- ✅ Kalau satu kotak rusak, langsung diganti
- ✅ Mudah diatur dan dikelola

---

## 🔄 Alur Kerja Lengkap

### 1️⃣ **Persiapan (Sekali Saja)**
```
Kamu punya:
- Website Laravel (sudah ada Dockerfile)
- Akun Google Cloud
- Terraform terinstall
```

### 2️⃣ **Buat "Resep" Terraform**
```
File: terraform/main.tf
Isi: "Saya mau GKE cluster dengan 3 node"
```

### 3️⃣ **Jalankan Terraform**
```bash
terraform init    # Download "bahan-bahan"
terraform plan    # Lihat dulu apa yang akan dibuat
terraform apply   # Buat infrastruktur!
```

### 4️⃣ **Deploy Aplikasi ke GKE**
```bash
kubectl apply -f k8s/  # Deploy aplikasi Laravel
```

### 5️⃣ **Website Sudah Online! 🎉**

---

## 📁 Struktur File yang Akan Dibuat

```
inventorygudang/
├── terraform/              # 📝 "Resep" untuk infrastruktur
│   ├── main.tf            # Resep utama
│   ├── variables.tf       # Variabel (bisa diubah-ubah)
│   ├── outputs.tf         # Output (info setelah dibuat)
│   └── gke.tf             # Resep khusus untuk GKE
│
├── k8s/                   # 📦 "Resep" untuk aplikasi
│   ├── deployment.yaml    # Cara deploy aplikasi
│   ├── service.yaml       # Cara akses aplikasi
│   ├── configmap.yaml     # Konfigurasi aplikasi
│   └── secret.yaml        # Rahasia (password, dll)
│
└── TERRAFORM_PENJELASAN.md  # File ini
```

---

## 🎓 Keuntungan Menggunakan IaC

### ✅ **1. Bisa Diulang (Reproducible)**
```
Kalau mau buat environment baru (dev, staging, production),
tinggal copy file Terraform → terraform apply
Hasilnya sama persis!
```

### ✅ **2. Bisa Dikontrol dengan Git**
```
Semua perubahan tercatat di Git
Bisa lihat history, rollback, dll
```

### ✅ **3. Mudah Dikelola**
```
Satu perintah untuk buat semua
Satu perintah untuk hancurkan semua
Tidak perlu klik-klik manual
```

### ✅ **4. Bisa Dibagikan**
```
Tim lain bisa pakai file Terraform yang sama
Hasilnya konsisten untuk semua orang
```

---

## 🚀 Langkah-Langkah Praktis

### **Step 1: Install Terraform**
```bash
# Download dari: https://www.terraform.io/downloads
# Atau pakai package manager
```

### **Step 2: Setup Google Cloud**
```bash
# Install gcloud CLI
# Login: gcloud auth login
# Set project: gcloud config set project YOUR_PROJECT_ID
```

### **Step 3: Jalankan Terraform**
```bash
cd terraform/
terraform init
terraform plan
terraform apply
```

### **Step 4: Deploy Aplikasi**
```bash
# Build & push Docker image
docker build -t gcr.io/YOUR_PROJECT/inventorygudang:latest .
docker push gcr.io/YOUR_PROJECT/inventorygudang:latest

# Deploy ke GKE
kubectl apply -f ../k8s/
```

---

## 💡 Tips Penting

1. **Jangan Hardcode Secret** → Pakai Secret Manager atau file terpisah
2. **Gunakan Variables** → Supaya mudah diubah untuk environment berbeda
3. **Backup State File** → File `terraform.tfstate` penting, jangan hilang!
4. **Test Dulu di Dev** → Jangan langsung apply ke production

---

## 🎉 Kesimpulan

**IaC dengan Terraform** = Menulis "resep" untuk infrastruktur, lalu robot (Terraform) yang membuatnya.

**Keuntungan:**
- ✅ Mudah diulang
- ✅ Mudah dikelola
- ✅ Bisa dikontrol dengan Git
- ✅ Konsisten untuk semua environment

**Hasil Akhir:**
Website Laravel kamu jalan di GKE dengan mudah dikelola! 🚀

