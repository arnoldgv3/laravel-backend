<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ProductRepositoryInterface
{
    public function find(int $id): ?Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    // Añadiremos aquí el método de búsqueda avanzada en la siguiente parte
    public function search(array $filters): CursorPaginator;
}