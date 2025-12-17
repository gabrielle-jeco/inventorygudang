# ============================================
# 📤 OUTPUTS - Informasi Setelah Dibuat
# ============================================
# File ini menampilkan informasi penting setelah infrastruktur dibuat
# Seperti "hasil akhir" setelah robot selesai bekerja

# Informasi Cluster
output "cluster_name" {
  description = "Nama GKE cluster"
  value       = google_container_cluster.main.name
}

output "cluster_endpoint" {
  description = "Endpoint untuk akses cluster"
  value       = google_container_cluster.main.endpoint
  sensitive   = false
}

output "cluster_ca_certificate" {
  description = "CA Certificate untuk cluster"
  value       = google_container_cluster.main.master_auth[0].cluster_ca_certificate
  sensitive   = true
}

# Command untuk setup kubectl
output "gke_connect_command" {
  description = "Command untuk connect ke cluster"
  value       = "gcloud container clusters get-credentials ${google_container_cluster.main.name} --region ${var.region} --project ${var.project_id}"
}

# Informasi Node Pool
output "node_pool_name" {
  description = "Nama node pool"
  value       = google_container_node_pool.main.name
}

# Informasi Project
output "project_id" {
  description = "Project ID"
  value       = var.project_id
}

output "region" {
  description = "Region cluster"
  value       = var.region
}

