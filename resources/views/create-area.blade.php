<!DOCTYPE html>
<html>
<head>
    <title>Crear Area</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">

        <div class="container mx-auto p-6">
            
            <div>
                @livewire('create-area')
            </div>
        </div>
        <div class="mt-6">
            <a href="{{ route('manage') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md">Ir a Gestión de Fichas</a>
            <a href="{{ route('display') }}" class="ml-4 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md">Ver Pantalla de Espera</a>
        </div>
    </div>
    @livewireScripts
</body>
</html>
