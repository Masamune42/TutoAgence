<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index() {
        $properties = Property::with('pictures')->orderBy('created_at','desc')->limit(4)->get();
        return view('home', ['properties' => $properties]);
    }
}
