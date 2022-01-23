<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductInCategory;
use App\Models\ProductOption;
use App\Models\ProductReview;
use App\Models\ProductTab;
use Faker\Provider\Image;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class ProductRepository
 * @package App\Repositories
 */
class ProductRepository
{
    public $productInCategory;
    public $productTab;
    public $productReview;

    /**
     * ProductRepository constructor.
     * @param Product $product
     * @param ProductCategory $productCategory
     * @param ProductInCategory $productInCategory
     * @param ProductTab $productTab
     * @param ProductReview $productReview
     */
    public function __construct(Product $product, ProductCategory $productCategory, ProductInCategory $productInCategory, ProductTab $productTab, ProductReview $productReview)
    {
        $this->product = $product;
        $this->productCategory = $productCategory;
        $this->productInCategory = $productInCategory;
        $this->productTab = $productTab;
        $this->productReview = $productReview;
    }

    /**
     * @return LengthAwarePaginator
     */
    public function getProducts(): LengthAwarePaginator
    {
        return $this->product->orderBy('created_at', 'DESC')->paginate(10);
    }

    public function getProductsWithCategoryForFront($categorySlug)
    {
        return $this->productInCategory->whereCategorySlug($categorySlug)->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @return mixed
     */
    public function getProductsForFront()
    {
        return $this->product->orderBy('created_at', 'DESC')->take(8)->get();
    }

    /**
     * @return mixed
     */
    public function getProductsInDetailForFront()
    {
        return $this->product->orderBy('created_at', 'DESC')->get()->shuffle()->all();
    }

    /**
     * @return mixed
     */
    public function getTrendProductsForFront()
    {
        return $this->product->orderBy('order_count', 'DESC')->take(8)->get();
    }

    /**
     * @return mixed
     */
    public function getMostOrderedProducts()
    {
        return $this->product->orderBy('order_count', 'DESC')->take(10)->get();
    }

    public function getProductReviews($id)
    {
        return $this->productReview->where('product_id', $id)->orderBy('created_at', 'DESC')->paginate(10);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getProductTabs($id)
    {
        return $this->productTab->where('product_id', $id)->orderBy('id', 'ASC')->get();
    }

    /**
     * @param $id
     * @return Builder|Model|object|null
     */
    public function getProductWithTabById($id)
    {
        return $this->product->with(['tabs'])->where(['id' => $id])->first();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getProductTabById($id)
    {
        return $this->productTab->find($id);
    }

    /**
     * @return mixed
     */
    public function getAllProductCount()
    {
        return $this->product->get()->count();
    }

//    /**
//     * @return LengthAwarePaginator
//     */
//    public function getProductsForFront(): LengthAwarePaginator
//    {
//        $pagination = $this->settingService->getGeneralSettings();
//
//        return $this->product->orderBy('id', 'DESC')->paginate($pagination['products_pagination']);
//    }

    /**
     * @return LengthAwarePaginator
     */
    public function getProductsForSlider(): LengthAwarePaginator
    {
        $pagination = $this->settingService->getGeneralSettings();

        return $this->product->orderBy('id', 'DESC')->where('is_home', 1)->paginate($pagination['product_amount']);
    }

    /**
     * @param $category_id
     * @return LengthAwarePaginator
     */
    public function getProductsForFrontByCategory($category_id): LengthAwarePaginator
    {
        $pagination = $this->settingService->getGeneralSettings();

        return $this->product->where('category_id', $category_id)->orderBy('id', 'DESC')->paginate($pagination['products_pagination']);
    }

    /**
     * @return mixed
     */
    public function getProductCategories()
    {
        return $this->productCategory->orderBy('created_at', 'DESC')->get();
    }

    public function getProductCategoriesForFront()
    {
        return $this->productCategory->orderBy('created_at', 'DESC')->where('parent_category_id',0)->get();
    }

    /**
     * @param $parent_id
     * @return LengthAwarePaginator
     */
    public function getProductSubCategories($parent_id): LengthAwarePaginator
    {
        return $this->productCategory->where('parent_id', $parent_id)->withCount('subCategory')->orderBy('position', 'ASC')->paginate(10);
    }

    public function getProductCategoriesListForCategories()
    {
//        return $this->productCategoryTranslation->where('language', App::getLocale())->orderBy('name', 'ASC')->get()->pluck('name', 'product_category_id');
        $categories = ProductCategory::all();

        $categoriesList = [];

        foreach ($categories as $category) {
            $categoriesList[$category->id] = $category->getFullName();
        }

        asort($categoriesList);
        $categoriesList = collect(['0' => trans('product-category.edit.main-category')] + $categoriesList);

        return $categoriesList;
    }

    public function getProductCategoriesList()
    {
        return $this->productCategory->pluck('name', 'id');
    }

    public function getProductCategoriesWithSubCategories()
    {
        $categories = $this->productCategory->where('parent_id', 0)->with(['subCategory'])->orderBy('position', 'ASC')->get();

        return $categories;
    }

    public function getProductImages($productId): LengthAwarePaginator
    {
        return $this->productImage->where('product_id', $productId)->paginate(10);
    }

    /**
     * @param $productId
     * @return Product|null
     */
    public function getProductById($productId): ?Product
    {
        return $this->product->find($productId);
    }

    /**
     * @param $slug
     * @return Product|null
     */
    public function getProductBySlug($slug): ?Product
    {
        $productTemp = $this->productTranslation->where('language', App::getLocale())->where('slug', $slug)->firstOrFail();
        return $this->product->find($productTemp->product_id);
    }

    /**
     * @param $id
     * @return ProductCategory|null
     */
    public function getCategoryById($id): ?ProductCategory
    {
        return $this->productCategory->find($id);
    }

    /**
     * @param $slug
     * @return ProductCategory|null
     */
    public function getCategoryBySlug($slug): ?ProductCategory
    {
        $productCategoryId = $this->productCategoryTranslation->where('slug', $slug)->first()->product_category_id;

        $translation = $this->productCategoryTranslation->where('language', App::getLocale())->where('product_category_id', $productCategoryId)->first();

        return $this->productCategory->find($translation->product_category_id);
    }

    /**
     * @param $data
     * @return Product
     */
    public function createProduct($data): Product
    {

        if (isset($data['special_price_status'])) {
            $special_price_status = 1;
        } else {
            $special_price_status = 0;
        }

        $product = $this->product->create([
            'category_id' => $data['category_id'],
            'stock' => $data['stock'],
            'price' => $data['price'],
            'special_price' => $data['special_price'],
            'stock_show' => $data['stock_show'],
            'special_price_status' => $special_price_status,
        ]);

        if ($product) {
            $product->translation()->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'code_two' => $data['code_two'],
                'box_size' => $data['box_size'],
                'description' => $data['description'],
                'cargo_description' => $data['cargo_description'],
                'stock_description' => $data['stock_description'],
                'meta_title' => $data['meta_title'],
                'meta_keywords' => $data['meta_keywords'],
                'meta_description' => $data['meta_description'],
            ]);

            $products = $this->productTranslation->where('slug', null)->where('name', '<>', '')->get();


            foreach ($products as $raw) {
                $productTemp = $this->product->find($raw->product_id);

                $categoryStr = $this->slugControlParentCategory($productTemp->productCategory, '', $raw->language);
                $productStr = $raw->code . '-' . $raw->code_two . '-' . $raw->name;

                $raw->slug = Str::slug($productStr) . '-' . $raw->id;
                $raw->category_slug = Str::slug($categoryStr);
                $raw->save();

                $slugCheck = $this->productTranslation->where('language', $raw->language)->where('slug', Str::slug($productStr))->where('id', '<>', $raw->id)->first();

                if (!$slugCheck) {
                    $raw->slug = Str::slug($productStr);
                    $raw->save();
                }
            }

            if (isset($data['market_place'])) {
                foreach ($data['market_place'] as $key => $value) {
                    $this->productMarketPlace->create([
                        'product_id' => $product->id,
                        'market_place_id' => $value
                    ]);
                }
            }

        }


        return $product;

    }

    /**
     * @param $data
     * @return ProductImage
     */
    public function createProductImage($data): ProductImage
    {
        if (!empty($data['cover'])) {
            $cover = 1;
        } else {
            $cover = 0;
        }

        if (!File::exists(public_path() . "/uploads/product-image/")) {
            File::makeDirectory(public_path() . "/uploads/product-image/", 0755, true);
        }

        $file = $data['image'];
        $imageName = $data['product_id'] . '-' . date("Ymd") . '-' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
        $path = public_path('/uploads/product-image/' . $imageName);
        Image::make($file)->save($path, 65);

        return $this->productImage->create([
            'product_id' => $data['product_id'],
            'cover' => $cover,
            'image' => $imageName,
        ]);

    }

    public function saveProduct(array $data)
    {
        if (!File::exists(public_path() . "/uploads/videos/")) {
            File::makeDirectory(public_path() . "/uploads/videos/", '0755', true);
        }

        if (!empty($data['preview_video'])) {
            $file = $data['preview_video'];
            $path = public_path('/uploads/videos/');
            $fileName = date("Ymd") . '-' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move($path, $fileName);


            $product = Product::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'discount_price' => $data['discount_price'],
                'status' => $data['status'],
                'stock' => $data['stock'],
                'brand_id' => $data['brand_id'],
                'supplier_id' => $data['supplier_id'],
                'is_visible_brand' => $data['is_visible_brand'],
                'is_visible_supplier' => $data['is_visible_supplier'],
                'delivery_time' => $data['delivery_time'],
                'free_cargo_status' => $data['free_cargo_status'],
                'detail_video_title' => $data['detail_video_title'],
                'detail_video_link' => $data['detail_video_link'],
                'homepage_visible_row' => $data['homepage_visible_row'],
                'video_visible' => $data['video_visible'],
                'preview_video' => $fileName
            ]);

            return $product;
        } else {
            $product = Product::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'discount_price' => $data['discount_price'],
                'status' => $data['status'],
                'stock' => $data['stock'],
                'brand_id' => $data['brand_id'],
                'supplier_id' => $data['supplier_id'],
                'is_visible_brand' => $data['is_visible_brand'],
                'is_visible_supplier' => $data['is_visible_supplier'],
                'delivery_time' => $data['delivery_time'],
                'free_cargo_status' => $data['free_cargo_status'],
                'detail_video_title' => $data['detail_video_title'],
                'detail_video_link' => $data['detail_video_link'],
                'homepage_visible_row' => $data['homepage_visible_row'],
                'video_visible' => $data['video_visible']
            ]);
        }
        return $product;

    }

    public function updateProduct($data, $product)
    {
        if (!empty($data['preview_video'])) {
            @unlink(public_path('/uploads/videos/' . $product->preview_video));
            $file = $data['preview_video'];
            $path = public_path('/uploads/videos/');
            $fileName = date("Ymd") . '-' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move($path, $fileName);

            $product->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'discount_price' => $data['discount_price'],
                'status' => $data['status'],
                'stock' => $data['stock'],
                'brand_id' => $data['brand_id'],
                'supplier_id' => $data['supplier_id'],
                'is_visible_brand' => $data['is_visible_brand'],
                'is_visible_supplier' => $data['is_visible_supplier'],
                'delivery_time' => $data['delivery_time'],
                'free_cargo_status' => $data['free_cargo_status'],
                'detail_video_title' => $data['detail_video_title'],
                'detail_video_link' => $data['detail_video_link'],
                'preview_video' => $fileName,
                'homepage_visible_row' => $data['homepage_visible_row'],
                'video_visible' => $data['video_visible'],
            ]);

            return $product;
        } else {

            $product->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'discount_price' => $data['discount_price'],
                'status' => $data['status'],
                'stock' => $data['stock'],
                'brand_id' => $data['brand_id'],
                'supplier_id' => $data['supplier_id'],
                'is_visible_brand' => $data['is_visible_brand'],
                'is_visible_supplier' => $data['is_visible_supplier'],
                'delivery_time' => $data['delivery_time'],
                'free_cargo_status' => $data['free_cargo_status'],
                'detail_video_title' => $data['detail_video_title'],
                'detail_video_link' => $data['detail_video_link'],
                'homepage_visible_row' => $data['homepage_visible_row'],
                'video_visible' => $data['video_visible'],
            ]);
        }

        return $product;
    }

    public function saveProductImage($productId, $imageName)
    {
        ProductImage::create([
            'product_id' => $productId,
            'name' => $imageName,
            'default' => 0,
        ]);

        return true;
    }

    public function saveOption($productId, $data, $key)
    {
        $option = ProductOption::create([
            'product_id' => $productId,
            'name' => $data['option_name'][$key],
            'stock_status' => $data['option_stock_status'][$key],
            'price' => $data['option_price'][$key],
            'stock' => $data['option_stock'][$key],
            'image' => $data['image_name'],
        ]);

        return $option;
    }

    public function saveProductCategory(array $data)
    {
        $productCategory = ProductCategory::create([
            'top_menu' => $data['top_menu'],
            'parent_category_id' => $data['parent_category_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'meta_keywords' => $data['meta_keywords'],
            'image' => "-",
            'status' => $data['status'],
        ]);

        return $productCategory;
    }

    public function updateProductCategory($data, $id)
    {
        $productCategory = $this->getProductCategoryById($id);

        $productCategory->update([
            'top_menu' => $data['top_menu'],
            'parent_category_id' => $data['parent_category_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'meta_keywords' => $data['meta_keywords'],
            'status' => $data['status'],
        ]);

        return $productCategory;
    }

    public function getProductCategoryById($productCategory)
    {
        $productCategory = ProductCategory::find($productCategory);
        return $productCategory;
    }

    public function saveProductInCategory($productId, $productInCategory)
    {
        $productInCategory = $this->productInCategory->create([
            'product_id' => $productId,
            'category_slug' => $productInCategory,
        ]);

        return $productInCategory;
    }

    public function createProductTab($data)
    {
        return $this->productTab->create([
            'product_id' => $data['product_id'],
            'title' => $data['title'],
            'content' => $data['content'],
        ]);
    }

    public function updateProductTab($data, $id)
    {
        $productTab = $this->getProductTabById($id);

        $productTab->update([
            'title' => $data['title'],
            'content' => $data['content'],
        ]);

        return $productTab;
    }
}
