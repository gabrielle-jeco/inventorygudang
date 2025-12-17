# ============================================
# 🏗️ MAIN TERRAFORM CONFIGURATION
# ============================================
# File ini adalah "resep utama" untuk membuat infrastruktur
# Terraform akan membaca file ini dan membuat semua yang diperlukan

terraform {
  required_version = ">= 1.0"

  # Backend untuk menyimpan state file (opsional, bisa pakai local juga)
  # Uncomment jika mau pakai Google Cloud Storage untuk state
  # backend "gcs" {
  #   bucket = "inventorygudang-terraform-state"
  #   prefix = "terraform/state"
  # }

  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "~> 5.0"
    }
  }
}

# Provider Google Cloud
# Ini seperti "koneksi" ke Google Cloud
provider "google" {
  project = var.project_id
  region  = var.region
  zone    = var.zone
}

