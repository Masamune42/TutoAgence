<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Weather;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private Weather $weather)
    {

    }

    public function index()
    {
        $properties = Property::with('pictures')->available()->recent()->limit(4)->get();
        return view('home', ['properties' => $properties]);
    }
}
