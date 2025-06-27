<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantalla de Espera</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Alpine.js para las interacciones -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    @livewire('display-screen')
    
    @livewireScripts
    
    <!-- Inicializar Alpine después de Livewire -->
    <script>
        document.addEventListener('livewire:init', () => {
            console.log('Livewire y pantalla de display iniciados');
        });
    </script>
</body>
</html>