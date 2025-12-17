# ============================================
# ☁️ GKE CLUSTER CONFIGURATION
# ============================================
# File ini membuat GKE cluster (tempat aplikasi Laravel akan jalan)

# Enable API yang diperlukan
resource "google_project_service" "gke_api" {
  project = var.project_id
  service = "container.googleapis.com"

  disable_on_destroy = false
}

resource "google_project_service" "compute_api" {
  project = var.project_id
  service = "compute.googleapis.com"

  disable_on_destroy = false
}

# GKE Cluster
resource "google_container_cluster" "main" {
  name     = var.cluster_name
  location = var.region

  # Tunggu API aktif dulu
  depends_on = [
    google_project_service.gke_api,
    google_project_service.compute_api
  ]

  # Hapus default node pool (kita akan buat sendiri)
  remove_default_node_pool = true
  initial_node_count       = 1

  # Network configuration
  network    = "default"
  subnetwork = "default"

  # Enable autoscaling (otomatis naik/turun jumlah node)
  enable_autopilot = false

  # Enable logging dan monitoring
  logging_service    = "logging.googleapis.com/kubernetes"
  monitoring_service = "monitoring.googleapis.com/kubernetes"

  # Network policy (keamanan)
  network_policy {
    enabled = true
  }

  # IP allocation policy
  ip_allocation_policy {
    cluster_secondary_range_name  = "pods"
    services_secondary_range_name  = "services"
  }

  # Master authorized networks (opsional, untuk keamanan)
  # Uncomment jika mau restrict akses ke cluster
  # master_authorized_networks_config {
  #   cidr_blocks {
  #     cidr_block   = "YOUR_IP/32"
  #     display_name = "My IP"
  #   }
  # }

  # Maintenance window
  maintenance_policy {
    daily_maintenance_window {
      start_time = "03:00"  # Maintenance jam 3 pagi
    }
  }

  # Lifecycle: jangan hancurkan cluster kalau ada perubahan kecil
  lifecycle {
    ignore_changes = [
      node_config,
    ]
  }
}

# Node Pool (kumpulan mesin virtual untuk menjalankan aplikasi)
resource "google_container_node_pool" "main" {
  name       = var.node_pool_name
  location   = var.region
  cluster    = google_container_cluster.main.name
  node_count = var.node_count

  # Autoscaling (otomatis naik/turun jumlah node)
  autoscaling {
    min_node_count = 1   # Minimum 1 node
    max_node_count = 5   # Maximum 5 node
  }

  # Konfigurasi node
  node_config {
    # Tipe mesin
    machine_type = var.machine_type

    # Disk
    disk_size_gb = var.disk_size_gb
    disk_type    = "pd-standard"  # Standard persistent disk

    # Image untuk node (OS)
    image_type = "COS_CONTAINERD"  # Container-Optimized OS

    # OAuth scopes (izin akses)
    oauth_scopes = [
      "https://www.googleapis.com/auth/cloud-platform"
    ]

    # Labels (tag untuk identifikasi)
    labels = {
      environment = var.environment
      app         = "inventorygudang"
    }

    # Taints (opsional, untuk scheduling khusus)
    # Uncomment jika mau restrict pod tertentu
    # taint {
    #   key    = "app"
    #   value  = "inventorygudang"
    #   effect = "NO_SCHEDULE"
    # }
  }

  # Management (auto-repair & auto-upgrade)
  management {
    auto_repair  = true   # Otomatis perbaiki node yang rusak
    auto_upgrade = true   # Otomatis upgrade node
  }

  # Upgrade settings
  upgrade_settings {
    max_surge       = 1
    max_unavailable = 0
  }
}

