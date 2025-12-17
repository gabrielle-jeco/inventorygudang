# ⚡ Quick Start: Setup CI/CD dengan Jenkins

## 🎯 Langkah Cepat (15 Menit)

### 1️⃣ **Buat Service Account untuk Jenkins**

```bash
# Jalankan script
./scripts/create-jenkins-service-account.sh

# Atau manual:
gcloud iam service-accounts create jenkins \
    --display-name="Jenkins Service Account"

gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
    --member="serviceAccount:jenkins@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/container.developer"

gcloud iam service-accounts keys create jenkins-key.json \
    --iam-account=jenkins@YOUR_PROJECT_ID.iam.gserviceaccount.com
```

### 2️⃣ **Setup Jenkins Server**

```bash
# Opsi A: Di Google Cloud (Recommended)
./scripts/setup-jenkins-gcp.sh

# Opsi B: Manual
# Ikuti panduan di JENKINS_SETUP_GUIDE.md
```

### 3️⃣ **Install Jenkins**

```bash
# SSH ke Jenkins server
gcloud compute ssh jenkins-server --zone=asia-southeast2-a

# Install Jenkins
sudo apt update
sudo apt install openjdk-17-jdk -y

curl -fsSL https://pkg.jenkins.io/debian-stable/jenkins.io-2023.key | sudo tee \
  /usr/share/keyrings/jenkins-keyring.asc > /dev/null

echo deb [signed-by=/usr/share/keyrings/jenkins-keyring.asc] \
  https://pkg.jenkins.io/debian-stable binary/ | sudo tee \
  /etc/apt/sources.list.d/jenkins.list > /dev/null

sudo apt-get update
sudo apt-get install jenkins -y
sudo systemctl start jenkins
```

### 4️⃣ **Setup Jenkins (First Time)**

1. **Akses Jenkins:** `http://YOUR_JENKINS_IP:8080`
2. **Get password:**
   ```bash
   sudo cat /var/lib/jenkins/secrets/initialAdminPassword
   ```
3. **Install suggested plugins**
4. **Create admin user**

### 5️⃣ **Install Required Plugins**

**Manage Jenkins → Manage Plugins → Available**

Install:
- Git Plugin
- Docker Pipeline Plugin
- Kubernetes Plugin
- Google Cloud Platform Plugin
- Blue Ocean (opsional)

### 6️⃣ **Setup Credentials**

**Manage Jenkins → Credentials → System → Global credentials → Add**

1. **Google Service Account:**
   - Kind: Google Service Account from private key
   - Upload: `jenkins-key.json`
   - ID: `gcp-service-account`

2. **Git (jika private repo):**
   - Kind: Username with password
   - ID: `git-credentials`

### 7️⃣ **Update Jenkinsfile**

Edit `Jenkinsfile` di root project:
```groovy
environment {
    GCP_PROJECT_ID = 'your-project-id'  // Ganti ini!
    GKE_CLUSTER_NAME = 'inventorygudang-cluster'
    GKE_ZONE = 'asia-southeast2'
}
```

### 8️⃣ **Buat Pipeline Job**

1. **New Item → Pipeline**
2. **Name:** `inventorygudang-pipeline`
3. **Pipeline → Definition:** Pipeline script from SCM
4. **SCM:** Git
5. **Repository URL:** URL repository kamu
6. **Script Path:** `Jenkinsfile`
7. **Save**

### 9️⃣ **Setup Webhook**

**GitHub:**
1. Repository → Settings → Webhooks → Add webhook
2. **URL:** `http://YOUR_JENKINS_IP:8080/github-webhook/`
3. **Content type:** `application/json`
4. **Events:** Push events
5. **Add webhook**

### 🔟 **Test Pipeline**

```bash
# Push perubahan kecil
echo "# Test CI/CD" >> README.md
git add README.md
git commit -m "Test CI/CD pipeline"
git push

# Cek Jenkins dashboard, pipeline harusnya jalan otomatis!
```

---

## ✅ Verifikasi

### Cek Pipeline Berjalan

1. **Jenkins Dashboard → inventorygudang-pipeline**
2. **Lihat build history**
3. **Klik build terakhir → Console Output**

### Cek Deployment

```bash
# Cek image di GCR
gcloud container images list --repository=gcr.io/YOUR_PROJECT_ID

# Cek pods di GKE
kubectl get pods -n inventorygudang

# Cek deployment
kubectl get deployments -n inventorygudang
```

---

## 🆘 Troubleshooting

### Pipeline Tidak Jalan Setelah Push

```bash
# Cek webhook di GitHub
# Settings → Webhooks → Recent Deliveries

# Test manual trigger
# Jenkins → inventorygudang-pipeline → Build Now
```

### Error: "Cannot connect to GKE"

```bash
# Pastikan service account punya permission
gcloud projects get-iam-policy YOUR_PROJECT_ID \
    --flatten="bindings[].members" \
    --filter="bindings.members:jenkins@YOUR_PROJECT_ID.iam.gserviceaccount.com"
```

### Error: "Docker push failed"

```bash
# Authenticate Docker
gcloud auth configure-docker gcr.io

# Test push manual
docker pull hello-world
docker tag hello-world gcr.io/YOUR_PROJECT_ID/test:latest
docker push gcr.io/YOUR_PROJECT_ID/test:latest
```

---

## 📚 Dokumentasi Lengkap

- **Penjelasan CI/CD:** [JENKINS_CI_CD_PENJELASAN.md](./JENKINS_CI_CD_PENJELASAN.md)
- **Setup Guide:** [JENKINS_SETUP_GUIDE.md](./JENKINS_SETUP_GUIDE.md)
- **Jenkinsfile:** [Jenkinsfile](./Jenkinsfile)

---

## 🎉 Selesai!

Setiap kali kamu push kode ke Git:
- ✅ Otomatis test
- ✅ Otomatis build Docker image
- ✅ Otomatis push ke GCR
- ✅ Otomatis deploy ke GKE

**Selamat! CI/CD sudah jalan!** 🚀

