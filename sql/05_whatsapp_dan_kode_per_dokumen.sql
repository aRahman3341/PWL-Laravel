-- =====================================================================
-- 05. Nomor WhatsApp + kode akses per dokumen (aman dijalankan ulang)
-- =====================================================================

-- Nomor WhatsApp perusahaan (format internasional; bisa diubah di admin: Profil Perusahaan)
update public.company_info
set whatsapp = '6289644113257'
where whatsapp is null or whatsapp = '';

-- Kode akses boleh dibatasi untuk SATU dokumen. Kosong = berlaku untuk semua dokumen.
alter table public.access_codes
    add column if not exists file_id uuid
    references public.confidential_files(id) on delete cascade;
