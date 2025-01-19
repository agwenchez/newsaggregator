<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SourceController extends Controller
{
    // List all sources
    public function index()
    {
        $sources = Source::with('categories')->get();
        return response()->json($sources);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:sources,name',
                'base_url' => 'required|url',
                'categories' => 'required|array',
                'categories.*.name' => 'string|required', // Validate category name
                'categories.*.category_url' => 'required|url', // Ensure category URL is valid for each source-category pair
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Create the source
            $source = Source::create([
                'name' => $request->name,
                'base_url' => $request->base_url,
            ]);

            // Prepare categories and their custom URLs
            $categories = [];
            foreach ($request->categories as $categoryData) {
                // Fetch or create the category with the shared name
                $category = Category::firstOrCreate(['name' => $categoryData['name']]);

                // Add the category ID and the custom category URL for the source-category relationship
                $categories[] = [
                    'category_id' => $category->id,
                    'category_url' => $categoryData['category_url'], // Custom URL per source-category link
                ];
            }

            // Attach the categories to the source with their custom URLs
            foreach ($categories as $category) {
                $source->categories()->attach($category['category_id'], ['category_url' => $category['category_url']]);
            }

            return response()->json($source->load('categories'));
        } catch (\Exception $e) {
            \Log::error('Error creating source and categories: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while creating the source.'], 500);
        }
    }

    // Update a source
    public function update(Request $request, $id)
    {
        $source = Source::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|unique:sources,name,' . $source->id,
            'categories' => 'sometimes|array',
            'categories.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $source->name = $request->name;
        }
        $source->save();

        if ($request->has('categories')) {
            $categories = [];
            foreach ($request->categories as $categoryName) {
                $category = Category::firstOrCreate(['name' => $categoryName]);
                $categories[] = $category->id;
            }
            $source->categories()->sync($categories);
        }

        return response()->json($source->load('categories'));
    }

    // Delete a source
    public function destroy($id)
    {
        $source = Source::findOrFail($id);
        $source->categories()->detach();
        $source->delete();

        return response()->json(['message' => 'Source deleted successfully.']);
    }
}
