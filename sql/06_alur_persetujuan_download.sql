-- =====================================================================
-- 06. Alur download dengan persetujuan + lampiran file + pengaturan
-- Aman dijalankan ulang. Jalankan di Supabase: SQL Editor -> Run.
-- =====================================================================

-- Daftar file: bisa milik perusahaan, sebuah proyek, atau sebuah layanan
alter table public.confidential_files add column if not exists owner_type varchar(20) not null default 'company';
alter table public.confidential_files add column if not exists owner_id uuid;
alter table public.confidential_files add column if not exists storage varchar(20) not null default 'supabase';

-- Satu proyek / satu layanan hanya punya satu lampiran
create unique index if not exists uq_confidential_owner
    on public.confidential_files (owner_type, owner_id) where owner_id is not null;

-- Permintaan download dari pengunjung (menunggu persetujuan manusia)
create table if not exists public.download_requests (
    id              uuid primary key default gen_random_uuid(),
    file_id         uuid not null references public.confidential_files(id) on delete cascade,
    requester_name  varchar(150) not null,
    purpose         text not null,
    status          varchar(20) not null default 'pending'
                    check (status in ('pending', 'approved', 'rejected', 'expired')),
    token_hash      text not null,           -- pengikat ke browser pemohon
    code_enc        text,                    -- kode 6 angka (terenkripsi)
    code_expires_at timestamptz,
    decided_by      uuid references auth.users(id) on delete set null,
    decided_at      timestamptz,
    downloaded_at   timestamptz,
    ip_address      varchar(64),
    user_agent      varchar(255),
    created_at      timestamptz not null default now()
);
create index if not exists idx_download_requests_status on public.download_requests (status, created_at desc);

-- Pengaturan situs (email penerima notifikasi, kalimat sapaan WhatsApp, dll)
create table if not exists public.site_settings (
    key        varchar(60) primary key,
    value      text,
    updated_at timestamptz not null default now()
);

-- Terkunci dari akses langsung browser; Laravel memakai service key di server
alter table public.download_requests enable row level security;
alter table public.site_settings     enable row level security;

insert into public.site_settings (key, value) values
    ('notify_email', 'pt.pwlkonsultan@gmail.com'),
    ('wa_greeting',  'saya ingin bertanya mengenai layanan konsultansi.')
on conflict (key) do nothing;
