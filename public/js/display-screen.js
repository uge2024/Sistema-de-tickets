document.addEventListener('DOMContentLoaded', () => {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }
        .blink {
            animation: blink 1s ease-in-out 3;
        }
    `;
    document.head.appendChild(style);

    Livewire.on('ticket-updated', ({ areaId, ticketNumber, areaName }) => {
        console.log('Ticket updated received:', { areaId, ticketNumber, areaName });

        const areaElement = document.getElementById(`area-${areaId}`);
        if (areaElement) {
            const ticketElement = areaElement.querySelector('.ticket-number');
            if (ticketElement) {
                ticketElement.classList.add('blink');
                console.log('Blink applied to:', ticketNumber);
            } else {
                console.log('Ticket element not found for area:', areaId);
            }
        } else {
            console.log('Area element not found:', areaId);
        }

        if (ticketNumber && areaName) {
            const utterance = new SpeechSynthesisUtterance(
                `Por favor, ficha ${ticketNumber}, diríjase al área ${areaName}.`
            );
            utterance.lang = 'es-ES';
            utterance.volume = 1;
            utterance.rate = 1;
            utterance.pitch = 1;

            const voices = window.speechSynthesis.getVoices();
            const spanishVoice = voices.find(voice => voice.lang.startsWith('es')) || voices[0];
            if (spanishVoice) {
                utterance.voice = spanishVoice;
                console.log('Using voice:', spanishVoice.name);
                window.speechSynthesis.speak(utterance);
            } else {
                console.log('No Spanish voice found, using default:', voices[0]?.name);
            }
        }
    });

    window.speechSynthesis.onvoiceschanged = () => {
        const voices = window.speechSynthesis.getVoices();
        console.log('Voces cargadas:', voices.map(v => v.lang));
    };

    if (window.speechSynthesis.getVoices().length === 0) {
        window.speechSynthesis.getVoices();
    }
});