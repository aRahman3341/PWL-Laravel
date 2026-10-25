<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseAdmin;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(private SupabaseAdmin $db) {}

    public function edit()
    {
        return view('admin.company', ['row' => $this->db->first('company_info') ?? []]);
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255', 'short_name' => 'nullable|string|max:100',
            'founded_year' => 'nullable|integer|min:1900|max:2100',
            'about' => 'nullable|string', 'vision' => 'nullable|string', 'mission' => 'nullable|string',
            'address' => 'nullable|string|max:500', 'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50', 'email' => 'nullable|email|max:150',
            'website' => 'nullable|string|max:200',
            'npwp' => 'nullable|string|max:50', 'nib' => 'nullable|string|max:50',
            'perkindo_number' => 'nullable|string|max:50',
            'leader_name' => 'nullable|string|max:150', 'operational_manager' => 'nullable|string|max:150',
        ]);

        $row = $this->db->first('company_info');
        $res = $row
            ? $this->db->update('company_info', ['id' => 'eq.' . $row['id']], $data)
            : $this->db->insert('company_info', $data);

        if (! $res['ok']) {
            return back()->withInput()->withErrors(['db' => 'Gagal menyimpan: ' . $res['error']]);
        }

        $this->db->flushPublic();
        $this->db->audit('update', 'company_info', $row['id'] ?? null, $data);

        return back()->with('ok', 'Profil perusahaan diperbarui.');
    }
}
