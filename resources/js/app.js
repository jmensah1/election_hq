import './bootstrap';

// Only import and start Alpine if Livewire hasn't already done so
// Livewire 3 bundles Alpine, so we check if it's already defined
if (!window.Alpine) {
    import('alpinejs').then((Alpine) => {
        window.Alpine = Alpine.default;
        Alpine.default.start();
    });
}
