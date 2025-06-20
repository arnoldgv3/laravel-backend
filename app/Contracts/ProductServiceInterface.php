<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\CursorPaginator;

interface ProductServiceInterface
{
    public function getProductById(int $id): ?Model;
    public function createProduct(array $data): Model;
    public function updateProduct(int $id, array $data): bool;
    public function deleteProduct(int $id): bool;
}