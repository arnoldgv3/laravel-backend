<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct()
    {
        // Protegemos todos los endpoints de escritura para que solo los admins puedan usarlos
        $this->middleware(['auth:api', 'admin'])->except(['index', 'show']);
    }

    // GET /api/categories
    public function index()
    {
        // Devolvemos todas las categorías. Ideal para que el frontend las muestre en un filtro.
        return CategoryResource::collection(Category::all());
    }

    // POST /api/categories
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id'
        ]);
        
        $validatedData['slug'] = Str::slug($validatedData['name']);
        
        $category = Category::create($validatedData);

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    // GET /api/categories/{category}
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    // PUT /api/categories/{category}
    public function update(Request $request, Category $category)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,'.$category->id,
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        if (isset($validatedData['name'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }
        
        $category->update($validatedData);

        return new CategoryResource($category);
    }

    // DELETE /api/categories/{category}
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
