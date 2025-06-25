<!DOCTYPE html>
<html>
<head>
    <title>Pantalla de Espera</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.2; }
        }
        .animate-blink {
            animation: blink 1s ease-in-out 3;
        }
        </style>
</head>
<body class="bg-gray-100">
    @livewire('display-screen')
    @livewireScripts
</body>
</html>