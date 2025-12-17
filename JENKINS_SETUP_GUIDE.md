# 🚀 Panduan Setup Jenkins untuk CI/CD

## 📋 Daftar Isi
1. [Persiapan](#persiapan)
2. [Install Jenkins](#install-jenkins)
3. [Setup Jenkins](#setup-jenkins)
4. [Install Plugins](#install-plugins)
5. [Setup Credentials](#setup-credentials)
6. [Buat Pipeline Job](#buat-pipeline-job)
7. [Setup Webhook](#setup-webhook)
8. [Test Pipeline](#test-pipeline)
9. [Troubleshooting](#troubleshooting)

---

## 🔧 Persiapan

### 1. Pilih Lokasi Jenkins

**Opsi 1: Google Cloud Compute Engine (Recommended)**
- ✅ Mudah setup
- ✅ Terintegrasi dengan GCP
- ✅ Bisa pakai preemptible untuk hemat cost

**Opsi 2: Google Kubernetes Engine (GKE)**
- ✅ Scalable
- ✅ High availability
- ✅ Lebih kompleks setup

**Opsi 3: Server Sendiri**
- ✅ Full control
- ✅ Perlu maintain sendiri

### 2. Requirements

- **OS:** Ubuntu 20.04+ / Debian 10+ / CentOS 7+
- **RAM:** Minimum 2 GB (recommended: 4 GB)
- **Disk:** Minimum 20 GB
- **Java:** OpenJDK 11 atau 17

### 3. Tools yang Diperlukan

- Docker (untuk build image)
- kubectl (untuk deploy ke GKE)
- gcloud CLI (untuk akses GCP)

---

## 🏗️ Install Jenkins

### Opsi 1: Install di Google Cloud Compute Engine

```bash
# 1. Buat VM instance
gcloud compute instances create jenkins-server \
    --zone=asia-southeast2-a \
    --machine-type=e2-medium \
    --image-family=ubuntu-2004-lts \
    --image-project=ubuntu-os-cloud \
    --boot-disk-size=30GB

# 2. SSH ke VM
gcloud compute ssh jenkins-server --zone=asia-southeast2-a

# 3. Install Java
sudo apt update
sudo apt install openjdk-17-jdk -y

# 4. Install Jenkins
curl -fsSL https://pkg.jenkins.io/debian-stable/jenkins.io-2023.key | sudo tee \
  /usr/share/keyrings/jenkins-keyring.asc > /dev/null

echo deb [signed-by=/usr/share/keyrings/jenkins-keyring.asc] \
  https://pkg.jenkins.io/debian-stable binary/ | sudo tee \
  /etc/apt/sources.list.d/jenkins.list > /dev/null

sudo apt-get update
sudo apt-get install jenkins -y

# 5. Start Jenkins
sudo systemctl start jenkins
sudo systemctl enable jenkins

# 6. Cek status
sudo systemctl status jenkins
```

### Opsi 2: Install dengan Docker

```bash
# Run Jenkins di Docker
docker run -d \
  --name jenkins \
  -p 8080:8080 \
  -p 50000:50000 \
  -v jenkins_home:/var/jenkins_home \
  jenkins/jenkins:lts

# Get initial password
docker exec jenkins cat /var/jenkins_home/secrets/initialAdminPassword
```

### Opsi 3: Install di GKE (Helm)

```bash
# Install Helm
curl https://raw.githubusercontent.com/helm/helm/main/scripts/get-helm-3 | bash

# Add Jenkins Helm repo
helm repo add jenkins https://charts.jenkins.io
helm repo update

# Install Jenkins
helm install jenkins jenkins/jenkins \
  --namespace jenkins \
  --create-namespace \
  --set controller.serviceType=LoadBalancer
```

---

## ⚙️ Setup Jenkins

### 1. Akses Jenkins

```bash
# Jika di Compute Engine, buat firewall rule
gcloud compute firewall-rules create allow-jenkins \
    --allow tcp:8080 \
    --source-ranges 0.0.0.0/0 \
    --description "Allow Jenkins"

# Akses di browser
# http://YOUR_VM_IP:8080
```

### 2. Initial Setup

1. **Unlock Jenkins**
   - Copy initial admin password dari:
     ```bash
     sudo cat /var/lib/jenkins/secrets/initialAdminPassword
     ```
   - Paste di browser

2. **Install Suggested Plugins**
   - Pilih "Install suggested plugins"
   - Tunggu sampai selesai

3. **Create Admin User**
   - Isi username, password, email
   - Simpan credentials

4. **Instance Configuration**
   - Biarkan default atau ubah sesuai kebutuhan

---

## 🔌 Install Plugins

### Plugins yang Diperlukan

Masuk ke: **Manage Jenkins → Manage Plugins → Available**

Install plugins berikut:

1. **Git Plugin** - Pull kode dari Git
2. **Docker Pipeline Plugin** - Build Docker image
3. **Kubernetes Plugin** - Deploy ke Kubernetes
4. **Google Cloud Platform Plugin** - Akses GCP
5. **Blue Ocean** - UI yang lebih bagus (opsional)
6. **Pipeline** - Untuk Jenkinsfile
7. **Credentials Binding** - Manage credentials
8. **AnsiColor** - Warna di console output

### Install via Command Line

```bash
# SSH ke Jenkins server
sudo jenkins-plugin-cli --plugins \
    git \
    docker-workflow \
    kubernetes \
    google-cloud-platform \
    blueocean \
    pipeline-stage-view \
    credentials-binding \
    ansicolor
```

---

## 🔐 Setup Credentials

### 1. Google Cloud Service Account

```bash
# Buat service account untuk Jenkins
gcloud iam service-accounts create jenkins \
    --display-name="Jenkins Service Account"

# Beri permission
gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
    --member="serviceAccount:jenkins@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/container.developer"

gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
    --member="serviceAccount:jenkins@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/storage.admin"

gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
    --member="serviceAccount:jenkins@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
    --role="roles/container.clusterAdmin"

# Buat key
gcloud iam service-accounts keys create jenkins-key.json \
    --iam-account=jenkins@YOUR_PROJECT_ID.iam.gserviceaccount.com
```

**Di Jenkins:**
1. **Manage Jenkins → Credentials → System → Global credentials**
2. **Add Credentials**
3. **Kind:** Google Service Account from private key
4. **Upload:** `jenkins-key.json`
5. **ID:** `gcp-service-account`
6. **Save**

### 2. Docker Credentials (Opsional)

Jika pakai Docker Hub:
1. **Add Credentials**
2. **Kind:** Username with password
3. **Username:** Docker Hub username
4. **Password:** Docker Hub password
5. **ID:** `docker-hub`
6. **Save**

### 3. Git Credentials (Jika Private Repo)

1. **Add Credentials**
2. **Kind:** Username with password atau SSH Username with private key
3. **Isi:** Username & password atau SSH key
4. **ID:** `git-credentials`
5. **Save**

---

## 📝 Buat Pipeline Job

### 1. Create New Item

1. **Dashboard → New Item**
2. **Item name:** `inventorygudang-pipeline`
3. **Type:** Pipeline
4. **OK**

### 2. Configure Pipeline

**Tab: General**
- ✅ GitHub project (opsional): URL repository
- ✅ Build triggers: GitHub hook trigger for GITScm polling

**Tab: Pipeline**
- **Definition:** Pipeline script from SCM
- **SCM:** Git
- **Repository URL:** URL repository kamu
- **Credentials:** Pilih credentials Git (jika private)
- **Branches:** `*/main` atau `*/master`
- **Script Path:** `Jenkinsfile`
- **Save**

### 3. Update Jenkinsfile

Pastikan `Jenkinsfile` di root project sudah di-update dengan:
- Project ID yang benar
- Cluster name yang benar
- Zone yang benar

---

## 🔗 Setup Webhook

### GitHub

1. **Repository → Settings → Webhooks → Add webhook**
2. **Payload URL:** `http://YOUR_JENKINS_IP:8080/github-webhook/`
   - Atau jika pakai domain: `https://jenkins.yourdomain.com/github-webhook/`
3. **Content type:** `application/json`
4. **Events:** Pilih "Just the push event"
5. **Active:** ✅
6. **Add webhook**

### GitLab

1. **Repository → Settings → Webhooks**
2. **URL:** `http://YOUR_JENKINS_IP:8080/project/inventorygudang-pipeline`
3. **Trigger:** Push events
4. **Add webhook**

### Test Webhook

```bash
# Push perubahan kecil ke repository
git commit --allow-empty -m "Test webhook"
git push

# Cek Jenkins dashboard, harusnya pipeline langsung jalan
```

---

## ✅ Test Pipeline

### 1. Manual Trigger

1. **Dashboard → inventorygudang-pipeline**
2. **Build Now**
3. **Cek console output**

### 2. Test dengan Push

```bash
# Buat perubahan kecil
echo "# Test" >> README.md
git add README.md
git commit -m "Test CI/CD pipeline"
git push

# Cek Jenkins, pipeline harusnya otomatis jalan
```

### 3. Verifikasi

```bash
# Cek apakah image sudah di-push ke GCR
gcloud container images list --repository=gcr.io/YOUR_PROJECT_ID

# Cek deployment di GKE
kubectl get pods -n inventorygudang
kubectl get deployments -n inventorygudang
```

---

## 🔧 Troubleshooting

### Pipeline Tidak Jalan Setelah Push

```bash
# Cek webhook
# Di GitHub: Settings → Webhooks → Recent Deliveries
# Lihat apakah request berhasil

# Cek Jenkins logs
sudo tail -f /var/log/jenkins/jenkins.log

# Test manual trigger dulu
```

### Error: "Cannot connect to GKE"

```bash
# Pastikan service account punya permission
gcloud projects get-iam-policy YOUR_PROJECT_ID \
    --flatten="bindings[].members" \
    --filter="bindings.members:jenkins@YOUR_PROJECT_ID.iam.gserviceaccount.com"

# Test koneksi manual
gcloud container clusters get-credentials CLUSTER_NAME \
    --zone ZONE \
    --project YOUR_PROJECT_ID
```

### Error: "Docker push failed"

```bash
# Pastikan Docker sudah authenticate
gcloud auth configure-docker gcr.io

# Test push manual
docker pull hello-world
docker tag hello-world gcr.io/YOUR_PROJECT_ID/hello-world:test
docker push gcr.io/YOUR_PROJECT_ID/hello-world:test
```

### Error: "kubectl not found"

```bash
# Install kubectl di Jenkins server
sudo apt-get install kubectl -y

# Atau install via gcloud
gcloud components install kubectl
```

### Pipeline Lambat

```bash
# Cek resource Jenkins server
htop

# Tambah RAM/CPU jika perlu
# Atau pakai Jenkins agent (distributed build)
```

---

## 🎯 Best Practices

### 1. Environment Separation

```groovy
// Deploy ke dev dulu, baru production
stage('Deploy to Dev') {
    when { branch 'develop' }
    // ...
}

stage('Deploy to Production') {
    when { branch 'main' }
    // ...
}
```

### 2. Notifications

```groovy
post {
    success {
        slackSend(
            channel: '#deployments',
            message: "✅ Deployment successful!"
        )
    }
    failure {
        email(
            to: 'team@example.com',
            subject: 'Deployment Failed',
            body: 'Check Jenkins for details'
        )
    }
}
```

### 3. Secrets Management

```groovy
// Jangan hardcode secrets
// Pakai Jenkins credentials
withCredentials([string(credentialsId: 'db-password', variable: 'DB_PASS')]) {
    sh "echo $DB_PASS"
}
```

### 4. Build Caching

```groovy
// Cache Docker layers
sh """
    docker build \
        --cache-from gcr.io/PROJECT/image:latest \
        -t gcr.io/PROJECT/image:new \
        .
"""
```

---

## 📚 Referensi

- [Jenkins Documentation](https://www.jenkins.io/doc/)
- [Jenkins Pipeline Syntax](https://www.jenkins.io/doc/book/pipeline/syntax/)
- [Google Cloud Jenkins Guide](https://cloud.google.com/solutions/automated-build-images-with-jenkins-kubernetes)

---

## 🎉 Selesai!

CI/CD dengan Jenkins sudah siap! Setiap push ke Git akan otomatis:
- ✅ Test kode
- ✅ Build Docker image
- ✅ Push ke GCR
- ✅ Deploy ke GKE

**Selamat!** 🚀

