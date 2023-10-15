<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\GhiHD;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;

class BillsController extends Controller
{
    private $list;
    private $values;
    public function __construct(){
        $this->values = new GhiHD();
        $this->list = new Bill();
    }

    public function index(){
        $listghd = $this->values->listghihd();
        // $listhd=$this->list->listhoadon();
        $key = request()->key;
        $listhd = Bill::search($key)->get();
        // dd($listhd);
        $prices = DB::table('prices')->select('medicine_id', 'Gia', 'ngay_id')->get();
        return view('checks.bills', compact('listghd', 'listhd', 'prices'));
    }

    public function indexpay($id){
        $ghd = DB::table('ghihds')
        ->join('medicines', 'ghihds.medicine_id', '=', 'medicines.ThuocID')
        ->join('bills', 'ghihds.bill_id', '=', 'bills.HDID')
        ->select('ghihds.bill_id', 'ghihds.medicine_id','medicines.Tenthuoc', 'Soluong')
        ->where('bill_id', $id)
        ->get();

        $bill = DB::table('bills')
        ->join('prescriptions', 'bills.prescription_id', '=', 'prescriptions.ToaID')
        ->join('staffs', 'bills.staff_id', '=', 'staffs.NVID')
        ->join('customers', 'bills.customer_id', '=', 'customers.KHID')
        ->select('HDID', 'Tongtien', 'DoituongSD', 'bills.created_at', 'staffs.TenNV', 'prescription_id', 'customers.TenKH', 'customers.SDT')
        ->where('HDID', $id)
        ->first();
        // dd($bill, $ghd);
        $prices = DB::table('prices')->select('medicine_id', 'Gia', 'ngay_id')->get();

        return view('checks.printbill', compact('bill', 'ghd', 'prices'));
    }
    public function updatehd(Request $request, $id)
    {
        $drs = DB::table('bills')
        ->where('HDID', '=', $id)
        ->update([
            'Tongtien' => $request->input('sum')
        ]);

         // Debugging
    $currentSoluong = DB::table('ghipns')
        ->where('medicine_id', $request->input('medicine_id'))
        ->value('Soluong');

    // Debugging
    // dd('Before Update', $request->input('sl'), $currentSoluong);
    // Enable query log
    DB::enableQueryLog();
    // Build and execute the update query
    $ud_soluong = DB::table('ghipns')
        ->join('phieunhaps','ghipns.phieunhap_id','=','phieunhaps.PNID')
        ->where('medicine_id', $request->input('medicine_id'))
        ->update([
            'Soluong' => DB::raw('Soluong - ' . (int)$request->input('sl'))
        ]);
    // Get the executed query
    $query = DB::getQueryLog();
    // Print or log the executed query
    dd($query);
    // Debugging
    $updatedSoluong = DB::table('ghipns')
        ->where('medicine_id', $request->input('medicine_id'))
        ->value('Soluong');
    // Debugging
    // dd('After Update', $updatedSoluong, $ud_soluong);

    return redirect('/bills');
    }

}
