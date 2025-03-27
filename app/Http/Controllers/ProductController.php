<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http; 
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; 
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    private $imgbbApiKey = "dfdf0b2c58bc183e7d8c4bb19fa26331";
    
    public function index()
{
    $products = Product::all()->map(function ($product) {
        $product->image = $product->image ? url('storage/' . $product->image) : null;
        return $product;
    });
    return response()->json([
        "status" => "success",
        "data" => $products
    ]);
}
    
public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $imageUrl = null;

        // Upload image to ImgBB
        if ($request->hasFile('image')) {
            $response = Http::asMultipart()->post("https://api.imgbb.com/1/upload", [
                'key' => $this->imgbbApiKey,
                'image' => base64_encode(file_get_contents($request->file('image')->path())),
            ]);
            
            if ($response->successful()) {
                $imageUrl = $response->json()['data']['url'];
            } else {
                return response()->json(['success' => false, 'message' => 'Image upload failed'], 500);
            }
        }

        // Create product with ImgBB image URL
        $product = Product::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'price' => $validatedData['price'],
            'image' => $imageUrl, // Store ImgBB URL
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully',
            'data' => $product,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return $this->successResponse($product);
    }

    
    
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);
    
        // Update only non-image fields
        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $product->description,
            'price' => $validated['price'],
        ]);
    
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ]);
    }
    


    
    
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return $this->successResponse("Product deleted successfully");
    }
}
