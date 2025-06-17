<?php

namespace App\Http\Controllers\API;

use App\Contracts\ProductServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(private ProductServiceInterface $productService)
    {
        // Proteger rutas de escritura con middleware de autenticación y de admin
        $this->middleware(['auth:api', 'admin'])->only(['store', 'update', 'destroy', 'uploadImage']);
    }

    // GET /api/products - Listado con filtros avanzados
    public function index(Request $request)
    {
        $filters = $request->all();
        $products = $this->productService->searchProducts($filters);
        return response()->json($products);
    }

    // GET /api/products/{id} - Detalle con caché
    public function show($id)
    {
        $product = $this->productService->getProductById((int)$id);
        return $product ? response()->json($product) : response()->json(['error' => 'Product not found'], 404);
    }

    // POST /api/products - Crear (solo admin)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            // ... otras validaciones
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = $this->productService->createProduct($validator->validated());
        return response()->json($product, 201);
    }
    
    // PUT /api/products/{id} - Actualizar (solo admin)
    public function update(Request $request, $id)
    {
        $product = $this->productService->getProductById((int)$id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        
        $this->productService->updateProduct((int)$id, $request->all());
        return response()->json($this->productService->getProductById((int)$id)); // Devuelve el producto actualizado
    }
    
    // DELETE /api/products/{id} - Eliminar (solo admin)
    public function destroy($id)
    {
        if ($this->productService->deleteProduct((int)$id)) {
            return response()->json(null, 204);
        }
        return response()->json(['error' => 'Product not found'], 404);
    }

    // POST /api/products/{id}/images - Subir imágenes con validación
    public function uploadImage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $product = Product::findOrFail($id);
        
        $path = $request->file('image')->store('product-images', 'public');
        
        $image = $product->images()->create([
            'url' => Storage::url($path),
            'alt_text' => $request->input('alt_text', $product->name),
            'is_primary' => $request->input('is_primary', false),
        ]);

        return response()->json($image, 201);
    }
    public function registerView($id)
    {
        // Usamos una consulta atómica para evitar race conditions
        $updated = Product::where('id', $id)->update(['views_count' => DB::raw('views_count + 1')]);

        if ($updated) {
            return response()->json(['message' => 'Product view registered.']);
        }

        return response()->json(['error' => 'Product not found.'], 404);
    }
}