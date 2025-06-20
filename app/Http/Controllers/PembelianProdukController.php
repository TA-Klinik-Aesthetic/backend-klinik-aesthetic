<?php

namespace App\Http\Controllers;

use App\Models\PembelianProduk;
use App\Models\DetailPembelianProduk;
use App\Models\Produk;
use App\Models\Promo;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PembelianProdukController extends Controller
{

    public function storeKasir(Request $request) //untuk website
    {
        $request->validate([
            'id_user'                     => 'required',
            'produk'                      => 'required|array',
            'produk.*.id_produk'          => 'required|exists:tb_produk,id_produk',
            'produk.*.jumlah_produk'      => 'required|integer|min:1',
            'id_promo'                    => 'nullable|exists:tb_promo,id_promo',
            'status_pengambilan_produk'   => 'nullable|string',
        ]);
    
        DB::beginTransaction();
    
        try {
            $harga_total    = 0;
            $detail_produk  = [];
    
            foreach ($request->produk as $item) {
                $produk = Produk::findOrFail($item['id_produk']);
    
                if ($produk->stok_produk < $item['jumlah_produk']) {
                    throw new Exception("Stok produk {$produk->nama_produk} tidak mencukupi");
                }
    
                $subtotal      = $item['jumlah_produk'] * $produk->harga_produk;
                $harga_total  += $subtotal;
    
                $detail_produk[] = [
                    'id_produk'               => $item['id_produk'],
                    'jumlah_produk'           => $item['jumlah_produk'],
                    'harga_penjualan_produk'  => $produk->harga_produk,
                ];
            }
    
            // Hitung potongan jika ada promo
            $nilaiPotonganUntukDisimpan = 0;
            $nilaiPotonganDihitung     = 0;
            if ($request->id_promo) {
                $promo = Promo::findOrFail($request->id_promo);
                if ($promo->jenis_promo !== 'Produk') {
                    throw new Exception("Promo yang digunakan bukan untuk penjualan produk.");
                }
                if (!is_null($promo->minimal_belanja) && $harga_total < $promo->minimal_belanja) {
                    throw new Exception("Promo tidak dapat digunakan karena total belanja kurang dari minimal belanja sebesar Rp" . number_format($promo->minimal_belanja, 0, ',', '.'));
                }
                $nilaiPotonganUntukDisimpan = $promo->potongan_harga;
                $nilaiPotonganDihitung     = $promo->tipe_potongan === 'Diskon'
                    ? ($harga_total * $promo->potongan_harga) / 100
                    : $promo->potongan_harga;
            }
    
            // Pajak 10%
            $subtotalSetelahDiskon = $harga_total - $nilaiPotonganDihitung;
            $pajakHitung          = ($subtotalSetelahDiskon * 10) / 100;
            $hargaAkhir           = $subtotalSetelahDiskon + $pajakHitung;
    
            // Status pengambilan + waktu pengambilan
            $status = $request->input('status_pengambilan_produk', 'Belum diambil');
            $waktu  = $status === 'Sudah diambil' ? now() : null;
    
            // Simpan penjualan
            $pembelian = PembelianProduk::create([
                'id_user'                     => $request->id_user,
                'tanggal_pembelian'           => now(),
                'harga_total'                 => $harga_total,
                'id_promo'                    => $request->id_promo,
                'potongan_harga'              => $nilaiPotonganUntukDisimpan,
                'besaran_pajak'               => $pajakHitung,
                'harga_akhir'                 => $hargaAkhir,
                'status_pengambilan_produk'   => $status,
                'waktu_pengambilan'           => $waktu,
            ]);
    
            // Simpan detail produk
            foreach ($detail_produk as $detail) {
                DetailPembelianProduk::create([
                    'id_penjualan_produk'         => $pembelian->id_penjualan_produk,
                    'id_produk'                   => $detail['id_produk'],
                    'jumlah_produk'               => $detail['jumlah_produk'],
                    'harga_penjualan_produk'      => $detail['harga_penjualan_produk'],
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Penjualan (kasir) berhasil disimpan',
                'data'    => $pembelian,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function store(Request $request) //untuk mobile
    {
        $request->validate([
            'id_user' => 'required',
            'produk' => 'required|array',
            'produk.*.id_produk' => 'required|exists:tb_produk,id_produk',
            'produk.*.jumlah_produk' => 'required|integer|min:1',
            'id_promo' => 'nullable|exists:tb_promo,id_promo',
            'status_pengambilan_produk'    => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $harga_total = 0;
            $detail_produk = [];

            foreach ($request->produk as $item) {
                $produk = Produk::findOrFail($item['id_produk']);

                if ($produk->stok_produk < $item['jumlah_produk']) {
                    throw new Exception("Stok produk {$produk->nama_produk} tidak mencukupi");
                }

                $subtotal = $item['jumlah_produk'] * $produk->harga_produk;
                $harga_total += $subtotal;

                $detail_produk[] = [
                    'id_produk' => $item['id_produk'],
                    'jumlah_produk' => $item['jumlah_produk'],
                    'harga_penjualan_produk' => $produk->harga_produk,
                ];
            }

            $nilaiPotonganUntukDisimpan = 0;
            $nilaiPotonganDihitung     = 0;

            if ($request->id_promo) {
                $promo = Promo::findOrFail($request->id_promo);
                // Validasi jenis promo
                if ($promo->jenis_promo !== 'Produk') {
                    throw new Exception("Promo yang digunakan bukan untuk penjualan produk.");
                }

                // Validasi minimal belanja
                if (!is_null($promo->minimal_belanja) && $harga_total < $promo->minimal_belanja) {
                    throw new Exception("Promo tidak dapat digunakan karena total belanja kurang dari minimal belanja sebesar Rp" . number_format($promo->minimal_belanja, 0, ',', '.'));
                }

                // nilai yang akan disimpan (persis seperti di tabel promo)
                $nilaiPotonganUntukDisimpan = $promo->potongan_harga;

                // nilai potong untuk menghitung harga akhir
                if ($promo->tipe_potongan === 'Diskon') {
                    // potongan_persen = 75 → diskon 75% dari hargaTotal
                    $nilaiPotonganDihitung = ($harga_total * $promo->potongan_harga) / 100;
                } else {
                    // potongan langsung
                    $nilaiPotonganDihitung = $promo->potongan_harga;
                }
            }

            // netto setelah diskon
            $subtotalSetelahDiskon = $harga_total - $nilaiPotonganDihitung;

            // pajak selalu 10 (persen)
            $pajak = 10;
            // hitung nominal pajak untuk perhitungan harga akhir
            $pajakHitung = ($subtotalSetelahDiskon * $pajak) / 100;

            // harga akhir = netto + pajak
            $hargaAkhir = $subtotalSetelahDiskon + $pajakHitung;

            // 1) Ambil status, default 'Belum diambil'
            $status = $request->input('status_pengambilan_produk', 'Belum diambil');

            $pembelian = PembelianProduk::create([
                'id_user' => $request->id_user,
                'tanggal_pembelian' => now(),
                'harga_total' => $harga_total,
                'id_promo' => $request->id_promo,
                'potongan_harga' => $nilaiPotonganUntukDisimpan,
                'besaran_pajak' => $pajakHitung,
                'harga_akhir' => $hargaAkhir,
                // simpan status apa adanya (null jika tidak dikirim)
                'status_pengambilan_produk' => $status,
                // hanya set waktu jika benar-benar di‐request dan "Sudah diambil"
                'waktu_pengambilan'          => $status === 'Sudah diambil' ? now() : null,
            ]);

            foreach ($detail_produk as $detail) {
                DetailPembelianProduk::create([
                    'id_penjualan_produk' => $pembelian->id_penjualan_produk,
                    'id_produk' => $detail['id_produk'],
                    'jumlah_produk' => $detail['jumlah_produk'],
                    'harga_penjualan_produk' => $detail['harga_penjualan_produk'],
                ]);
            }

            // ✨ Baru: Buat record pembayaran untuk penjualan produk
            Pembayaran::create([
                'id_booking_treatment' => null,
                'id_penjualan_produk'  => $pembelian->id_penjualan_produk,
                'uang'                 => null,
                'kembalian'            => null,
                'metode_pembayaran'    => 'Tunai',
                'status_pembayaran'    => 'Belum Dibayar',
                'waktu_pembayaran'     => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil disimpan',
                'data' => $pembelian,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function index()
    {
        $pembelian = PembelianProduk::with('detailPembelian', 'user')->get();
        return response()->json($pembelian);
    }

    public function show($id)
    {
        $pembelian = PembelianProduk::with('detailPembelian.produk', 'user')->find($id);

        if (!$pembelian) {
            return response()->json(['error' => 'Data pembelian tidak ditemukan'], 404);
        }

        return response()->json($pembelian);
    }


    public function getByUser($id_user)
    {
        try {
            // Validate that the user exists
            if (!DB::table('tb_user')->where('id_user', $id_user)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Get all purchases for this user with their details
            $pembelian = PembelianProduk::with(['detailPembelian.produk', 'user', 'promo'])
                ->where('id_user', $id_user)
                ->orderBy('tanggal_pembelian', 'desc')
                ->get();

            if ($pembelian->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada pembelian untuk user ini',
                    'data' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pembelian berhasil diambil',
                'data' => $pembelian
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pembelian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $pembelian = PembelianProduk::find($id);

        if (!$pembelian) {
            return response()->json(['success' => false, 'message' => 'Data penjualan tidak ditemukan'], 404);
        }

        $request->validate([
            'produk' => 'required|array',
            'produk.*.id_produk' => 'required|exists:tb_produk,id_produk',
            'produk.*.jumlah_produk' => 'required|integer|min:1',
            'id_promo' => 'nullable|exists:tb_promo,id_promo',
        ]);

        DB::beginTransaction();

        try {
            $harga_total = 0;
            $detail_produk = [];

            foreach ($pembelian->detailPembelian as $detail) {
                $produk = Produk::findOrFail($detail->id_produk);
                $produk->increment('stok_produk', $detail->jumlah_produk);
            }

            $pembelian->detailPembelian()->delete();

            foreach ($request->produk as $item) {
                $produk = Produk::findOrFail($item['id_produk']);

                if ($produk->stok_produk < $item['jumlah_produk']) {
                    throw new Exception("Stok produk {$produk->nama_produk} tidak mencukupi");
                }

                $subtotal = $item['jumlah_produk'] * $produk->harga_produk;
                $harga_total += $subtotal;

                $detail_produk[] = [
                    'id_produk' => $item['id_produk'],
                    'jumlah_produk' => $item['jumlah_produk'],
                    'harga_penjualan_produk' => $produk->harga_produk,
                ];
            }

            // Simpan nilai potongan & hitung nilai diskon untuk harga akhir
            $nilaiPotonganUntukDisimpan = 0;
            $nilaiPotonganDihitung     = 0;

            // Ambil promo lama (jika ada)
            $lamaPromo = $pembelian->promo; // pastikan relasi `promo()` di-model sudah ada

            if ($request->id_promo) {
                $promo = Promo::findOrFail($request->id_promo);

                // Promo harus jenis Produk
                if ($promo->jenis_promo !== 'Produk') {
                    throw new Exception("Promo yang digunakan bukan untuk penjualan produk.");
                }

                // Promo tidak berlaku jika total belanja kurang dari minimum
                if (!is_null($promo->minimal_belanja) && $harga_total < $promo->minimal_belanja) {
                    throw new Exception("Promo tidak dapat digunakan karena total belanja kurang dari minimal belanja sebesar Rp" . number_format($promo->minimal_belanja, 0, ',', '.'));
                }

                $nilaiPotonganUntukDisimpan = $promo->potongan_harga;
                if ($promo->tipe_potongan === 'Diskon') {
                    $nilaiPotonganDihitung = ($harga_total * $promo->potongan_harga) / 100;
                } else {
                    $nilaiPotonganDihitung = $promo->potongan_harga;
                }
            } elseif ($request->id_promo === null) {
                // User mengosongkan promo → reset potongan
                $nilaiPotonganUntukDisimpan = 0;
                $nilaiPotonganDihitung     = 0;
            } else {
                // Promo tidak diubah → pakai nilai lama
                if ($lamaPromo) {
                    $nilaiPotonganUntukDisimpan = $lamaPromo->potongan_harga;
                    if ($lamaPromo->tipe_potongan === 'Diskon') {
                        $nilaiPotonganDihitung = ($harga_total * $lamaPromo->potongan_harga) / 100;
                    } else {
                        $nilaiPotonganDihitung = $lamaPromo->potongan_harga;
                    }
                }
            }

            // 3) Netto setelah diskon
            $subtotalSetelahDiskon = max(0, $harga_total - $nilaiPotonganDihitung);

            // 4) Hitung nominal pajak 10%
            $pajakHitung = ($subtotalSetelahDiskon * 10) / 100;

            // 5) Harga akhir = netto + pajak
            $hargaAkhir = $subtotalSetelahDiskon + $pajakHitung;

            $pembelian->update([
                'harga_total' => $harga_total,
                'id_promo' => $request->id_promo,
                'potongan_harga' => $nilaiPotonganUntukDisimpan,
                'besaran_pajak' => $pajakHitung,
                'harga_akhir' => $hargaAkhir,
            ]);

            foreach ($detail_produk as $detail) {
                DetailPembelianProduk::create([
                    'id_penjualan_produk' => $pembelian->id_penjualan_produk,
                    'id_produk' => $detail['id_produk'],
                    'jumlah_produk' => $detail['jumlah_produk'],
                    'harga_penjualan_produk' => $detail['harga_penjualan_produk'],
                ]);
            }

            // Muat ulang relasi detail agar response akurat
            $pembelian->load('detailPembelian');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pembelian berhasil diperbarui',
                'data' => $pembelian,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy($id_pembelian_produk)
    {
        $pembelian = PembelianProduk::find($id_pembelian_produk);

        if (!$pembelian) {
            return response()->json(['success' => false, 'message' => 'Data pembelian tidak ditemukan'], 404);
        }

        DB::beginTransaction();

        try {
            // Hapus detail pembelian
            $pembelian->detailPembelian()->delete();

            // Hapus pembayaran produk (langsung via query)
            Pembayaran::where('id_penjualan_produk', $pembelian->id_penjualan_produk)->delete();

            // Hapus data pembelian produk
            $pembelian->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pembelian dan pembayaran berhasil dihapus',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateStatusPengambilan(Request $request, $id)
    {
        $data = $request->validate([
            'status_pengambilan_produk' => 'required|in:Belum diambil,Sudah diambil',
        ]);

        $penjualan = PembelianProduk::findOrFail($id);
        $penjualan->status_pengambilan_produk = $data['status_pengambilan_produk'];
        $penjualan->waktu_pengambilan = $data['status_pengambilan_produk'] === 'Sudah diambil'
            ? now()
            : null;
        $penjualan->save();

        return response()->json([
            'success' => true,
            'message' => 'Status pengambilan produk berhasil diperbarui',
            'data' => $penjualan,
        ]);
    }
}
