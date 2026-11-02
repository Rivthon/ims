<?php
namespace App\Http\Controllers;
use App\Models\{Asset,Category,Location};
use Illuminate\Http\Request;
class AssetController extends Controller {
 public function index(Request $r){$assets=Asset::with(['category','location'])->when($r->q,fn($q,$v)=>$q->where(fn($x)=>$x->where('name','like',"%$v%")->orWhere('code','like',"%$v%")))->latest()->paginate(15)->withQueryString();return view('assets.index',compact('assets'));}
 public function create(){return view('assets.form',['asset'=>new Asset,'categories'=>Category::where('type','asset')->get(),'locations'=>Location::orderBy('name')->get()]);}
 public function store(Request $r){$asset=Asset::create($this->validated($r));return redirect()->route('assets.index')->with('success',"Aset {$asset->code} ditambahkan.");}
 public function edit(Asset $asset){return view('assets.form',['asset'=>$asset,'categories'=>Category::where('type','asset')->get(),'locations'=>Location::orderBy('name')->get()]);}
 public function update(Request $r,Asset $asset){$asset->update($this->validated($r,$asset));return redirect()->route('assets.index')->with('success','Aset diperbarui.');}
 private function validated(Request $r,?Asset $asset=null){$ignoreId=$asset ? $asset->id : 'NULL';return $r->validate(['code'=>'required|max:50|unique:assets,code,'.$ignoreId,'name'=>'required|max:255','category_id'=>'nullable|exists:categories,id','location_id'=>'nullable|exists:locations,id','serial_number'=>'nullable|max:100','brand'=>'nullable|max:100','model'=>'nullable|max:100','pic'=>'nullable|max:150','purchase_date'=>'nullable|date','warranty_until'=>'nullable|date','purchase_price'=>'nullable|numeric|min:0','condition'=>'required|in:new,good,needs_repair,damaged,disposed','status'=>'required|in:active,maintenance,loaned,disposed','notes'=>'nullable']);}
}
