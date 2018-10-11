<?php

namespace App\Http\Controllers;

use App\Bundle;
use App\Book;
use App\Transformer\BundleTransformer;

class BundlesController extends Controller
{
    public function show($id)
    {
        $bundle = Bundle::findOrFail($id);
        $data = $this->item($bundle, new BundleTransformer());
        return response()->json($data);
    }

    public function addBook($bundleId, $bookId)
    {
        $bundle = Bundle::findOrFail($bundleId);
        $book = Book::findOrFail($bookId);

        $bundle->books()->attach($book);
        $data = $this->item($bundle, new BundleTransformer());

        return response()->json($data);
    }

    public function removeBook($bundleId, $bookId)
    {
        $bundle = Bundle::findOrFail($bundleId);
        $book = Book::findOrFail($bookId);

        $bundle->books()->detach($book);

        return response(null, 204);
    }
}
