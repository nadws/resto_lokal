<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StokmasukController extends Controller
{

    public function index(Request $r)
    {
        $id_user = Auth::user()->id;
        $id_lokasi = $r->session()->get('id_lokasi');
        $id_menu = DB::table('tb_permission')->select('id_menu')->where('id_user', $id_user)
            ->where('id_menu', 12)->first();
        if (empty($id_menu)) {
            return back();
        } else {
            $hari  = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
            if (empty($r->tgl1)) {
                $tgl1   = date('Y-m-') . '01';
                $tgl2   = date('Y-m-') . $hari;
            } else {
                $tgl1   = $r->tgl1;
                $tgl2   = $r->tgl2;
            }
            $data = [
                'title' => 'Produk Majo',
                'title' => 'Stok Produk',
                'stokProduk' => DB::select("SELECT a.*, SUM(a.debit) as debit, SUM(a.kredit) as kredit FROM tb_stok_produk as a
                WHERE a.tgl BETWEEN '$tgl1' AND '$tgl2' AND a.id_lokasi = '$id_lokasi' and a.ket = 'stok masuk'
                GROUP BY a.kode_stok_produk
                ORDER BY a.id_stok_produk DESC"),

                'logout' => $r->session()->get('logout'),
            ];
            return view('stokmajo.index', $data);
        }
    }

    public function buatStokProduk(Request $r)
    {
        $id_user = Auth::user()->id;
        $id_lokasi = $r->session()->get('id_lokasi');
        $id_menu = DB::table('tb_permission')->select('id_menu')->where('id_user', $id_user)
            ->where('id_menu', 12)->first();
        if (empty($id_menu)) {
            return back();
        } else {
            $id_lokasi = $r->session()->get('id_lokasi');
            $data = [
                'title'  => "Create Stok Masuk",
                'kategori' => DB::table('tb_kategori_majo')->get(),
                'produk' => DB::select("SELECT a.id_produk, a.komisi,  a.nm_produk, a.sku, a.harga, b.satuan , c.nm_kategori, a.id_lokasi, d.debit, d.kredit,e.kredit_penjualan
                FROM tb_produk AS a
                LEFT JOIN tb_satuan_majo AS b ON b.id_satuan = a.id_satuan
                LEFT JOIN tb_kategori_majo AS c ON c.id_kategori = a.id_kategori
                
                LEFT JOIN (
                SELECT d.id_produk, SUM(d.debit) AS debit, SUM(d.kredit) AS kredit
                FROM tb_stok_produk AS d 
                GROUP BY d.id_produk
                ) AS d ON d.id_produk = a.id_produk

                LEFT JOIN (
                SELECT e.id_produk , SUM(e.jumlah) AS kredit_penjualan
                FROM tb_pembelian AS e 
                GROUP BY e.id_produk
                )AS e ON e.id_produk = a.id_produk
                
                WHERE a.id_lokasi = '$id_lokasi'"),
                'logout' => $r->session()->get('logout'),
            ];
            return view('stokmajo.buat', $data);
        }
    }
    public function inputProdukMasuk(Request $r)
    {
        $id_produk = $r->id_stok_produk;
        $kode_stok_produk = 'INV' . date('ymd') . strtoupper(Str::random(3));

        $id_lokasi = $r->session()->get('id_lokasi');
        foreach ($id_produk as $id) {
            $get_produk = Produk::where('id_produk', $id)->first();
            $data = [
                'kode_stok_produk' => $kode_stok_produk,
                'id_produk' => $get_produk->id_produk,
                'stok_program' => $get_produk->stok,
                'ttl_stok' => $get_produk->stok,
                'debit' => 0,
                'kredit' => 0,
                'harga' => $get_produk->harga,
                'admin' => Auth::user()->nama,
                'jenis' => 'Masuk',
                'status' => 'Draft',
                'tgl_input' => date('Y-m-d H:i:s'),
                'tgl' => date('Y-m-d'),
                'ket' => 'stok masuk',
                'id_lokasi' => $id_lokasi,
            ];
            DB::table('tb_stok_produk')->insert($data);
        }
        return redirect()->route('detailStokProduk', ['kode' => $kode_stok_produk]);
    }
}
