<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_kategori')->insert([
        ['nama_kategori' => 'Skincare',
            'created_at' => now(),
            'updated_at' => now()
        ],
        ['nama_kategori' => 'Facewash',                
        'created_at' => now(),
        'updated_at' => now()
        ],
        ['nama_kategori' => 'Haircare',
        'created_at' => now(),
        'updated_at' => now()
        ],
        ['nama_kategori' => 'Bodycare',
        'created_at' => now(),
        'updated_at' => now()
        ],
        ['nama_kategori' => 'Acnecare',
        'created_at' => now(),
        'updated_at' => now()
        ]
        ]);
    }
}
