import './bootstrap';

import focus from '@alpinejs/focus';

// Livewire bundles and starts its own Alpine instance; registering plugins
// here avoids a second "Detected multiple instances of Alpine running" instance.
document.addEventListener('alpine:init', () => {
    Alpine.plugin(focus);
});
