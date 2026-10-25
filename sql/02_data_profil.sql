-- =====================================================================
-- 02. Data profil dari Company Profile PT. PWL Konsultan (Sept 2026)
-- Aman dijalankan ulang: tidak menimpa data yang sudah Anda isi.
-- =====================================================================

-- Visi, Misi, NIB (hanya mengisi yang masih kosong)
update public.company_info set
    vision  = coalesce(nullif(vision, ''),  'Menjadi perusahaan yang profesional dan dapat bersaing secara sehat serta berperan aktif dalam mewujudkan manusia Indonesia yang mandiri dalam menyongsong era globalisasi, dengan selalu meningkatkan mutu sumber daya manusia dan penguasaan teknologi informasi.'),
    mission = coalesce(nullif(mission, ''), 'Memberikan pelayanan yang terbaik dan membantu owner, baik pemerintah maupun swasta, dalam melaksanakan pembangunan yang berkelanjutan.'),
    nib     = coalesce(nullif(nib, ''),     '8120016021187');

-- Pengurus perusahaan (tampil di halaman Profil -> Manajemen)
insert into public.management (name, position, sort_order, is_active)
select v.name, v.position, v.sort_order::int, true
from (values
    ('Junjunan Maulana Yusuf, S.Ap', 'Komisaris',                     '1'),
    ('Iim Mulnadi, ST.',             'Direktur Utama',                '2'),
    ('Dede Gusan Gunawarsa, ST.',    'Direktur I',                    '3'),
    ('Budi Permana, ST',             'Direktur II',                   '4'),
    ('Ir. M. Arif Yusry, IPM',       'Manajer Teknik',                '5'),
    ('Dani Jaenudin, SE',            'Manajer Keuangan & Administrasi','6'),
    ('Bambang Kuhartono, ST',        'Manajer Pemasaran',             '7')
) as v(name, position, sort_order)
where not exists (select 1 from public.management m where m.name = v.name);

-- Legalitas publik (tampil di halaman Profil -> Legalitas & Sertifikasi)
-- Hanya nomor dokumen, TANPA data pribadi. Dokumen sensitif lewat Dokumen Rahasia.
insert into public.legal_documents (name, document_type, document_number, issuer, issued_date, sort_order, is_active)
select v.name, v.document_type, v.document_number, v.issuer, v.issued_date::date, v.sort_order::int, true
from (values
    ('NPWP',                        'Perpajakan',          '81.586.513.4-444.000',                                   null,                          null,         '1'),
    ('NIB / IUJK',                  'Perizinan Berusaha',  '8120016021187',                                          'OSS Pemerintah RI',           '2018-10-18', '2'),
    ('SIUP',                        'Izin Usaha',          '00456/10-12/PK/IV/2017',                                 null,                          '2017-04-25', '3'),
    ('Domisili',                    'Surat Keterangan',    '420/02/Kesra',                                           'Pemerintah Desa Cibiru Wetan','2020-02-12', '4'),
    ('Akta Pendirian CV',           'Akta',                'No. 01 - 1 Maret 2007 (Notaris Ratu Zulyani Minpaduka, SH)', null,                      '2007-03-01', '5'),
    ('Akta Perubahan CV ke PT',     'Akta',                'No. 14 - 14 Februari 2017 (Notaris Harry Susanto, SH)',  null,                          '2017-02-14', '6'),
    ('Akta Perubahan Terakhir',     'Akta',                'No. 09 - 24 Maret 2022 (Notaris Harry Susanto, SH)',     null,                          '2022-03-24', '7'),
    ('Pengesahan Kemenkumham',      'Pengesahan',          'AHU-0021866.AH.01.02 Tahun 2022',                        'Kemenkumham RI',              null        , '8'),
    ('Keanggotaan PERKINDO',        'Asosiasi',            '0327.32.03.2025',                                        'PERKINDO',                    null,         '9')
) as v(name, document_type, document_number, issuer, issued_date, sort_order)
where not exists (select 1 from public.legal_documents l where l.name = v.name);
