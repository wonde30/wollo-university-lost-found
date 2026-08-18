<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Items;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Items\UploadItemPhotoRequest;
use App\Http\Resources\Api\V1\ItemPhotoResource;
use App\Models\Item;
use App\Models\ItemPhoto;
use Illuminate\Http\JsonResponse;

class ItemPhotoController extends Controller
{
    public function store(UploadItemPhotoRequest $request, int $itemId): JsonResponse
    {
        $item = Item::findOrFail($itemId);
        $this->authorize('update', $item);

        $file = $request->file('photo');
        $path = $file->store('item-photos', 'public');

        $photo = ItemPhoto::create([
            'item_id' => $item->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'is_primary' => $request->boolean('is_primary', false),
        ]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'data' => new ItemPhotoResource($photo),
        ], JsonResponse::HTTP_CREATED);
    }

    public function destroy(int $itemId, int $photoId): JsonResponse
    {
        $item = Item::findOrFail($itemId);
        $this->authorize('update', $item);

        $photo = ItemPhoto::where('item_id', $itemId)->findOrFail($photoId);
        $photo->delete();

        return response()->json([
            'message' => 'Photo deleted successfully',
        ], JsonResponse::HTTP_NO_CONTENT);
    }
}
