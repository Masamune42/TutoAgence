# TP Agence Laravel
## Lancer le projet
Installation
```
composer install
```
Copier le fichier [.env.example](.env.example) en .env et changer les constantes
```dotenv
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
# Supprimer les lignes suivantes pour une connexion sqlite
```

Création de la BDD + migrations
```
php artisan migrate
```

Création du raccourci pour le stockage des images
```
php artisan storage:link
```

```
php artisan serve
```
En utilisant vite
```
npm i
npm run build
npm run dev
```

## Les bases à connaitre

### Le Routing
A configurer dans **routes\web.php**
```php
// URL get '/' pour accéder à la vue 'welcome'
Route::get('/', function () {
    return view('welcome');
});

// URL get '/blog' pour retourner un json avec 'article' qui contiendra l'élément de la requête 'name' sinon 'john' par défaut
// Nom : blog.index
Route::get('/blog', function(Request $request) {
    return ['article' => $request->input('name', 'john')];
})->name('blog.index');

// URL get qui va contenir un slug et un id que l'on défini dans la fonction
// where => Défini le format attendu pour id (numérique) et slug (chaines de caractères en acceptant que des tirets)
// Nom : blog.show
Route::get('/blog/{slug}-{id}', function(string $slug, string $id) {
    return [
        "slug" => $slug,
        "id" => $id,
    ];
})->where([
    'id' => '[0-9]+',
    'slug' => '[a-z0-9\-]+',
])->name('blog.show');

// On peut regrouper les routes de cette façon
// toutes les routes préfixer par '/blog' et avec un nom qui commencent par 'blog.'
Route::prefix('/blog')->name('blog.')->group(function() {
    Route::get('/', function(Request $request) {
        return ['article' => $request->input('name', 'john')];
    })->name('index');
    
    Route::get('/{slug}-{id}', function(string $slug, string $id) {
        return [
            "slug" => $slug,
            "id" => $id,
        ];
    })->where([
        'id' => '[0-9]+',
        'slug' => '[a-z0-9\-]+',
    ])->name('show');
});
```
Pour accéder à toutes les routes
```
php artisan route:list
```

### ORM Eloquent
Créer une migration
```
php artisan make:migration CreatePostTable
```
On configure la migration
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->longText('content');
    $table->timestamps();
});
```
On envoie en BDD
```
php artisan migrate
```

On crée un modèle (avec -m si l'on veut créer la migration en même temps)
```
php artisan make:model Post
php artisan make:model Post -m
```

Exemple de création d'un article dans **web.php**
```php
$post = new Post();
$post->title = "Mon premier article";
$post->slug = "mon-premier-article";
$post->content = "Contenu";
$post->save();

return $post;
```

Exemple de visualisation dans **web.php**
```php
// Retourne tous les articles
return Post::all();

// Retourne tous les articles avec uniquement les champs id et title
return Post::all(['id', 'title']);

// On renvoie un objet de type Collection qui contient les items de type Post
$posts = Post::all(['id', 'title']);
dd($posts);

// On accède au title du 1er élément
dd($posts[0]->title);

// On récupère le post avec l'id 2, renvoie null si la donnée n'a pas été trouvée
$posts = Post::find(2);

// Renvoie une page d'erreur si la donnée n'a pas été trouvée
$posts = Post::findOrFail(3);

// Pagination : 1 élément par page
// Avec un return, est converti et affiché : affichage toutes les informations concernant la pagination
$posts = Post::paginate(1);
// On ne retourne que l'id et le title
$posts = Post::paginate(1, ['id', 'title']);

// QueryBuilder
// On récupère tous les éléments avec un id > 1
$posts = Post::where('id', '>', '0')->get();
// On limite à 1 résultat
$posts = Post::where('id', '>', '1')->limit(1)->get();

// Modifier un élément
$post = Post::find(1);
$post->title = 'Nouveau titre';
$post->save();

// Supprimer un élément
$post = Post::find(1);
$post->delete();


// Création via un tableau
// Au préalable ajouter le code dans Post.php avec tous les champs que l'on peut créer
// Option inverse avec $guarded
protected $fillable = [
    'title',
    'slug',
    'content',
];
// On peut ainsi créer de cette façon
$post = Post::create([
    'title' => 'Mon nouveau titre',
    'slug' => 'nouveau-titre',
    'content' => 'Contenu',
]);

// Aussi possible avec where sur update / delete
$post = Post::where('id', '>', 1)->update([
    'title' => 'Mon nouveau titre',
    'slug' => 'nouveau-titre',
    'content' => 'Contenu',
]);

```

### Controllers
```
php artisan make:controller PostController
```
On récupère les actions à exécuter dans les pages dans le controller qu'on appelle dans **web.php**
```php
// web.php
Route::prefix('/blog')->name('blog.')->group(function() {

    Route::get('/', [BlogController::class, 'index'])->name('index');
    
    Route::get('/{slug}-{id}', [BlogController::class, 'show'])->where([
        'id' => '[0-9]+',
        'slug' => '[a-z0-9\-]+',
    ])->name('show');
});

// BlogController.php
class BlogController extends Controller
{
    public function index(): Paginator
    {
        return Post::paginate(25);
    }

    public function show(string $slug, string $id): RedirectResponse | Post
    {
        $post = Post::findOrFail($id);
        if($post->slug !== $slug) {
            return to_route('blog.show', ['slug' => $post->slug, 'id' => $post->id]);
        }
        return $post;
    }
}
```
On peut aussi grouper au niveau du controller
```php
Route::prefix('/blog')->name('blog.')->controller(BlogController::class)->group(function() {

    Route::get('/', 'index')->name('index');
    
    Route::get('/{slug}-{id}', 'show')->where([
        'id' => '[0-9]+',
        'slug' => '[a-z0-9\-]+',
    ])->name('show');
});
```


### Blade
On crée les vues index et show pour les appeler avec des paramètres dans les controller
```php
public function index(): View
{
    return view('blog.index', [
        'posts' => Post::paginate(1)
    ]);
}

public function show(string $slug, string $id): RedirectResponse | View
{
    $post = Post::findOrFail($id);
    if($post->slug !== $slug) {
        return to_route('blog.show', ['slug' => $post->slug, 'id' => $post->id]);
    }
    return view('blog.show', [
        'post' => $post
    ]);
}
```
**base.blade.php** pour créer une base HTML réutilisable pour les autres vues
```php
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>

    @php
        $routeName = request()->route()->getName();
    @endphp
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Blog</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01"
                aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a  @class(['nav-link', 'active' =>  Str::startsWith($routeName, 'blog.')]) href="{{ route('blog.index') }}">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Link</a>
                    </li>
                </ul>
            </div>
        </div>

    </nav>

    <div class="container">
        @yield('content')
    </div>
</body>

</html>
```
Comme on utilise Bootstrap 5 on l'indique dans le fichier **app\Providers\AppServiceProvider.php**
```php
public function boot(): void
{
    Paginator::useBootstrapFive();
}
```
**index.blade.php**
```php
@extends('base')

@section('title', 'Accueil du blog')

@section('content')
    <h1>Mon blog</h1>
    
    @foreach ($posts as $post)
        <article>
            <h2>{{ $post->title }}</h2>
            <p>
                {{ $post->content }}
            </p>
            <p>
                <a href="{{ route('blog.show', ['slug' => $post->slug, 'id' => $post->id]) }}" class="btn btn-primary">Lire la suite</a>
            </p>
        </article>
    @endforeach

    {{ $posts->links() }}
@endsection
```
**show.blade.php**
```php
@extends('base')

@section('title', $post->title)

@section('content')
    <article>
        <h2>{{ $post->title }}</h2>
        <p>
            {{ $post->content }}
        </p>
    </article>
@endsection
```

### Validator
Dans le controller
```php
// 1er paramètre : champ reçu de la requête, 2e paramètre : les règles
$validator = Validator::make([
    'title' => '',
    'content' => ''
], [
    'title' => 'required|min:8'
]);


// renvoie true si la validation échoue
$validator->fails();

// renvoie les messages d'erreur
$validator->errors()

// Renvoie les champs validés
// Si aucune donnée validée, renvoie vers la page précédente par défaut
$validator->validated()

// Les règles peuvent être écrites en tableau
$validator = Validator::make([
    'title' => 'a',
    'content' => 'zzaza'
], [
    'title' => ['required', 'min:8', 'regex:pattern']
]);

// title doit être unique dans la table post en ignorant l'entrée avec l'ID 2
$validator = Validator::make([
    'title' => 'a',
    'content' => 'zzaza'
], [
    'title' => [Rule::unique('post')->ignore(2)]
]);
```

On peut créer des requêtes personnalisées pour valider les données
```
php artisan make:request BlogFilterRequest
```

On y place la règle dans une fonction
```php
// Dans BlogFilterRequest
public function rules(): array
{
    return [
        'title' => ['required', 'min:4'],
        'slug' => ['required', 'regex:/^[a-z0-9\-]+$/']
    ];
}

// Dans BlogController
public function index(BlogFilterRequest $request): View
{
    return view('blog.index', [
        'posts' => Post::paginate(1)
    ]);
}

// On peut y définir une fonction appelée avant la validation
// Si slug existe dans la requête on l'utilise, sinon on slugify title dans la requête
protected function  prepareForValidation()
{
    $this->merge([
        'slug' => $this->input('slug') ?: Str::slug($this->input('title'))
    ]);
}
```

### Model Binding
On peut pré-récupérer les informations lorsqu'on a une route spécifique
1. On change le nommage dans l'URL (id > post)
```php
Route::get('/{slug}-{post}', 'show')->where([
    'id' => '[0-9]+',
    'slug' => '[a-z0-9\-]+',
])->name('show');
```
2. On renomme dans le controller et on récupère un objet Post. On peut supprimer le findOrFail.
```php
public function show(string $slug, Post $post): RedirectResponse | View
{
    if($post->slug !== $slug) {
        return to_route('blog.show', ['slug' => $post->slug, 'id' => $post->id]);
    }
    return view('blog.show', [
        'post' => $post
    ]);
}
```
3. On change dans les vues les urls
```php
<a href="{{ route('blog.show', ['slug' => $post->slug, 'post' => $post->id]) }}" class="btn btn-primary">Lire la suite</a>
```
Peut fonctionner aussi avec le slug
```php
// web.php
Route::get('/{post:slug}', 'show')->where([
    'post' => '[a-z0-9\-]+',
])->name('show');
// BlogController.php
public function show(Post $post): RedirectResponse | View
{
    return view('blog.show', [
        'post' => $post
    ]);
}
```

### Debug
On peut se servir d'un outil de debug dans Laravel en installant une librairie
```
composer require barryvdh/laravel-debugbar --dev
```


### Formulaires
Pour gérer les formulaires on en crée sous blade avec un jeton CSRF.
Comme on va l'utiliser pour créer et modifier on le crée dans un fichier **form.blade.php**
```php
<form action="" method="POST">
    @csrf
    <div>
        <input type="text" name="title" value="{{ old('title', $post->title) }}">
        @error("title")
            {{ $message }}
        @enderror
    </div>
        <div>
        <textarea name="slug">{{ old('slug', $post->slug) }}</textarea>
        @error("slug")
            {{ $message }}
        @enderror
    </div>
        <div>
        <textarea name="content">{{ old('content', $post->content) }}</textarea>
        @error("content")
            {{ $message }}
        @enderror
    </div>
    <button type="submit">
        @if ($post->id)
            Modifier
        @else
            Enregistrer
        @endif
    </button>
</form>
```

On gère les routes dans **web.php** et dans le controller
```php
// web.php
Route::get('/new', 'create')->name('create');
Route::post('/new', 'store');
Route::get('/{post}/edit', 'edit')->name('edit');
Route::post('/{post}/edit', 'update');

// BlogController
public function create() : View
{
    $post = new Post();
    return view('blog.create', [
        'post' => $post
    ]);
}

public function store(FormPostRequest $request)
{
    $post = Post::create($request->validated());
    return redirect()->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])->with('success', "L'article a bien été sauvegardé");
}

public function edit(Post $post)
{
    // On crée un article vide car le formulaire attends un objet Post
    return view('blog.edit', [
        'post' => $post
    ]);
}

public function update(Post $post, FormPostRequest $request)
{
    $post->update($request->validated());
    return redirect()->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])->with('success', "L'article a bien été modifié");
}
```
### Relation
On crée Category : Model +  migration
```
php artisan make:model Category -m
```

On spécifie que chaque catégorie n'appartient qu'à un article
```php
// Migration
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    Schema::table('posts', function(Blueprint $table) {
        $table->foreignIdFor(Category::class)->nullable()->constrained()->cascadeOnDelete();
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::dropIfExists('categories');
    Schema::table('posts', function(Blueprint $table) {
        $table->dropForeignIdFor(Category::class);
    });
}

// Post.php
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

Pour récupérer les informations
```php
// On récupère tous les articles avec une catégorie associée
$posts = Post::with('category')->get();
```

Pour récupérer les articles associés à une catégorie
```php
// Dans Category.php
public function posts() {
    return $this->hasMany(Post::class);
}
```

```php
// On récupère la catégorie avec l'id 1
$category = Category::find(1);
// On récupère les articles lié à la catégorie 1 qui ont un id supérieur à 10
$category->posts()->where('id', '>', '10')->get();

// Pour associer une catégorie à un article
$category = Category::find(1);
$post = Post::find(6);
$post->category()->associate($category);
$post->save();
```
On crée un modèle et une migration pour Tag et on prépare la migration
```php
public function up(): void
{
    Schema::create('tags', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
    Schema::create('post_tag', function(Blueprint $table) {
        $table->foreignIdFor(Post::class)->constrained()->cascadeOnDelete();
        $table->foreignIdFor(Tag::class)->constrained()->cascadeOnDelete();
        $table->primary(['post_id', 'tag_id']);
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::dropIfExists('post_tag');
    Schema::dropIfExists('tags');
}

// On prépare le modèle Tag
class Tag extends Model
{
    use HasFactory;

    public function posts() {
        return $this->belongsToMany(Post::class);
    }

    protected $fillable = [
        'name'
    ];
}

// On modifie le modèle Post en créant une fonction
public function tags() {
    return $this->belongsToMany(Tag::class);
}

// On récupère l'article avec l'ID 2 et on lui assigne 2 tags que l'on crée
$post = Post::find(2);
$post->tags()->createMany([[
    'name' => 'Tag 1'
] , [
    'name' => 'Tag 2'
]]);

// On peut attacher et détacher le tag d'un article
$post = Post::find(2);
$post->tags()->detach(2);
$post->tags()->attach(2);
// On peut synchroniser un article avec des tags ou vide
$post->tags()->sync([1, 2]);
// On récupère les articles qui possèdent au moins un tag
Post::has('tags', '>=', 1)->get()
```

On inclut le formulaire suivant dans les pages de création et modification
```php
<form action="" method="POST" class="container mt-5">
    @csrf
    <div class="mb-3">
        <label for="title" class="form-label">Titre</label>
        <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $post->title) }}">
        @error('title')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="slug" class="form-label">Slug</label>
        <textarea name="slug" id="slug" class="form-control">{{ old('slug', $post->slug) }}</textarea>
        @error('slug')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">Contenu</label>
        <textarea name="content" id="content" class="form-control">{{ old('content', $post->content) }}</textarea>
        @error('content')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label for="category" class="form-label">Catégorie</label>
        <select name="category_id" id="category" class="form-select">
            <option value="">Sélectionner une catégorie</option>
            @foreach ($categories as $category)
                <option @selected(old('category', $post->category_id) == $category->id) value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        @php
            $tagsIds = $post->tags()->pluck('id');
        @endphp
        <label for="tag" class="form-label">Tags</label>
        <select name="tags[]" id="tag" class="form-select" multiple>
            @foreach ($tags as $tag)
                <option @selected($tagsIds->contains($tag->id)) value="{{ $tag->id }}">{{ $tag->name }}</option>
            @endforeach
        </select>
        @error('tags')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary">
        @if ($post->id)
            Modifier
        @else
            Enregistrer
        @endif
    </button>
</form>
```

On prépare le controlleur à recevoir les informations de tags et catégorie
```php
public function store(FormPostRequest $request)
{
    $post = Post::create($request->validated());
    $post->tags()->sync($request->validated('tags'));
    return redirect()
        ->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])
        ->with('success', "L'article a bien été sauvegardé");
}

public function update(Post $post, FormPostRequest $request)
{
    // On update les champs validés en BDD
    $post->update($request->validated());
    // On synchronise les tags (car relation ManyToMany)
    $post->tags()->sync($request->validated('tags'));
    return redirect()
        ->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])
        ->with('success', "L'article a bien été modifié");
}
```


### Authentification
On crée un AuthController, on assigne des routes de connexion / déconnexion et on crée une page de login
```php
// web.php
Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/login', [AuthController::class, 'doLogin']);
Route::delete('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// AuthController
public function login()
{
    return view('auth.login');
}

public function logout()
{
    Auth::logout();
    return to_route('auth.login');
}


public function doLogin(LoginRequest $request)
{
    $credentials = $request->validated();

    if(Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended(route('blog.index'));
    }

    return to_route('auth.login')->withErrors([
        'email' => "Email invalide"
    ])->onlyInput('email');
}

// login.blade.php
@extends('base')


@section("content")

    <h1>Se connecter</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('auth.login') }}" method="post" class="vstack gap-3">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" name="password" id="password" class="form-control" >
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary">
                    Se Connecter
                </button>

            </form>
        </div>
    </div>
@endsection
```
Dans app\Http\Middleware\Authenticate.php on modifie la route de redirection par défaut en cas d'accès à une page où il faut autre authentifié
```php
protected function redirectTo(Request $request): ?string
{
    return $request->expectsJson() ? null : route('auth.login');
}
```

### Système de fichiers
On crée une migration pour créer un champ image en BDD
```php
public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->string('image')->nullable();
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropColumn('image');
    });
}
```
On modifie les fonctions pour créer et update des articles pour pouvoir télécharger des images. On ajoute une fonction privée pour gérer les images (ajout / suppression)
```php
public function store(FormPostRequest $request)
{
    $post = Post::create($this->extractData(new Post(), $request));
    $post->tags()->sync($request->validated('tags'));
    return redirect()
        ->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])
        ->with('success', "L'article a bien été sauvegardé");
}


public function update(Post $post, FormPostRequest $request)
{
    
    $post->update($this->extractData($post, $request));
    // On synchronise les tags (car relation ManyToMany)
    $post->tags()->sync($request->validated('tags'));
    return redirect()
        ->route('blog.show', ['slug' => $post->slug, 'post' => $post->id])
        ->with('success', "L'article a bien été modifié");
}

private function extractData(Post $post, FormPostRequest $request)
{
    $data = $request->validated();
    /**
     * @var UploadedFile|null $image
     */
    $image = $request->validated('image');
    if($image === null || $image->getError())
        return $data;
    if($post->image)
        Storage::disk('public')->delete($post->image);
    $data['image'] = $image->store('blog', 'public');
    return $data;
}

// Dans form.blade.php on ajoute un champ et on ajoute enctype="multipart/form-data" dans la balise form
<div class="mb-3">
    <label for="image" class="form-label">Titre</label>
    <input type="file" name="image" id="image" class="form-control">
    @error('image')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
```

On modifie le .env pour qu'il corresponde à l'URL du site
```.env
APP_URL=http://localhost:8000
```

On crée un lien vers le dossier de storage qui n'est pas accessible normalement
```
php artisan storage:link
```


## Lancer le projet
```
composer install

php artisan serve
```

## Création du projet

### Partie administration
Création du modèle Property
```
php artisan make:model -m Property
```
Dans **2023_12_06_230538_create_properties_table.php** on crée les colonnes de la table properties
```php
Schema::create('properties', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->longText('description');
    $table->integer('surface');
    $table->integer('rooms');
    $table->integer('floor');
    $table->integer('price');
    $table->string('city');
    $table->string('address');
    $table->string('postal_code');
    $table->boolean('sold');
    $table->timestamps();
});
```

Création du controlleur (ajouter -r pour dire que c'est une ressource)
```
php artisan make:controller Admin\PropertyController
php artisan make:controller Admin\PropertyController -r
```


Création des routes dans **web.php**
```php
// Création d'une route /admin
Route::prefix('admin')->name('admin.')->group(function() {
    // Création d'une route /admin/property qui utilise le PropertyController
    Route::resource('property', \App\Http\Controllers\Admin\PropertyController::class);
});
```

Création d'une requête
```
php artisan make:request Admin\PropertyFormRequest
```
- On crée le fichier **views/admin/admin.blade.php** pour l'accueil de la page admin
- On crée le fichier **views/admin/form.blade.php** pour gérer le formulaire d'édition/création d'un bien
- On crée le fichier **views/shared/input.blade.php** pour générer les inputs de texte
- On crée le fichier **views/shared/checkbox.blade.php** pour générer les checkbox

## Gestion des options
On crée un modèle + une migration pour les options
```
php artisan make:model -m Option
```
- On crée **OptionFormRequest.php** pour gérer les règles des requêtes
- On crée **OptionControllerphp** (à partir de PropertyController) pour gérer les routes
- On crée **views/options/index.blade.php** (à partir de /admin/index.blade.php) pour gérer la page principale
- On crée **views/options/form.blade.php** (à partir de /admin/form.blade.php) pour gérer le formulaire

On crée une migration pour générer une table de liaison pour la relation manyToMany
````
php artisan make:migration CreateOptionPropertyTable
php artisan migrate
````
- On crée le fichier **views/shared/select.blade.php** pour générer les select

## Listing
On crée un controller
```
php artisan make:controller HomeController
```
On crée une Request
```
php artisan make:request SearchPropertiesRequest
```
Installation de la debugbar
```
composer require barryvdh/laravel-debugbar --dev
```

## Demande de contact
On crée une requête
```
php artisan make:request PropertyContactRequest
```

On crée un mailable
```
php artisan make:mail PropertyContactMail --markdown=emails.property.contact
```

On utilise [Mailhog](https://github.com/mailhog/MailHog/releases/tag/v1.0.1) pour simuler l'envoi de mails en configurant le .env
```dotenv
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
```

## Authentification
On crée un controller pour l'authentification et une requête
```dotenv
php artisan make:controller AuthController
php artisan make:request LoginRequest
```

## Images
On crée un modèle + migration et on migre après avoir configuré
```
php artisan make:model -m Picture
php artisan migrate
```

On configure l'enregistrement de l'image et fait la liaison pour les images et ajoute le port 8000 dans le .env (en localhost)
```
php artisan storage:link
```

On ajoute [htmx](https://htmx.org) pour supprimer des images et on crée un controller
```
php artisan make:controller Admin/PictureController
```

On installe Glider pour Laravel pour redéfinir la dimension des images à afficher
```
composer require league/glide-laravel
```

## Accesseurs et mutateurs
### Exemple de local scope
On crée un scope dans **Property.php** en créant une fonction
```php
public function scopeAvailable(Builder $builder): Builder
{
    return $builder->where('sold', false);
}
```
On peut ensuite appeler ce scope dans un controller en retirant le mot scope
```php
public function index()
{
    $properties = Property::with('pictures')->available()->orderBy('created_at','desc')->limit(4)->get();
    return view('home', ['properties' => $properties]);
}
```

### Exemple de global scope
Il existe un Global Scope qui permet de faire un soft delete qui permet de ne pas supprimer réellement un élément mais d'enregistrer en BDD une date de suppression si on a besoin de revenir dessus
On crée un scope dans **Property.php** on ajoute
```php
use SoftDeletes;
```
On doit aussi générer un champ en plus en BDD
```
php artisan make:migration AddDeletedAtToProperties
php artisan migrate
```
De cette façon si on supprime un élément, on va retrouver les mêmes informations à l'affichage mais au niveau de la BDD le bien n'a pas été supprimé car au niveau de la requête SQL on a "deleted_at" is null
```sql
select count(*) as aggregate from "properties" where "properties"."deleted_at" is null
```
Pour afficher des éléments supprimés on peut ajouter la fonction withTrashed()
```php
return view('admin.properties.index', [
    'properties' => Property::orderBy('created_at', 'desc')->withTrashed()->paginate(25)
]);
```
Pour vraiment supprimer un élément on utilisera la fonction forceDelete() à la place de delete() et restore() pour restaurer un élément
```php
$property->forceDelete();
$property->restore();
```

### Mutateurs et casts
Pour afficher des champs de la BDD (created_at) si on veut les afficher correctement on doit préciser un cast dans **Property.php**
```php
protected $casts = [
    'created_at' => 'string'
];
```
Sinon on peut cast un champ comme sold
```php
protected $casts = [
    'sold' => 'boolean'
];
```
Pour des cas plus spécifiques
```php
$user = User::first();
// On veut assigner un password à 000 mais il faut qu'il soit encrypter
$user->password = '0000';
// A l'affichage de l'attribut password on souhaite afficher '' et si on affiche le user on veut afficher le champ encrypter
dd($user->password, $user);
```
Pour cela on crée une fonction protected dans **Property.php**
```php
protected function password(): Attribute
{
    return Attribute::make(
        get: fn (?string $value) => '',
        set: fn (string $value) => Hash::make($value),
    );
}
```

### Important
Lorsque l'on regarde le modèle User on remarque :
- Un tableau "attributes" : qui contient les informations actuelles
- Un tableau "original" : qui contient les informations originales avant modification
Lorsque que l'on fait un save() par la suite Laravel va comparer les 2 tableaux et on récupère dans le tableau "changes" la liste des changements à effectuer qui sera utilisé pour faire un update

## Seed et Factory
On peut modifier le fichier **database\seeders\DatabaseSeeder.php** pour remplir la base de données
```php
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        User::factory()->unverified()->create();
    }
}
```
On utilise ensuite une commande pour le faire
```
php artisan db:seed
```
Pour tout effacer
```
php artisan migrate:fresh
``` 
On crée une nouvelle Factory pour les biens
```
php artisan make:factory PropertyFactory
```
On change dans **config\app.php** la langue local de faker
```php
'faker_locale' => 'fr_FR',
```
On crée aussi une factory pour les options
```
php artisan make:factory OptionFactory
```

### Vite
On peut ajouter Vite sur Laravel et utiliser Bootstrap avec :
```js
// Dans app.js
import './bootstrap';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.css';
```
On ajoute ensuite dans **base.blade.php** du code pour remplacer l'import du CSS et JS de Bootstrap :
```
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

## Laravel Breeze
C'est un template blade préstylisé avec Tailwind CSS
```
composer require laravel/breeze --dev
php artisan breeze:install blade
```

On peut modifier les prérequis du mdp dans **\Controllers\Auth\RegisteredUserController.php@store**
```php
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'confirmed', new Rules\Password(4)],
]);
```

Si on implements de MustVerifyEmail dans **User.php** on défini que l'utilisateur doit avoir vérifier son adresse mail pour accéder à des pages (ex : dashboard)
```php
class User extends Authenticatable implements MustVerifyEmail
```

Pour limiter les routes dans **web.php** aux user vérifiés on ajoute une règle dans le middleware d'une Route
```php
middleware(['auth', 'verified'])
```

## Les Policy
On ajoute une colonne pour les rôles
```
php artisan make:migration AddRoleToUsers
php artisan migrate
```
On crée une Policy qu'on associe au modèle Property
```
php artisan make:policy PropertyPolicy --model=Property
```
On définie les règle dans **PropertyPolicy.php**
```php
// Tout le monde peut voir tous les biens
public function viewAny(User $user): bool
{
    return true;
}
// Seuls les 'admin' peuvent supprimer un bien
public function restore(User $user, Property $property): bool
{
    return $user->role === 'admin';
}
```
On ajoute la Policy dans **app\Providers\AuthServiceProvider.php**
```php
protected $policies = [
    Property::class => PropertyPolicy::class
];
```
On peut vérifier dans un controller si l'utilisateur a le droit
```php
// Droit de supprimer un bien
Auth::user()->can('delete', $property)
// Droit de voir tous les biens
Auth::user()->can('viewAny', Property::class)
// Permet de vérifier le droit + retourner une exception
$this->authorize('delete', $property)
```
On peut utiliser la Policy de façon générale dans **\Admin\PropertyController.php**
```php
public function __construct()
{
    $this->authorizeResource(Property::class, 'property');
}
```
On peut vérifier les correspondances entre les fonctions de Policy et celles du controller [ici](https://laravel.com/docs/10.x/authorization#authorizing-resource-controllers)

On crée une Policy pour les images
```
php artisan make:policy PicturePolicy --model=Picture
```

On peut utiliser une Policy dans **web.php** via un middleware
```php
middleware('can:delete, picture')
// On peut raccourcir aussi comme ceci
can('delete', 'picture')
```
On peut utiliser une condition sur des droits dans blade
```php
// On affiche le bouton si on a le droit delete
@can("delete", $property)
    <form action="{{ route('admin.property.destroy', $property) }}" method="POST">
        @csrf
        @method("delete")
        <button class="btn btn-danger">Supprimer</button>
    </form>
@endcan
```

## Service Provider
Si on crée un fichier avec la classe Weather on va pouvoir l'appeler et utiliser les fonctions grace à l'injection de dépendances mais on sera bloqué si le contructeur doit recevoir un ou plusieurs paramètres. On se sert alors de Service Provider.

Dans **app\Providers\AppServiceProvider.php**
```php
public function register(): void
{
    $this->app->singleton('weather', fn () => new Weather('demo'));
}
// Appelle dans le controller
app('weather');
// Autre méthode
$this->app->singleton(Weather::class, fn () => new Weather('demo'));
app(Weather::class)
// Autre méthode
$this->app->singleton(Weather::class, fn () => new Weather('demo'));
public function index(Weather $weather)
{
    dd($weather);
}
// Autre méthode
public function __construct(private Weather $weather)
{
}

public function index()
{
    dd($this->weather);
}
```
On peut aussi créer un component **Components\Weather.php** et on modifie **weather.blade.php**
```
php artisan make:component Weather
```
Des services existent déjà par défaut pour récupérer des informations
```php
// Récupère les informations de l'utilisateur authentifié
public function index(AuthManager $auth)
$auth->user()
// Récupère les informations des cookies
\Illuminate\Cookie\CookieJar
// On peut aussi appeler le AuthManager
app('auth');
app(\Illuminate\Auth\AuthManager::class);
```

### Pour résumer
Un service provider est un registre où l'on fait correspondre à une clé un objet particulier et que l'on peut récupérer :
- en utilisant la fonction app()
- en utilisant l'injection de dépendance
On utilise les facades qui sont un raccourci pour aller plus vite pour accéder aux services provider :
```php
// Auth retourne la même chose que la 2e ligne
\Illuminate\Support\Facades\Auth::user();
app('auth')->user();
```
Grace aux facades on peut aussi faire :
```php
use Facades\App\Weather;
public function index()
{
    dd(Weather::isSunnyTomorrow());
}
```

## Les évènements
On ajoute un évènement et un listener lié
```
php artisan make:event ContactRequestEvent
php artisan make:listener ContactListener --event=ContactRequestEvent
```
On peut implémenter ShouldQueue si on veut rendre le listener asynchrone et effectuer l'action en tâche de fond si elle prend du temps
```php
class ContactListener implements ShouldQueue
```

On peut utiliser des suscriber pour s'abonner à plusieurs events en créant :
[ContactEventSuscriber.php](app%2FListeners%2FContactEventSuscriber.php)

### Résumé
Lorsqu'il se passe des choses spécifiques dans l'application il est conseillé de créer des évènements.

## Notifications
Si on veut envoyer des notifications sur plusieurs canaux on utiliser les notifications de Laravel. Si on n'utilise que les mails, on n'utilisera de préférence Mailable uniquement.
```
php artisan make:notification ContactRequestNotification
```
On crée une table qui va stocker toutes les notifications
```
php artisan notifications:table
php artisan migrate
```
On peut récupérer toutes les notifications d'un utilisateur par la suite
```php
$user = User::first();
dd($user->notifications);
// Notifications non lues par l'utilisateur
dd($user->unreadNotifications);
// Passer une notification à "lue"
dd($user->unreadNotifications[0]->markAsRead());
// Passer toutes les notifications à "lue"
$user->unreadNotifications->markAsRead()
// Suppression des notifications
$user->unreadNotifications()->delete()
```

## Internionalisation
Pour gérer la traduction des textes on crée un fichier **lang/fr/property.php** pour le français et on ajoute les références
```php
<h4>{{ __('property.contact_title') }}</h4>
```
```php
return [
    'contact_title' => 'Inéressé par ce bien ?'
];
```
On peut aussi publier les fichiers de traductions afin de gérer les messages d'erreurs dans **lang/en**
```
php artisan lang:publish
```
On se rend par la suite sur [https://laravel-lang.com/installation.html](https://laravel-lang.com/installation.html) et on ajoute la langue française
```
composer require --dev laravel-lang/common
php artisan lang:add fr
```
On ajouter des traductions à la volet
```php
'attributes'           => [
    'firstname'                => 'Prénom',
]
```
On peut aussi gérer les traduction de phrase complète à partir de l'anglais au lieu d'une clé
```json
{
    "Interested in this property :title ?": "Inéressé par ce bien :title ?"
}
```
```php
<h4>{{ __('Interested in this property :title ?', ['title' => $property->title]) }}</h4>
```
On peut utiliser les 2 méthodes en parrallèle 

## Les filtes d'attente
Permet de faire la partie de traitement lourd qui peut prendre du temps en tâche de fond sans faire attendre le rechargement d'une page.
```dotenv
# On change QUEUE_CONNECTION pour utiliser la base de données pour les files d'attente dans **.env*
QUEUE_CONNECTION=database
```
On crée la table qui correspond
```
php artisan queue:table
php artisan migrate
```
On ajoute implements ShouldQueue dans **ContactRequestNotification**
```php
class ContactRequestNotification extends Notification implements ShouldQueue
```
On peut visualiser les informations sur les files qu'on peut laisser ouvert. Quand on utilise une file d'attente, on stockera une ligne dans la table **jobs**
```
php artisan queue:work -v
```
Pour debuger c'est mieux d'utiliser
```
php artisan queue:listen
```
Cela permet d'actualiser quand on change le code

Si la touche échoue, l'utilisateur ne le verra pas mais on le verra dans la console avec "FAIL" et on ajoutera une ligne dans la table **failed_jobs** en capturant l'exception. Pour afficher la liste des jobs en erreur :
```
php artisan queue:failed
```
On peut relancer l'exécution :
```
php artisan queue:retry [id]
php artisan queue:retry all
```
On peut oublier une tache échouée
```
php artisan queue:forget [id]
php artisan queue:flush
```
Si on utilise Redis (non fait dans le tuto) on peut gérer les tâches avec [Laravel Horizon](https://laravel.com/docs/10.x/horizon)

On peut sinon créer un Job pour gérer les tâches asyncrhones
```
php artisan make:job DemoJob
```
Une fois **DemoJob** configuré, on peut l'utiliser dans le controller
```php
DemoJob::dispatch($property);
// On peut ajouter un délai
DemoJob::dispatch($property)->delay(now()->addSeconds(10));
```

## Api Resource
Lorsque l'on envoie un objet sur Laravel il est converti automatiquement en json, mais cela peut exposer la plupart des champs. Pour celà on peut créer une ressource API
```
php artisan make:resource PropertyResource
php artisan make:controller Api/PropertyController
```
On peut ainsi configurer les liens dans **api.php** et configurer l'envoi des données
```php
// api.php
Route::get('/biens', [\App\Http\Controllers\Api\PropertyController::class, 'index']);


// PropertyController
class PropertyController extends Controller
{
    public function index()
    {
        return PropertyResource::collection(Property::limit(5)->get());
    }
}

// PropertyResource
// Si on utilise les fonctions magiques
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
    ];
}
// Sinon
/**
 * @property Property $resource
 */
class PropertyResource extends JsonResource
{
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
            'options' => $this->resource->options,
        ];
    }
}
```
Si on veut limiter les informations envoyées par un élément en relation, on crée une nouvelle ressource
```
php artisan make:resource OptionResource
```
On peut ainsi tout configurer
```php
// OptionResource
public function toArray(Request $request): array
{
    return [
        'id' => $this->resource->id,
        'name' => $this->resource->name,
    ];
}

// PropertyResource
public function toArray(Request $request): array
{
    return [
        'id' => $this->resource->id,
        'title' => $this->resource->title,
        'options' => OptionResource::collection($this->resource->options),
    ];
}
// On peut conditionner l'affichage d'éléments envoyés
public function toArray(Request $request): array
{
    return [
        'id' => $this->resource->id,
        'title' => $this->resource->title,
        'price' => $this->when(false, $this->resource->price),
        'options' => OptionResource::collection($this->resource->options),
    ];
}
// Utilisation de l'eager loading
// Api/PropertyController
// On précharge les options dans la requête
return PropertyResource::collection(Property::limit(5)->with('options')->get());

// PropertyResource
public function toArray(Request $request): array
{
    return [
        'id' => $this->resource->id,
        'title' => $this->resource->title,
        'price' => $this->when(false, $this->resource->price),
        'options' => OptionResource::collection($this->whenLoaded('options')),
    ];
}
```
On peut aussi faire la PropertyResource sur une entrée en particulier
```php
// Api/PropertyController
return new PropertyResource(Property::find(1));
```
On peut utiliser le nom que l'on veut pour préfixer les données envoyées à la place de "data" en ajoutant une propriété. Ne marche pas pour le renvoi de collection.

```php
public static $wrap = "property";
```
On peut utiliser la pagination dans la ressource pour aussi récupérer les métadonnées
```php
// Api/PropertyController
return PropertyResource::collection(Property::paginate(5));
```
On peut aussi utiliser des ressources de type Collection (voir vidéo sur API Resource à partir de 12:00)...


## Tester avec Laravel
On peut lancer un test avec la commande
```
php artisan test
```

On va retrouver dans le dossier tests 2 sous-dossiers :
- Feature : va contenir des tests fonctionnels où l'application va avoir été démarré, donc à tester dans l'application. Tests un peu plus larges.
- Unit : contient les tests unitaires. On va tester des unités de code.

Pour créer nos propres tests :
1. On va modifier le fichier [phpunit.xml](phpunit.xml).
On décommente les 2 lignes pour ne pas vider la BDD en lançant les tests
```xml
 <env name="DB_CONNECTION" value="sqlite"/> 
 <env name="DB_DATABASE" value=":memory:"/> 
```
2. On ajoute une ligne dans [ExampleTest.php](tests%2FFeature%2FExampleTest.php)
```php
use RefreshDatabase;
```

3. On peut effectuer des tests :
```php
public function test_the_application_returns_a_successful_response(): void
{
    $response = $this->get('/');

    $response->assertStatus(200);
//    On vérifie qu'on a bien le texte suivant dans la page
    $response->assertSee('Agence lorem ipsum');
} 
```

4. On peut créer des tests pour créer [PropertyTest.php](tests%2FFeature%2FPropertyTest.php) :
```
php artisan make:test PropertyTest
```

5. On peut aussi créer des tests pour tester des API [WeatherTest.php](tests%2FFeature%2FWeatherTest.php)

6. Création d'un test unitaire [WeatherTest.php](tests%2FUnit%2FWeatherTest.php)
```
php artisan make:test WeatherTest --unit
```
