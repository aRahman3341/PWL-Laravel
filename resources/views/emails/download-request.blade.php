<!DOCTYPE html>
<html lang="id">
<body style="margin:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#334155">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:24px 12px"><tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0">
    <tr><td style="background:#0B3D91;color:#ffffff;padding:20px 24px">
        <div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#bfdbfe">PWL Konsultan</div>
        <div style="font-size:20px;font-weight:bold;margin-top:4px">Permintaan download baru</div>
    </td></tr>
    <tr><td style="padding:24px">
        <p style="margin:0 0 16px;font-size:15px"><strong>{{ $req['requester_name'] }}</strong> ingin mengunduh file berikut dan menunggu persetujuan Anda.</p>
        <table width="100%" cellpadding="8" cellspacing="0" style="font-size:14px;border:1px solid #e2e8f0;border-radius:8px">
            <tr><td width="130" style="color:#64748b;border-bottom:1px solid #f1f5f9">File</td><td style="border-bottom:1px solid #f1f5f9">@foreach ($files as $f)<strong>{{ $f['title'] }}</strong><br>@endforeach<span style="color:#64748b;font-size:12px">{{ $owner }}</span></td></tr>
            <tr><td style="color:#64748b;border-bottom:1px solid #f1f5f9">Nama pemohon</td><td style="border-bottom:1px solid #f1f5f9">{{ $req['requester_name'] }}</td></tr>
            <tr><td style="color:#64748b;border-bottom:1px solid #f1f5f9;vertical-align:top">Kebutuhan</td><td style="border-bottom:1px solid #f1f5f9">{!! nl2br(e($req['purpose'])) !!}</td></tr>
            <tr><td style="color:#64748b;border-bottom:1px solid #f1f5f9">Waktu</td><td style="border-bottom:1px solid #f1f5f9">{{ \Illuminate\Support\Carbon::parse($req['created_at'])->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</td></tr>
            <tr><td style="color:#64748b">Alamat IP</td><td>{{ $req['ip_address'] ?? '-' }}</td></tr>
        </table>
        <p style="margin:24px 0 8px;font-size:14px">Klik tombol di bawah untuk <strong>meninjau</strong>, lalu pilih Setujui atau Tolak. Kode 6 angka akan dibuat otomatis dan langsung muncul di layar pemohon.</p>
        <p style="margin:16px 0"><a href="{{ $reviewUrl }}" style="display:inline-block;background:#0B3D91;color:#ffffff;text-decoration:none;font-weight:bold;padding:13px 28px;border-radius:10px;font-size:15px">Tinjau permintaan</a></p>
        <p style="margin:20px 0 0;font-size:12px;color:#94a3b8">Tautan berlaku 2 hari. Anda juga bisa memprosesnya di panel admin: <a href="{{ $panelUrl }}" style="color:#0B3D91">Permintaan Download</a>.<br>Jika Anda tidak mengenal pemohon, abaikan email ini atau tekan Tolak.</p>
    </td></tr>
</table>
</td></tr></table>
</body>
</html>
