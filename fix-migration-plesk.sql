-- Script untuk membersihkan record migrasi yang tidak sesuai
-- Jalankan di database MySQL Plesk sebelum migrasi

-- 1. Cek dulu record migrasi yang ada
SELECT migration FROM migrations WHERE migration LIKE '%notification%' ORDER BY id;

-- 2. Hapus record migrasi yang tidak ada file-nya (jika ada)
DELETE FROM migrations
WHERE migration IN (
    '2025_01_20_000001_add_indexes_to_notifications_table',
    '2025_04_24_044917_create_notifications_table',
    '2025_04_24_044918_add_indexes_to_notifications_table'
);

-- 3. Verifikasi hasil (harus kosong atau hanya yang valid)
SELECT migration FROM migrations WHERE migration LIKE '%notification%' ORDER BY id;
