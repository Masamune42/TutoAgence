<?php

namespace App\Http\Controllers;

use App\Events\ContactRequestEvent;
use App\Http\Requests\PropertyContactRequest;
use App\Http\Requests\SearchPropertiesRequest;
use App\Mail\PropertyContactMail;
use App\Models\Property;
use App\Models\User;
use App\Notifications\ContactRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class PropertyController extends Controller
{
    public function index(SearchPropertiesRequest $request)
    {
        $query = Property::query()->with('pictures')->orderBy('created_at', 'desc');
        if($request->validated('price')) {
            $query = $query->where('price', '<=', $request->validated('price'));
        }
        if($request->validated('surface')) {
            $query = $query->where('surface', '>=', $request->validated('surface'));
        }
        if($request->validated('rooms')) {
            $query = $query->where('rooms', '>=', $request->validated('rooms'));
        }
        if($request->validated('title')) {
            $query = $query->where('title', 'like', "%{$request->validated('title')}%");
        }
//        $properties = Property::paginate(16);
        return view('property.index', [
            'properties' => $query->paginate(16),
            'input' => $request->validated()
        ]);
    }

    public function show(string $slug, Property $property)
    {
        $expectedSlug = $property->getSlug();
        if($slug !== $expectedSlug) {
            return to_route('property.show', ['slug' => $expectedSlug, 'property' => $property]);
        }
        return view('property.show', [
           'property' => $property
        ]);
    }

    public function contact(Property $property, PropertyContactRequest $request)
    {
//        Anciennes méthodes (sans la création de notification)
//        event(new ContactRequestEvent($property, $request->validated()));
//        Mail::send(new PropertyContactMail($property, $request->validated()));
//        Utilisation des notifications
//        Méthode 1
//        $user = User::first();
//        $user->notify(new ContactRequestNotification($property, $request->validated()));
//        Méthode 2
        Notification::route('mail', 'john@admin.fr')->notify(new ContactRequestNotification($property, $request->validated()));
        return back()->with('success', 'Votre demande de contact a bien été envoyée');
    }
}
