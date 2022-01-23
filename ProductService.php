<?php

namespace App\Services;


use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class ProductService
 * @package App\Services
 */
class ProductService
{
    protected $productRepository;
    protected $product;

    /**
     * ProductService constructor.
     * @param ProductRepository $productRepository
     * @param Product $product
     */
    public function __construct(ProductRepository $productRepository, Product $product)
    {
        $this->productRepository = $productRepository;
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->productRepository->getProducts();
    }

    public function getProductsWithCategoryForFront($categorySlug)
    {
        return $this->productRepository->getProductsWithCategoryForFront($categorySlug);
    }

    /**
     * @return mixed
     */
    public function getProductsForFront()
    {
        return $this->productRepository->getProductsForFront();
    }

    /**
     * @return mixed
     */
    public function getProductsInDetailForFront()
    {
        return $this->productRepository->getProductsInDetailForFront();
    }
    /**
     * @return mixed
     */
    public function getTrendProductsForFront()
    {
        return $this->productRepository->getTrendProductsForFront();
    }

    /**
     * @return mixed
     */
    public function getMostOrderedProducts()
    {
        return $this->productRepository->getMostOrderedProducts();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getProductReviews($id)
    {
        return $this->productRepository->getProductReviews($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getProductTabs($id)
    {
        return $this->productRepository->getProductTabs($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getProductTabById($id)
    {
        return $this->productRepository->getProductTabById($id);
    }

    /**
     * @param $id
     * @return Builder|Model|object|null
     */
    public function getProductWithTabById($id)
    {
        return $this->productRepository->getProductWithTabById($id);
    }

    /**
     * @return mixed
     */
    public function getAllProductCount()
    {
        return $this->productRepository->getAllProductCount();
    }

//    /**
//     * @return LengthAwarePaginator
//     */
//    public function getProductsForFront(): LengthAwarePaginator
//    {
//        return $this->productRepository->getProductsForFront();
//    }

    /**
     * @return LengthAwarePaginator
     */
    public function getProductsForSlider(): LengthAwarePaginator
    {
        return $this->productRepository->getProductsForSlider();
    }

    /**
     * @param $category_id
     * @return LengthAwarePaginator
     */
    public function getProductsForFrontByCategory($category_id): LengthAwarePaginator
    {
        return $this->productRepository->getProductsForFrontByCategory($category_id);
    }

    /**
     * @return mixed
     */
    public function getProductCategories()
    {
        return $this->productRepository->getProductCategories();
    }

    /**
     * @return mixed
     */
    public function getProductCategoriesForFront()
    {
        return $this->productRepository->getProductCategoriesForFront();
    }

    /**
     * @param $parent_id
     * @return LengthAwarePaginator
     */

    public function getProductSubCategories($parent_id): LengthAwarePaginator
    {
        return $this->productRepository->getProductSubCategories($parent_id);
    }

    public function getProductCategoriesList()
    {
        return $this->productRepository->getProductCategoriesList();
    }

    public function getProductCategoriesListForCategories()
    {
        return $this->productRepository->getProductCategoriesListForCategories();
    }

    public function getProductCategoriesWithSubCategories()
    {
        return $this->productRepository->getProductCategoriesWithSubCategories();
    }

    /**
     * @param $productId
     * @return LengthAwarePaginator
     */
    public function getProductImages($productId): LengthAwarePaginator
    {
        return $this->productRepository->getProductImages($productId);
    }

    /**
     * @param $productId
     * @return Product|null
     */
    public function getProductById($productId): ?Product
    {
        return $this->productRepository->getProductById($productId);
    }

    /**
     * @param $slug
     * @return Product|null
     */
    public function getProductBySlug($slug): ?Product
    {
        return $this->productRepository->getProductBySlug($slug);
    }

    /**
     * @param $id
     * @return ProductCategory|null
     */
    public function getCategoryById($id): ?ProductCategory
    {
        return $this->productRepository->getCategoryById($id);
    }

    /**
     * @param $slug
     * @return ProductCategory|null
     */
    public function getCategoryBySlug($slug): ?ProductCategory
    {
        return $this->productRepository->getCategoryBySlug($slug);
    }

    /**
     * @param $productImageId
     * @return ProductImage|null
     */
    public function getProductImageById($productImageId): ?ProductImage
    {
        return $this->productRepository->getProductImageById($productImageId);
    }

    public function getProductWithImagesById($productId)
    {
        return $this->productRepository->getProductWithImagesById($productId);
    }

    /**
     * @param $keyword
     * @return Collection
     */
    public function searchProducts($keyword): Collection
    {
        return $this->productRepository->searchProducts($keyword);
    }

    /**
     * @param $data
     * @return Product
     */
    public function createProduct($data): Product
    {
        return $this->productRepository->createProduct($data);
    }

    /**
     * @param $data
     * @return ProductImage
     */
    public function createProductImage($data): ProductImage
    {
        return $this->productRepository->createProductImage($data);
    }

    /**
     * @param $data
     * @param $productId
     * @return Product
     */
//    public function updateProduct($data, $productId): Product
//    {
//        return $this->productRepository->updateProduct($data, $productId);
//    }

    /**
     * @param $data
     * @param $productImageId
     * @return ProductImage
     */
    public function updateProductImage($data, $productImageId): ProductImage
    {
        return $this->productRepository->updateProductImage($data, $productImageId);
    }

    /**
     * @param $data
     * @return ProductCategory
     */
    public function createProductCategory($data): ProductCategory
    {
        return $this->productRepository->createProductCategory($data);
    }

    /**
     * @param $productId
     * @return bool
     */
    public function passiveAllImages($productId)
    {
        return $this->productRepository->passiveAllImages($productId);
    }

    /**
     * @return mixed
     */
    public function productSlugControl()
    {
        return $this->productRepository->productSlugControl();
    }

    public function saveProduct(array $data)
    {
        return $this->productRepository->saveProduct($data);
    }

    public function updateProduct($data, $product)
    {
        return $this->productRepository->updateProduct($data,$product);
    }

    public function saveProductImage($productId, $imageName)
    {
        return $this->productRepository->saveProductImage($productId, $imageName);
    }

    public function saveOption($productId, $data, $key)
    {
        return $this->productRepository->saveOption($productId, $data ,$key);
    }

    public function saveProductCategory(array $data)
    {
        return $this->productRepository->saveProductCategory($data);
    }

    public function getProductCategoryById($productCategory)
    {
        return $this->productRepository->getProductCategoryById($productCategory);
    }

    public function saveProductInCategory($productId, $productInCategory)
    {
        return $this->productRepository->saveProductInCategory($productId, $productInCategory);
    }

    public function updateProductCategory($data, $id)
    {
        return $this->productRepository->updateProductCategory($data, $id);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createProductTab($data)
    {
        return $this->productRepository->createProductTab($data);
    }

    /**
     * @param $data
     * @param $id
     * @return mixed
     */
    public function updateProductTab($data,$id)
    {
        return $this->productRepository->updateProductTab($data,$id);
    }

    public function searchContents($keyword)
    {
        return $this->product->where(function ($query) use ($keyword){
           $query->orWhere('name','like','%'.$keyword. '%');
        })->orderBy('created_at','DESC')->get();
    }
}
