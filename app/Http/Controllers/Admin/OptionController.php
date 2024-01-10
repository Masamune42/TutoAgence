<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OptionFormRequest;
use App\Models\Option;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    
    public function index()
    {
        // On retourne sur la page les biens triés par ordre de création et paginés par 25
        return view('admin.options.index', [
            'options' => Option::paginate(25)
        ]);
    }

    /**
     * Page de création d'un bien
     */
    public function create()
    {
        // On crée un bien et on le rempli avec fill()
        $option = new Option();
        return view('admin.options.form', [
            'option' => $option
        ]);
    }

    /**
     * Création d'un bien
     */
    public function store(OptionFormRequest $request)
    {
        // On crée un bien avec les champs validés
        Option::create($request->validated());
        return to_route('admin.option.index')->with('success', 'L\'option a été créée');
    }

    /**
     * Page d'édition d'un bien
     */
    public function edit(Option $option)
    {
        // On récupère le bien via le model binding et on retourne ses infos
        return view('admin.options.form', [
            'option' => $option
        ]);
    }

    /**
     * Modification d'un bien
     */
    public function update(OptionFormRequest $request, Option $option)
    {
        $option->update($request->validated());
        return to_route('admin.option.index')->with('success', 'L\'option a été modifiée');
    }

    /**
     * Suppression d'un bien
     */
    public function destroy(Option $option)
    {
        $option->delete();
        return to_route('admin.option.index')->with('success', 'L\'option a été supprimée');
    }
}
