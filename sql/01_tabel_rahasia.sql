-- =====================================================================
-- 01. Tabel untuk dokumen rahasia + kode akses (aman dijalankan ulang)
-- Jalankan di Supabase: SQL Editor -> New query -> tempel -> Run
-- =====================================================================

create table if not exists public.access_codes (
    id          uuid primary key default gen_random_uuid(),
    label       varchar(150) not null,
    code_hash   text not null unique,
    expires_at  timestamptz,
    max_uses    integer,
    used_count  integer not null default 0,
    is_active   boolean not null default true,
    created_by  uuid references auth.users(id) on delete set null,
    created_at  timestamptz not null default now()
);

create table if not exists public.confidential_files (
    id           uuid primary key default gen_random_uuid(),
    title        varchar(255) not null,
    file_name    varchar(255) not null,
    storage_path text not null,
    file_size    bigint,
    is_active    boolean not null default true,
    created_by   uuid references auth.users(id) on delete set null,
    created_at   timestamptz not null default now()
);

-- RLS aktif tanpa policy = tidak bisa diakses lewat anon key / browser.
-- Laravel memakai service key (server) sehingga tetap bisa membaca/menulis.
alter table public.access_codes       enable row level security;
alter table public.confidential_files enable row level security;

-- Bucket PRIVAT untuk file rahasia (tidak punya URL publik)
insert into storage.buckets (id, name, public)
values ('confidential', 'confidential', false)
on conflict (id) do nothing;
