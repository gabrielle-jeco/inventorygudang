# 🤖 CI/CD dengan Jenkins - Overview

## 📖 Penjelasan Singkat

**CI/CD dengan Jenkins** = Robot otomatis yang:
- ✅ Test kode setiap kali ada perubahan
- ✅ Build Docker image otomatis
- ✅ Deploy ke GKE otomatis
- ✅ Update website tanpa downtime

**Baca penjelasan lengkap:** [JENKINS_CI_CD_PENJELASAN.md](./JENKINS_CI_CD_PENJELASAN.md)

---

## 🚀 Quick Start

Ikuti panduan cepat di: [CI_CD_QUICK_START.md](./CI_CD_QUICK_START.md)

---

## 📁 File yang Dibutuhkan

### 1. **Jenkinsfile** (Pipeline Definition)
File di root project yang berisi alur CI/CD:
- ✅ Checkout kode
- ✅ Run tests
- ✅ Build Docker image
- ✅ Push ke GCR
- ✅ Deploy ke GKE

### 2. **Jenkins Server**
Server yang menjalankan Jenkins:
- Bisa di Google Cloud Compute Engine
- Atau di GKE
- Atau server sendiri

### 3. **Credentials**
- Google Cloud Service Account
- Docker credentials (jika perlu)
- Git credentials (jika private repo)

### 4. **Webhook**
Setup webhook di Git repository untuk trigger pipeline otomatis

---

## 🔄 Alur Kerja CI/CD

```
Developer Push Kode
        ↓
Git Repository (GitHub/GitLab)
        ↓
Webhook → Jenkins
        ↓
Pipeline:
  1. Checkout
  2. Test
  3. Build Docker Image
  4. Push ke GCR
  5. Deploy ke GKE
        ↓
Website Ter-update! 🎉
```

---

## 📚 Dokumentasi

1. **Penjelasan Sederhana:** [JENKINS_CI_CD_PENJELASAN.md](./JENKINS_CI_CD_PENJELASAN.md)
   - Analogi sederhana tentang CI/CD
   - Penjelasan Jenkins
   - Alur kerja lengkap

2. **Setup Guide:** [JENKINS_SETUP_GUIDE.md](./JENKINS_SETUP_GUIDE.md)
   - Install Jenkins
   - Setup plugins
   - Konfigurasi credentials
   - Buat pipeline job

3. **Quick Start:** [CI_CD_QUICK_START.md](./CI_CD_QUICK_START.md)
   - Langkah cepat setup CI/CD
   - Troubleshooting

4. **Jenkinsfile:** [Jenkinsfile](./Jenkinsfile)
   - Pipeline definition
   - Stages dan steps

---

## 🛠️ Script Helper

### `scripts/setup-jenkins-gcp.sh`
Setup Jenkins di Google Cloud Compute Engine

### `scripts/create-jenkins-service-account.sh`
Buat service account untuk Jenkins dengan permission yang tepat

### `scripts/test-pipeline.sh`
Test pipeline steps secara lokal sebelum deploy ke Jenkins

---

## 🎯 Yang Harus Dipersiapkan

### ✅ **1. Jenkins Server**
- Install Jenkins (di GCP, GKE, atau server sendiri)
- Install plugins yang diperlukan
- Setup credentials

### ✅ **2. Jenkinsfile**
- File pipeline di root project
- Sudah ada di: `Jenkinsfile`

### ✅ **3. Service Account**
- Google Cloud Service Account dengan permission:
  - Container Registry (push/pull image)
  - Kubernetes Engine (deploy ke GKE)

### ✅ **4. Webhook**
- Setup webhook di Git repository
- URL: `http://YOUR_JENKINS_IP:8080/github-webhook/`

### ✅ **5. Update Jenkinsfile**
- Ganti `GCP_PROJECT_ID` dengan project ID kamu
- Ganti `GKE_CLUSTER_NAME` jika berbeda
- Ganti `GKE_ZONE` jika berbeda

---

## 🔧 Konfigurasi

### Environment Variables di Jenkinsfile

```groovy
environment {
    GCP_PROJECT_ID = 'your-project-id'      // ⚠️ GANTI INI!
    GKE_CLUSTER_NAME = 'inventorygudang-cluster'
    GKE_ZONE = 'asia-southeast2'
    GKE_NAMESPACE = 'inventorygudang'
}
```

### Credentials di Jenkins

1. **Google Service Account:**
   - ID: `gcp-service-account`
   - Type: Google Service Account from private key
   - File: `jenkins-key.json`

2. **Git (jika private):**
   - ID: `git-credentials`
   - Type: Username with password

---

## 🎓 Best Practices

### 1. **Environment Separation**
```groovy
// Deploy ke dev dulu
stage('Deploy to Dev') {
    when { branch 'develop' }
}

// Baru deploy ke production
stage('Deploy to Production') {
    when { branch 'main' }
}
```

### 2. **Notifications**
Setup notifikasi (Slack, Email) untuk:
- ✅ Deployment berhasil
- ❌ Deployment gagal
- ⚠️ Warning

### 3. **Secrets Management**
- Jangan hardcode secrets di Jenkinsfile
- Pakai Jenkins credentials
- Pakai Google Secret Manager untuk production

### 4. **Build Caching**
Cache Docker layers untuk build lebih cepat

### 5. **Rollback Strategy**
Setup automatic rollback jika deployment gagal

---

## 🆘 Troubleshooting

### Pipeline Tidak Jalan
- ✅ Cek webhook di Git repository
- ✅ Cek Jenkins logs
- ✅ Test manual trigger

### Error: "Cannot connect to GKE"
- ✅ Cek service account permissions
- ✅ Cek kubectl config
- ✅ Test koneksi manual

### Error: "Docker push failed"
- ✅ Authenticate Docker dengan GCR
- ✅ Cek service account permissions
- ✅ Test push manual

### Pipeline Lambat
- ✅ Tambah resource Jenkins server
- ✅ Pakai build cache
- ✅ Optimize Dockerfile

---

## 📊 Monitoring

### Jenkins Dashboard
- Lihat build history
- Cek build status
- Lihat console output

### GKE Monitoring
```bash
# Cek pods
kubectl get pods -n inventorygudang

# Cek deployments
kubectl get deployments -n inventorygudang

# Cek logs
kubectl logs -f deployment/inventorygudang-app -n inventorygudang
```

---

## 🎉 Hasil Akhir

Setelah setup CI/CD:
- ✅ **Push kode** → Otomatis test, build, deploy
- ✅ **Zero downtime** → Rolling update tanpa matikan website
- ✅ **Fast feedback** → Bug ketahuan lebih cepat
- ✅ **Konsisten** → Setiap deploy pakai proses yang sama

**Selamat! CI/CD sudah siap!** 🚀

