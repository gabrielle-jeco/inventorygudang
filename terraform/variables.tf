# ============================================
# 📝 VARIABLES - Data yang Bisa Diubah-Ubah
# ============================================
# File ini berisi "variabel" yang bisa kamu ubah sesuai kebutuhan
# Seperti "resep masakan" yang bisa disesuaikan porsinya

# Project ID di Google Cloud
variable "project_id" {
  description = "ID Project Google Cloud kamu"
  type        = string
  # Contoh: "my-inventory-project"
}

# Region (lokasi data center)
variable "region" {
  description = "Region untuk GKE cluster"
  type        = string
  default     = "asia-southeast2"  # Jakarta
  # Pilihan lain: asia-southeast1 (Singapore), us-central1, dll
}

# Zone (sub-lokasi dalam region)
variable "zone" {
  description = "Zone untuk GKE cluster"
  type        = string
  default     = "asia-southeast2-a"
}

# Nama cluster GKE
variable "cluster_name" {
  description = "Nama untuk GKE cluster"
  type        = string
  default     = "inventorygudang-cluster"
}

# Versi Kubernetes
variable "kubernetes_version" {
  description = "Versi Kubernetes yang akan digunakan"
  type        = string
  default     = "latest"
}

# Jumlah node (mesin virtual) di cluster
variable "node_count" {
  description = "Jumlah node di setiap zone"
  type        = number
  default     = 2
  # 2 node = 2 mesin virtual (bisa naik/turun sesuai kebutuhan)
}

# Tipe mesin untuk node
variable "machine_type" {
  description = "Tipe mesin untuk node (CPU & RAM)"
  type        = string
  default     = "e2-medium"
  # e2-medium = 2 vCPU, 4 GB RAM
  # Pilihan lain: e2-small (2 vCPU, 2 GB), e2-standard-2 (2 vCPU, 8 GB), dll
}

# Nama untuk node pool
variable "node_pool_name" {
  description = "Nama untuk node pool"
  type        = string
  default     = "inventorygudang-pool"
}

# Disk size untuk setiap node (dalam GB)
variable "disk_size_gb" {
  description = "Ukuran disk untuk setiap node"
  type        = number
  default     = 20
}

# Environment (dev, staging, production)
variable "environment" {
  description = "Environment (dev, staging, production)"
  type        = string
  default     = "dev"
}

