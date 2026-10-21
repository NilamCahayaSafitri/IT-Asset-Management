<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\KategoriAset;
use App\Models\Pegawai;
use App\Models\Ruangan;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Departemen
        $dept = [];
        foreach (['IT Support', 'Finance', 'Operations', 'HR', 'Marketing'] as $nama) {
            $dept[$nama] = Departemen::firstOrCreate(['nama_departemen' => $nama]);
        }

        // Ruangan (nama ruangan => departemen)
        $ruangan = [
            'R. IT Support' => 'IT Support',
            'R. Server'     => 'IT Support',
            'Gudang ICT'    => 'IT Support',
            'R. Finance'    => 'Finance',
            'R. Meeting 2'  => 'Operations',
            'R. HR'         => 'HR',
            'R. Marketing'  => 'Marketing',
        ];
        foreach ($ruangan as $nama => $namaDept) {
            Ruangan::firstOrCreate(
                ['nama_ruangan' => $nama],
                ['id_departemen' => $dept[$namaDept]->id]
            );
        }

        // Kategori aset
        foreach (['Laptop', 'Monitor', 'Mouse / Keyboard', 'Proyektor', 'Server / Rack', 'Headset'] as $nama) {
            KategoriAset::firstOrCreate(['nama_kategori' => $nama]);
        }

        // Vendor
        Vendor::firstOrCreate(
            ['nama_vendor' => 'PT Mitra Komputer Indo'],
            ['alamat' => 'Jakarta Selatan', 'kontak' => '021-0000000', 'email' => 'sales@mitrakomputer.example']
        );
        Vendor::firstOrCreate(
            ['nama_vendor' => 'CV Berkah Elektronik'],
            ['alamat' => 'Jakarta Barat', 'kontak' => '021-1111111', 'email' => 'info@berkah.example']
        );

        // Pegawai (kode_pegawai = "User ID" pada form surat)
        $pegawai = [
            ['EMP-00231', 'Rian Saputra',    'Staff IT Support', 'IT Support', 'rian.s@pge.example',  '0812xxxxxxx', 'R. IT Support'],
            ['EMP-00118', 'Dewi Anjani',     'Staff Finance',    'Finance',    'dewi.a@pge.example',  '0813xxxxxxx', 'R. Finance'],
            ['EMP-00145', 'Fajar Nugroho',   'Staff Operations', 'Operations', 'fajar.n@pge.example', '0857xxxxxxx', 'R. Meeting 2'],
            ['EMP-00160', 'Sinta Wulandari', 'Staff HR',         'HR',         'sinta.w@pge.example', '0819xxxxxxx', 'R. HR'],
        ];
        foreach ($pegawai as [$kode, $nama, $jabatan, $namaDept, $email, $kontak, $room]) {
            Pegawai::firstOrCreate(
                ['kode_pegawai' => $kode],
                [
                    'nama' => $nama,
                    'jabatan' => $jabatan,
                    'id_departemen' => $dept[$namaDept]->id,
                    'email' => $email,
                    'kontak' => $kontak,
                    'room_no' => $room,
                ]
            );
        }
    }
}
