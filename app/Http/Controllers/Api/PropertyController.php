<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
//        Pour une collection
//        return PropertyResource::collection(Property::limit(5)->with('options')->get());
//        Pour un seul élément simple
//        return new PropertyResource(Property::find(1));
        return PropertyResource::collection(Property::paginate(5));
    }
}
