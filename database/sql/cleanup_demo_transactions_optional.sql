-- OPSIONAL: Menghapus data transaksi uji sebelum website online.
-- Jangan jalankan script ini jika order lama masih ingin disimpan.
-- Jalankan hanya setelah backup database.

SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE cart_items;
TRUNCATE TABLE wishlists;
TRUNCATE TABLE order_tracking_histories;
TRUNCATE TABLE order_items;
TRUNCATE TABLE orders;
SET FOREIGN_KEY_CHECKS=1;
