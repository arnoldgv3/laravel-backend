<?php
namespace App\Services;

use App\Contracts\ProductRepositoryInterface;
use App\Contracts\ProductServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductService implements ProductServiceInterface
{
    // Usaremos tags para invalidar la caché de listas de manera eficiente
    private const CACHE_TAG = 'products';

    public function __construct(private ProductRepositoryInterface $productRepository) {}

    public function searchProducts(array $filters): CursorPaginator
    {
        // Generar una clave única para esta combinación de filtros
        $cacheKey = 'products_list:' . http_build_query($filters);

        // TTL de 15 minutos para listados 
        return Cache::tags([self::CACHE_TAG])->remember($cacheKey, 900, function () use ($filters) {
            return $this->productRepository->search($filters);
        });
    }

    public function getProductById(int $id): ?Model
    {
        $cacheKey = 'product:' . $id;
        
        // TTL de 1 hora para productos individuales 
        return Cache::tags([self::CACHE_TAG])->remember($cacheKey, 3600, function () use ($id) {
            return $this->productRepository->find($id);
        });
    }

    public function createProduct(array $data): Model
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        $product = $this->productRepository->create($data);
        
        // Invalidar caché de listas al crear un producto nuevo
        Cache::tags([self::CACHE_TAG])->flush();
        
        return $product;
    }

    public function updateProduct(int $id, array $data): bool
    {
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $updated = $this->productRepository->update($id, $data);

        if ($updated) {
            // Invalidación de caché en actualizaciones 
            Cache::tags([self::CACHE_TAG])->flush(); // Invalida todas las listas
            Cache::forget('product:' . $id); // Invalida el producto específico
        }
        
        return $updated;
    }

    public function deleteProduct(int $id): bool
    {
        $deleted = $this->productRepository->delete($id);
        
        if ($deleted) {
            // Invalidación de caché en borrados
            Cache::tags([self::CACHE_TAG])->flush();
            Cache::forget('product:' . $id);
        }

        return $deleted;
    }
}