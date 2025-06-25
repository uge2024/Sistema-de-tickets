<!DOCTYPE html>
<html lang="es">
<head>
    <title>Sistema de Fichas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Sistema de Fichas</h1>
        
        <!-- Sección para generar tickets -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            @livewire('manage-videos')
        </div>
        
        <!-- Botones de navegación -->
        <div class="flex justify-center gap-4">
            <a href="{{ route('manage') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-300 ease-in-out">
                Ir a Gestión de Fichas
            </a>
            <a href="{{ route('display') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-300 ease-in-out">
                Ver Pantalla de Espera
            </a>
        </div>
    </div>
    @livewireScripts
</body>
</html>