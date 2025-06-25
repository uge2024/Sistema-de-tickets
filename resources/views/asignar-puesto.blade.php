<!DOCTYPE html>
<html>
<head>
    <title>Asignar Puesto/title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">

        <div class="container mx-auto p-6">
            
            
            <x-app-layout>
                @livewire('assign-puesto-to-user')
            </x-app-layout>
        </div>
    </div>
    @livewireScripts
</body>
</html>
