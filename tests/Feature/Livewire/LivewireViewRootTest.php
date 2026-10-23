<?php

test('Livewire component views start without a UTF-8 BOM so directives stay inside their component root', function () {
    $views = [
        'resources/views/livewire/owner/dashboard.blade.php',
        'resources/views/livewire/owner/drivers.blade.php',
        'resources/views/livewire/owner/inventory.blade.php',
        'resources/views/livewire/owner/reports.blade.php',
    ];

    foreach ($views as $view) {
        expect(file_get_contents(base_path($view)))->not->toStartWith("\xEF\xBB\xBF");
    }
});
