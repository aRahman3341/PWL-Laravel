-- =========================================================
-- PWL - PT. PURNA WAHANA LESTARI KONSULTAN
-- DATABASE SCHEMA
-- Supabase / PostgreSQL
-- =========================================================

create extension if not exists "pgcrypto";


-- =========================================================
-- 1. UPDATED_AT TRIGGER
-- =========================================================

create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
    new.updated_at = now();
    return new;
end;
$$;


-- =========================================================
-- 2. COMPANY INFORMATION
-- =========================================================

create table if not exists public.company_info (
    id uuid primary key default gen_random_uuid(),

    name varchar(255) not null,
    short_name varchar(100),

    founded_year integer,

    about text,
    vision text,
    mission text,

    address text,
    village varchar(150),
    district varchar(150),
    regency varchar(150),
    province varchar(150),
    postal_code varchar(20),

    phone varchar(50),
    whatsapp varchar(50),
    email varchar(150),
    website varchar(255),

    npwp varchar(50),
    nib varchar(100),
    perkindo_number varchar(100),

    leader_name varchar(255),
    operational_manager varchar(255),

    logo_url text,
    favicon_url text,

    is_active boolean not null default true,

    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

drop trigger if exists trg_company_info_updated_at
on public.company_info;

create trigger trg_company_info_updated_at
before update on public.company_info
for each row
execute function public.set_updated_at();


-- =========================================================
-- 3. SERVICE / BIDANG USAHA
-- =========================================================
-- category:
--   Konstruksi
--   Non Konstruksi
--
-- Digunakan langsung oleh:
-- App\Services\Supabase::servicesByCategory()
-- =========================================================

create table if not exists public.services (
    id uuid primary key default gen_random_uuid(),

    category varchar(30) not null
        check (category in ('Konstruksi', 'Non Konstruksi')),

    name varchar(255) not null,
    code varchar(100),

    description text,

    qualification varchar(20),
    -- Kecil / Menengah / Besar

    icon varchar(100),

    sort_order integer not null default 0,
    is_active boolean not null default true,

    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_services_category
on public.services(category);

create index if not exists idx_services_active
on public.services(is_active);

drop trigger if exists trg_services_updated_at
on public.services;

create trigger trg_services_updated_at
before update on public.services
for each row
execute function public.set_updated_at();


-- =========================================================
-- 4. SUB-BIDANG / KLASIFIKASI USAHA
-- =========================================================

create table if not exists public.service_subfields (
    id uuid primary key default gen_random_uuid(),

    service_id uuid references public.services(id)
        on delete cascade,

    category varchar(30) not null
        check (category in ('Konstruksi', 'Non Konstruksi')),

    code varchar(100),
    name varchar(255) not null,

    description text,

    qualification varchar(20),

    sort_order integer not null default 0,
    is_active boolean not null default true,

    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_service_subfields_service
on public.service_subfields(service_id);

create index if not exists idx_service_subfields_category
on public.service_subfields(category);

drop trigger if exists trg_service_subfields_updated_at
on public.service_subfields;

create trigger trg_service_subfields_updated_at
before update on public.service_subfields
for each row
execute function public.set_updated_at();


-- =========================================================
-- 5. PENGALAMAN / PROYEK
-- =========================================================
-- Ini adalah tabel yang dibaca oleh:
-- Supabase::projects()
--
-- category:
--   konstruksi
--   non-konstruksi
-- =========================================================

create table if not exists public.projects (
    id uuid primary key default gen_random_uuid(),

    project_code varchar(100),

    name varchar(500) not null,

    category varchar(30) not null
        check (category in ('konstruksi', 'non-konstruksi')),

    subfield varchar(255),

    classification_code varchar(100),

    client_name varchar(255),

    contract_number varchar(150),

    contract_date date,

    location text,

    province varchar(150),

    regency varchar(150),

    description text,

    scope_of_work text,

    contract_value numeric(18,2),

    year integer,

    start_date date,
    end_date date,

    project_status varchar(50)
        default 'Selesai',

    image_url text,

    document_url text,

    is_featured boolean not null default false,

    is_active boolean not null default true,

    sort_order integer not null default 0,

    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_projects_category
on public.projects(category);

create index if not exists idx_projects_year
on public.projects(year);

create index if not exists idx_projects_active
on public.projects(is_active);

create index if not exists idx_projects_featured
on public.projects(is_featured);

drop trigger if exists trg_projects_updated_at
on public.projects;

create trigger trg_projects_updated_at
before update on public.projects
for each row
execute function public.set_updated_at();


-- =========================================================
-- 6. DOKUMEN LEGALITAS
-- =========================================================

create table if not exists public.legal_documents (
    id uuid primary key default gen_random_uuid(),

    name varchar(255) not null,

    document_type varchar(100),

    document_number varchar(255),

    issuer varchar(255),

    issued_date date,
    expiry_date date,

    description text,

    file_url text,

    is_active boolean not null default true,

    sort_order integer not null default 0,

    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_legal_documents_active
on public.legal_documents(is_active);

drop trigger if exists trg_legal_documents_updated_at
on public.legal_documents;

create trigger trg_legal_documents_updated_at
before update on public.legal_documents
for each row
execute function public.set_updated_at();


-- =========================================================
-- 7. MANAGEMENT / PIMPINAN
-- =========================================================

create table if not exists public.management (
    id uuid primary key default gen_random_uuid(),

    name varchar(255) not null,

    position varchar(255) not null,

    division varchar(255),

    education varchar(255),

    biography text,

    photo_url text,

    email varchar(150),
    phone varchar(50),

    sort_order integer not null default 0,

    is_active boolean not null default true,

    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_management_active
on public.management(is_active);

drop trigger if exists trg_management_updated_at
on public.management;

create trigger trg_management_updated_at
before update on public.management
for each row
execute function public.set_updated_at();


-- =========================================================
-- 8. PESAN KONTAK
-- =========================================================

create table if not exists public.messages (
    id uuid primary key default gen_random_uuid(),

    name varchar(100) not null,

    email varchar(150) not null,

    phone varchar(50),

    body text not null,

    status varchar(30) not null default 'baru'
        check (status in ('baru', 'dibaca', 'diproses', 'selesai', 'spam')),

    admin_note text,

    handled_by uuid,

    handled_at timestamptz,

    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_messages_status
on public.messages(status);

create index if not exists idx_messages_created
on public.messages(created_at desc);

drop trigger if exists trg_messages_updated_at
on public.messages;

create trigger trg_messages_updated_at
before update on public.messages
for each row
execute function public.set_updated_at();


-- =========================================================
-- 9. USER / PROFILE
-- =========================================================
-- Login menggunakan Supabase Auth.
-- Password TIDAK disimpan di tabel ini.
--
-- Role:
--   super_admin
--   admin
--   management
-- =========================================================

create table if not exists public.profiles (
    id uuid primary key references auth.users(id)
        on delete cascade,

    full_name varchar(255) not null,

    email varchar(255),

    role varchar(30) not null default 'management'
        check (role in ('super_admin', 'admin', 'management')),

    phone varchar(50),

    avatar_url text,

    is_active boolean not null default true,

    last_login_at timestamptz,

    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists idx_profiles_role
on public.profiles(role);

create index if not exists idx_profiles_active
on public.profiles(is_active);

drop trigger if exists trg_profiles_updated_at
on public.profiles;

create trigger trg_profiles_updated_at
before update on public.profiles
for each row
execute function public.set_updated_at();


-- =========================================================
-- 10. AUDIT LOG
-- =========================================================
-- Untuk mencatat perubahan dari dashboard admin.
-- =========================================================

create table if not exists public.audit_logs (
    id uuid primary key default gen_random_uuid(),

    user_id uuid references auth.users(id)
        on delete set null,

    action varchar(50) not null,

    table_name varchar(100),

    record_id uuid,

    old_data jsonb,

    new_data jsonb,

    ip_address inet,

    user_agent text,

    created_at timestamptz not null default now()
);

create index if not exists idx_audit_logs_user
on public.audit_logs(user_id);

create index if not exists idx_audit_logs_table
on public.audit_logs(table_name);

create index if not exists idx_audit_logs_created
on public.audit_logs(created_at desc);


-- =========================================================
-- 11. STORAGE / GALERI DOKUMEN
-- =========================================================

create table if not exists public.media (
    id uuid primary key default gen_random_uuid(),

    title varchar(255),

    file_name varchar(255) not null,

    file_url text not null,

    file_type varchar(100),

    file_size bigint,

    folder varchar(100),

    uploaded_by uuid references auth.users(id)
        on delete set null,

    is_active boolean not null default true,

    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

drop trigger if exists trg_media_updated_at
on public.media;

create trigger trg_media_updated_at
before update on public.media
for each row
execute function public.set_updated_at();


-- =========================================================
-- 12. DATA AWAL COMPANY
-- =========================================================

insert into public.company_info (
    name,
    founded_year,
    about,
    address,
    phone,
    email,
    leader_name,
    operational_manager,
    npwp,
    perkindo_number
)
select
    'PT. Purna Wahana Lestari Konsultan',
    2007,
    'PT. Purna Wahana Lestari Konsultan didirikan di Bandung pada tahun 2007 dan menjadi Perseroan Terbatas pada 2017. Kami memberikan jasa konsultansi konstruksi dan non konstruksi yang efisien, efektif, dan tepat guna.',
    'Komp. Cibiru Asri Blok L No. 02 Desa Cibiru Wetan, Kec. Cileunyi, Kab. Bandung',
    '022-7813823',
    'pt.pwlkonsultan@gmail.com',
    'IIM MULNADI, S.T',
    'IIM MULNADI, S.T',
    '81.586.513.4-444.000',
    '0327.32.03.2025'
where not exists (
    select 1 from public.company_info
);


-- =========================================================
-- 13. CONTOH BIDANG USAHA
-- =========================================================

insert into public.services
(category, name, code, description, qualification, sort_order)
select
    'Konstruksi',
    'Jasa Konsultansi Konstruksi',
    null,
    'Jasa konsultansi konstruksi sesuai dengan bidang dan klasifikasi usaha perusahaan.',
    'Kecil',
    1
where not exists (
    select 1
    from public.services
    where category = 'Konstruksi'
);


insert into public.services
(category, name, code, description, qualification, sort_order)
select
    'Non Konstruksi',
    'Jasa Konsultansi Non Konstruksi',
    null,
    'Jasa konsultansi non-konstruksi dalam berbagai bidang sesuai klasifikasi usaha perusahaan.',
    'Kecil',
    2
where not exists (
    select 1
    from public.services
    where category = 'Non Konstruksi'
);


-- =========================================================
-- 14. ROW LEVEL SECURITY
-- =========================================================

alter table public.company_info enable row level security;
alter table public.services enable row level security;
alter table public.service_subfields enable row level security;
alter table public.projects enable row level security;
alter table public.legal_documents enable row level security;
alter table public.management enable row level security;
alter table public.messages enable row level security;
alter table public.profiles enable row level security;
alter table public.audit_logs enable row level security;
alter table public.media enable row level security;


-- =========================================================
-- 15. PUBLIC READ POLICY
-- =========================================================
-- Website publik boleh membaca data yang aktif.
-- Service key yang digunakan backend Laravel tetap dapat
-- melewati RLS.
-- =========================================================

drop policy if exists "public read company" on public.company_info;
create policy "public read company"
on public.company_info
for select
using (is_active = true);


drop policy if exists "public read services" on public.services;
create policy "public read services"
on public.services
for select
using (is_active = true);


drop policy if exists "public read service subfields" on public.service_subfields;
create policy "public read service subfields"
on public.service_subfields
for select
using (is_active = true);


drop policy if exists "public read projects" on public.projects;
create policy "public read projects"
on public.projects
for select
using (is_active = true);


drop policy if exists "public read legal documents" on public.legal_documents;
create policy "public read legal documents"
on public.legal_documents
for select
using (is_active = true);


drop policy if exists "public read management" on public.management;
create policy "public read management"
on public.management
for select
using (is_active = true);


-- =========================================================
-- 16. PUBLIC CONTACT INSERT
-- =========================================================

drop policy if exists "public insert messages" on public.messages;
create policy "public insert messages"
on public.messages
for insert
with check (true);


-- =========================================================
-- 17. PROFILE POLICY
-- =========================================================

drop policy if exists "users read own profile" on public.profiles;
create policy "users read own profile"
on public.profiles
for select
to authenticated
using (auth.uid() = id);


drop policy if exists "users update own profile" on public.profiles;
create policy "users update own profile"
on public.profiles
for update
to authenticated
using (auth.uid() = id)
with check (auth.uid() = id);


-- =========================================================
-- SELESAI
-- =========================================================