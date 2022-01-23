<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductInCategory;
use App\Models\Supplier;
use App\Services\FileService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * @var ProductService
     */
    private $productService;
    private $page;
    private $fileService;

    /**
     * ProductController constructor.
     * @param ProductService $productService
     * @param FileService $fileService
     */
    public function __construct(
        ProductService $productService,
        FileService $fileService
    )
    {
        $this->productService = $productService;
        $this->fileService = $fileService;
        $this->page['title'] = "Ürün Yönetimi";
        $this->page['sub_title'] = "Ürün Yönetimi";
    }

    public function index()
    {
        $products = $this->productService->getProducts();
        return view('admin.product.index', compact('products'))->with('page', $this->page);
    }

    public function create()
    {
        $this->page['sub_title'] = "Ürün Ekle";
        $categories = $this->productService->getProductCategories();
        $suppliersList = collect(['0' => 'Tedarikçi Seçiniz'] + Supplier::where('status',1)->pluck('name', 'id')->all());
        $brandsList = collect(['0' => 'Marka Seçiniz'] + Brand::where('status',1)->pluck('name', 'id')->all());
        return view('admin.product.create', compact('categories','brandsList','suppliersList'))->with('page', $this->page);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $product = $this->productService->saveProduct($data);
        if ($product){
            foreach ($data['product_in_categories'] as $product_in_category){
                $this->productService->saveProductInCategory($product->id, $product_in_category);
            }
            foreach ($data['images'] as $key => $image){
                $path = "uploads/product/images/".$product->id;
                $imageName = $this->fileService->saveFile($image, $path);
                $this->productService->saveProductImage($product->id, $imageName);
            }
        }
        session_success("Ürün eklendi, lütfen ürüne ait varyasyonları ekleyin");
        return redirect()->route('admin.product.variation-type.index', $product);
    }

    public function edit(Product $product)
    {
        $this->page['sub_title'] = "Ürün Düzenle";
        $categories = $this->productService->getProductCategories();
        $getProductInCategories = $product->inCategories()->get();
        foreach ($categories as $category){
            if ($getProductInCategories->where('category_slug', $category->slug)->first()){
                $category->is_checked = true;
            }else{
                $category->is_checked = false;
            }
        }
        $brandsList = collect(['0' => 'Marka Seçiniz'] + Brand::where('status',1)->pluck('name', 'id')->all());
        $suppliersList = collect(['0' => 'Tedarikçi Seçiniz'] + Supplier::where('status',1)->pluck('name', 'id')->all());
        return view('admin.product.edit', compact('categories', 'product','brandsList','suppliersList', 'getProductInCategories'))->with('page', $this->page);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->all();
        $this->productService->updateProduct($data,$product);
        ProductInCategory::whereProductId($product->id)->delete();
        foreach ($data['product_in_categories'] as $product_in_category){
            $this->productService->saveProductInCategory($product->id, $product_in_category);
        }
        if (isset($data['images'])){
            foreach ($data['images'] as $key => $image){
                $path = "uploads/product/images/".$product->id;
                $imageName = $this->fileService->saveFile($image, $path);
                $this->productService->saveProductImage($product->id, $imageName);
            }
        }
        session_success("Ürün güncellendi");
        return redirect()->route('admin.product.index');
    }

    public function productImageDelete(Request $request)
    {
        $productImage = ProductImage::find($request->product_image_id);
        unlink('uploads/product/images/'. $productImage->product_id.'/'. $productImage->name);
        ProductImage::find($request->product_image_id)->delete();
        $responseData['status'] = 1;
        $responseData['message'] = "Ürün görseli silindi";
        return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy(Product $product)
    {
        $productImages = ProductImage::whereProductId($product->id)->get();
        $productId = $this->productService->getProductById($product->id);
        foreach ($productImages as $image){
            @unlink('uploads/product/images/'. $image->product_id.'/'. $image->name);
        }
        @unlink('uploads/videos/'. $productId->preview_video);
        ProductImage::whereProductId($product->product_image_id)->delete();
        ProductInCategory::whereProductId($product->id)->delete();
        Product::find($product->id)->delete();
        session_success("Ürün silindi");
        return redirect()->route('admin.product.index');
    }

    public function replicate(Product $product)
    {
        $cloneProduct = $product->replicate();
        $cloneProduct->save();
        foreach ($product->images()->get() as $image){
            $cloneProductImage = $image->replicate();
            $cloneProductImage->product_id = $cloneProduct->id;
            $cloneProductImage->save();
            if (!file_exists('uploads/product/images/'. $cloneProductImage->product_id)){
                mkdir('uploads/product/images/'. $cloneProductImage->product_id);
            }
            File::copy('uploads/product/images/'. $image->product_id.'/'. $image->name, 'uploads/product/images/'. $cloneProductImage->product_id.'/'. $cloneProductImage->name);
        }
        session_success("Ürün kopyalandı");
        return redirect()->route('admin.product.index');
    }

    public function changeHomepageVisible(Request $request)
    {
        $product = $this->productService->getProductById($request->product_id);
        $product->homepage_visible = $product->homepage_visible == 0 ? 1 : 0;
        $product->save();
        $responseData['status'] = 1;
        return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function productExport()
    {
        return Excel::download(new ProductExport(), 'urunler.xlsx');
    }

    public function xmlImport()
    {
        $url = "https://www.tablocumuz.com/XMLExport/1D439CECC2";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents

        $data = curl_exec($ch); // execute curl request
        curl_close($ch);

        $xml = simplexml_load_string($data);
        $json = json_encode($xml);

        $array = json_decode($json, TRUE);
        $products = $array['Urunler']['Urun'];
        foreach ($products as $product) {
            $newProduct = Product::create([
                'name' => $product['Baslik'],
                'slug' => Str::slug($product['Baslik']),
                'description' => $product['Aciklama'],
                'price' => $product['Fiyat'],
                'discount_price' => $product['Indirimli_Fiyati'],
                'status' => 1,
            ]);

            $productImages = $product['Resimler'];
            foreach ($productImages['Resim'] as $productImage) {
                if (! File::exists(public_path()."uploads/product/images/".$newProduct->id)) {
                    File::makeDirectory(public_path()."uploads/product/images/".$newProduct->id, 0777, true);
                }
                ProductImage::create([
                    'product_id' => $newProduct->id,
                    $file = file_get_contents($productImage),
                    $icerik = "uploads/product/images/".$newProduct->id,
                    File::put($icerik, $file),
                    'name' => $productImage,
                    'default' => 0,
                ]);
            }
        }
        session_success("Ürünler Import Edildi");
        return redirect()->route('admin.product.index');
    }


    public function importForm()
    {
        return view('admin.product.import')->with('page', $this->page);

    }

    public function excelImport(Request $request){
        if ($request->hasFile('excel')){
            if (! File::exists(public_path()."/uploads/excel/")) {
                File::makeDirectory(public_path()."/uploads/excel/", 0755, true);
            }
            $file = $request->file('excel');
            $fileName = date("YmdHis").".".rand(1000,9999).'.'.$file->getClientOriginalExtension();
            $path = public_path('/uploads/excel/');
            $file->move($path,$fileName);
        }

        $excel = public_path('/uploads/excel/'.$fileName);
        $data = array();
        $data = Excel::toArray(new ProductsImport(), $excel);

        $temp = 0;
        foreach($data[0] as $product){
            if($temp!=0){
                Product::create([
                    'name' => $product[1],
                    'slug' => Str::slug($product[1]),
                    'description' => $product[2],
                    'price' => $product[3],
                    'discount_price' => $product[4],
                    'status' => 1,
                    'stock' => $product[5],
                    'brand_id' => 0,
                    'supplier_id' => 0,
                    'is_visible_brand' => 0,
                    'is_visible_supplier' => 0,
                    'delivery_time' => 0,
                    'free_cargo_status' => 0,
                    'preview_video' => null,
                    'video_visible' => 0,
                    'detail_video_title' => null,
                    'detail_video_link' => null,
                    'order_count' => 0,
                    'homepage_visible' => 0,
                    'homepage_visible_row' => 0,
                ]);
            }

            $temp++;
        }
        session_success('Ürünler İçe Aktarıldı');
        return redirect()->route('admin.product.index');
    }
}
