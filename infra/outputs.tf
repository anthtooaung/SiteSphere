output "cloud_run_url" {
  value = google_cloud_run_v2_service.default.uri
}

output "sql_instance_connection_name" {
  value = google_sql_database_instance.main.connection_name
}

output "storage_bucket_name" {
  value = google_storage_bucket.uploads.name
}
