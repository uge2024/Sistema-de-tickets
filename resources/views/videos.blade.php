<!DOCTYPE html>
<html>
<head>
    <title>Videos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">

        <div class="container mx-auto p-6">
            
            
            <x-app-layout>
                @livewire('manage-videos')

            </x-app-layout>
        </div>
    </div>
    @livewireScripts
</body>
</html>
