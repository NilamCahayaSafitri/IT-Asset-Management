<?php

namespace Database\Seeders;

use App\Enums\StatusAset;
use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\Ruangan;
use Illuminate\Database\Seeder;

class AsetSeeder extends Seeder
{
    public function run(): void
    {
        // kode, nama, kategori, merk, model, serial, memory, hard disk, ruangan, status
        $data = [
            ['ITM-0001', 'Laptop Dell Latitude',   'Laptop',        'Dell',    'Latitude 7420',  'SN-DL7420-2291', '16GB', '512GB SSD', 'R. IT Support', StatusAset::Available->value],
            ['ITM-0002', 'Monitor Dell 24"',       'Monitor',       'Dell',    'P2422H',         'SN-MD24-1187',   null,   null,        'R. Finance',    StatusAset::Deployed->value],
            ['ITM-0004', 'Server Rack HP DL380',   'Server / Rack', 'HP',      'ProLiant DL380', 'SN-HP380-0033',  '64GB', '4x2TB HDD', 'R. Server',     StatusAset::WaitingForDisposal->value],
            ['ITM-0005', 'Laptop Lenovo T14',      'Laptop',        'Lenovo',  'ThinkPad T14',   'SN-LT14-5521',   '8GB',  '256GB SSD', 'R. IT Support', StatusAset::Broken->value],
            ['ITM-0006', 'Monitor LG 22"',         'Monitor',       'LG',      '22MK430H',       'SN-LG22-7710',   null,   null,        'Gudang ICT',    StatusAset::Surplus->value],
            ['ITM-0007', 'Proyektor Epson EB-X41', 'Proyektor',     'Epson',   'EB-X41',         'SN-EP41-0312',   null,   null,        'R. Meeting 2',  StatusAset::Maintenance->value],
            ['ITM-0009', 'Headset Jabra Evolve',   'Headset',       'Jabra',   'Evolve 40',      'SN-JB40-0921',   null,   null,        'Gudang ICT',    StatusAset::Available->value],
        ];

        foreach ($data as [$kode, $nama, $kategori, $merk, $model, $serial, $memory, $hdd, $namaRuangan, $status]) {
            Aset::updateOrCreate(
                ['kode_aset' => $kode],
                [
                    'nama_aset' => $nama,
                    'id_kategori' => KategoriAset::where('nama_kategori', $kategori)->value('id'),
                    'merk' => $merk,
                    'model' => $model,
                    'serial_number' => $serial,
                    'memory' => $memory,
                    'hard_disk' => $hdd,
                    'id_ruangan' => Ruangan::where('nama_ruangan', $namaRuangan)->value('id'),
                    'status' => $status,
                ]
            );
        }
    }
}
