<?php

namespace App\Http\Resources;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Property $resource
 */
class PropertyResource extends JsonResource
{
    public static $wrap = "property";

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'price' => $this->when(false, $this->resource->price),
            'options' => OptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
