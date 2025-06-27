<!DOCTYPE html>
<html>
<head>
    <title>Llamar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">

        <div class="container mx-auto p-6">
            
            
            <x-app-layout>
                @livewire('call-ticket')

            </x-app-layout>
        </div>
    </div>
    @livewireScripts
</body>
</html>
