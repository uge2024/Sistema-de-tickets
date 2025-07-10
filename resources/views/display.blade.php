<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantalla de Espera</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
      
</head>
<body class="bg-gray-100">
    @livewire('display-screen')
    
    @livewireScripts
    
</body>
</html>