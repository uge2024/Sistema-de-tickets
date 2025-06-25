<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Fichas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestión de Fichas</h1>
        @livewire('call-ticket')
        <div class="mt-6">
            <a href="{{ route('home') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md">Volver al Inicio</a>
        </div>

        
    </div>
    @livewireScripts
</body>
</html>