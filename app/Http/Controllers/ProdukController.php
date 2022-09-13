<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $id_user = Auth::user()->id;
        $id_lokasi = $request->session()->get('id_lokasi');
        $id_menu = DB::table('tb_permission')->select('id_menu')->where('id_user', $id_user)
            ->where('id_menu', 12)->first();
        if (empty($id_menu)) {
            return back();
        } else {

            $data = [
                'title' => 'Produk Majo',
                'produk' => DB::table('tb_produk as a')
                    ->join('tb_satuan_majo as b', 'a.id_satuan', '=', 'b.id_satuan')
                    ->join('tb_kategori_majo as c', 'a.id_kategori', '=', 'c.id_kategori')
                    ->where('a.id_lokasi', $id_lokasi)
                    ->orderBy('a.id_produk', 'DESC')
                    ->get(),
                'kategori' => DB::table('tb_kategori_majo')->get(),
                'satuan' => DB::table('tb_satuan_majo')->get(),

                'logout' => $request->session()->get('logout'),
            ];
            return view("produk.index", $data);
        }
    }

    public function tbh_produk_majo(Request $request)
    {

        $data = [
            'nm_produk' => $request->nm_produk,
            'id_kategori' => $request->id_kategori,
            'id_satuan' => $request->id_satuan,
            'stok' => $request->stok,
            'harga_modal' => $request->harga_modal,
            'harga' => $request->harga,
            'komisi' => $request->komisi,
            'id_lokasi' =>  $request->session()->get('id_lokasi')
        ];
        $sku = Produk::create($data);

        Produk::where('id_produk', $sku->id)->update(['sku' => 'TS' . $sku->id]);

        return redirect()->route('produk')->with('sukses', 'Berhasil tambah Produk');
    }

    public function delete(Request $r)
    {
        Produk::where('id_produk', $r->id_produk)->delete();
        return redirect()->route('produk')->with('error', 'Berhasil hapus produk');
    }

    public function get_edit_majo(Request $r)
    {
        $produk = DB::table('tb_produk')->where('id_produk', $r->id_produk)->first();
        $data = [
            'produk' => $produk,
            'kategori' => DB::table('tb_kategori_majo')->get(),
            'satuan' => DB::table('tb_satuan_majo')->get(),
        ];
        return view("produk.edit", $data);
    }

    public function edit(Request $r)
    {
        $data = [
            'nm_produk' => $r->nm_produk,
            'id_kategori' => $r->id_kategori,
            'id_satuan' => $r->id_satuan,
            'stok' => $r->stok,
            'harga' => $r->harga,
            'harga_modal' => $r->harga_modal,
            'komisi' => $r->komisi,
        ];
        Produk::where('id_produk', $r->id_produk)->update($data);
        return redirect()->route('produk')->with('sukses', 'Berhasil ubah produk');
    }
}
