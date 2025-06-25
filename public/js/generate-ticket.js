document.addEventListener('DOMContentLoaded', () => {
    Livewire.on('ticket-generated', ({ areaId, ticketNumber }) => {
        const type = ticketNumber.startsWith('S') ? 'Tercera Edad' : 'Normal';
        alert(`Ficha generada (${type}): ${ticketNumber}`);
    });
});