-- Membersihkan data runtime Laravel sebelum hosting.
-- Script ini tidak menghapus produk, user, banner, order, atau transaksi.
-- Jalankan setelah backup database.

SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE sessions;
TRUNCATE TABLE cache;
TRUNCATE TABLE cache_locks;
TRUNCATE TABLE jobs;
TRUNCATE TABLE job_batches;
TRUNCATE TABLE failed_jobs;
TRUNCATE TABLE notifications;
TRUNCATE TABLE admin_push_subscriptions;
TRUNCATE TABLE password_reset_tokens;
SET FOREIGN_KEY_CHECKS=1;
