<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
        // List all categories
        public function index()
        {
            $categories = Category::with('sources')->get();
            return response()->json($categories);
        }
    
        // Create a new category
        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:categories,name',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $category = Category::create(['name' => $request->name]);
    
            return response()->json($category);
        }
    
        // Update a category
        public function update(Request $request, $id)
        {
            $category = Category::findOrFail($id);
    
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:categories,name,' . $category->id,
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $category->name = $request->name;
            $category->save();
    
            return response()->json($category);
        }
    
        // Delete a category
        public function destroy($id)
        {
            $category = Category::findOrFail($id);
            $category->sources()->detach();
            $category->delete();
    
            return response()->json(['message' => 'Category deleted successfully.']);
        }
}
