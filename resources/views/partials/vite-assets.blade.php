@php
    $viteEntryPoints = $entrypoints ?? ['resources/css/app.css', 'resources/js/app.js'];
    $hasViteHotReload = file_exists(public_path('hot'));
    $hasViteManifest = file_exists(public_path('build/manifest.json'));
@endphp

@if ($hasViteHotReload || $hasViteManifest)
    @vite($viteEntryPoints)
@endif
