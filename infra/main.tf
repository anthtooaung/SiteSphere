provider "google" {
  project = var.project_id
  region  = var.region
}

# Cloud SQL Instance
resource "google_sql_database_instance" "main" {
  name             = "sitesphere-db"
  database_version = "MYSQL_8_0"
  region           = var.region
  settings {
    tier = "db-f1-micro"
  }
}

resource "google_sql_database" "database" {
  name     = "sitesphere"
  instance = google_sql_database_instance.main.name
}

# Cloud Storage Bucket
resource "google_storage_bucket" "uploads" {
  name          = "${var.project_id}-uploads"
  location      = var.region
  force_destroy = true
}

# Cloud Run Service
resource "google_cloud_run_v2_service" "default" {
  name     = "sitesphere-service"
  location = var.region
  ingress  = "INGRESS_TRAFFIC_ALL"

  template {
    containers {
      image = "gcr.io/${var.project_id}/sitesphere:latest"
      env {
        name  = "DB_HOST"
        value = google_sql_database_instance.main.connection_name
      }
      env {
        name  = "DB_DATABASE"
        value = google_sql_database.database.name
      }
      env {
        name  = "DB_PASSWORD"
        value = var.db_password
      }
    }
  }
}
