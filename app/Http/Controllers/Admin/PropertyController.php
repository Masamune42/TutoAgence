<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PropertyFormRequest;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    
    public function index()
    {
        // On retourne sur la page les biens triés par ordre de création et paginés par 25
        return view('admin.properties.index', [
            'properties' => Property::orderBy('created_at', 'desc')->paginate(25)
        ]);
    }

    /**
     * Page de création d'un bien
     */
    public function create()
    {
        // On crée un bien et on le rempli avec fill()
        $property = new Property();
        $property->fill([
            'surface' => 40,
            'rooms' => 3,
            'bedrooms' => 1,
            'floor' => 0,
            'city' => 'Quimper',
            'postal_code' => '29000',
            'sold' => false,
        ]);
        return view('admin.properties.form', [
            'property' => $property
        ]);
    }

    /**
     * Création d'un bien
     */
    public function store(PropertyFormRequest $request)
    {
        // On crée un bien avec les champs validés
        Property::create($request->validated());
        return to_route('admin.property.index')->with('success', 'Le bien a été créé');
    }

    /**
     * Page d'édition d'un bien
     */
    public function edit(Property $property)
    {
        // On récupère le bien via le model binding et on retourne ses infos
        return view('admin.properties.form', [
            'property' => $property
        ]);
    }

    /**
     * Modification d'un bien
     */
    public function update(PropertyFormRequest $request, Property $property)
    {
        $property->update($request->validated());
        return to_route('admin.property.index')->with('success', 'Le bien a été modifié');
    }

    /**
     * Suppression d'un bien
     */
    public function destroy(Property $property)
    {
        $property->delete();
        return to_route('admin.property.index')->with('success', 'Le bien a été supprimé');
    }
}
