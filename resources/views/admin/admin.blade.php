<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <style>
        @layer reset {
            button {
                all: unset;
            }
        }

        .htmx-indicator {
            display: none;
        }

        .htmx-request .htmx-indicator {
            display: inline-block;
        }

        .htmx-request.htmx-indicator {
            display: inline-block;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Agence</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            @php
                $route = request()->route()->getName();
            @endphp
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="{{ route('admin.property.index') }}" @class(['nav-link', 'active' => str_contains($route, 'property.')])>Gérer les biens</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.option.index') }}" @class(['nav-link', 'active' => str_contains($route, 'option.')])>Gérer les options</a>
                    </li>
                </ul>
                <div class="ms-auto">
                    @auth
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    @method('delete')
                                    <button class="nav-link">Se déconnecter</button>
                                </form>
                            </li>
                        </ul>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        @include('shared.flash')

        @yield('content')
    </div>
    <script>
        new TomSelect('select[multiple]', {plugins: {remove_button: {title: 'Supprimer'}}})
    </script>
</body>

</html>
