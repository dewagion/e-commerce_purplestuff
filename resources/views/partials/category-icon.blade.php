@props([
    'icon' => 'star',
    'class' => 'h-7 w-7',
])

@php
    $icon = strtolower(trim((string) $icon));
@endphp

@if($icon === 'bag')
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12l-1 12H7L6 7z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7a3 3 0 116 0"/>
    </svg>

@elseif($icon === 'star')
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3.5 2.78 5.63 6.22.91-4.5 4.39 1.06 6.2L12 17.77 6.44 20.63l1.06-6.2L3 10.04l6.22-.91L12 3.5z"/>
    </svg>

@elseif($icon === 'album')
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <rect x="5" y="5" width="14" height="14" rx="2" stroke-width="1.8"/>
        <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
    </svg>

@elseif($icon === 'hoodie')
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9a4 4 0 118 0l2 2v8H6v-8l2-2z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 13h4"/>
    </svg>

@elseif($icon === 'bolt')
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 2 5 14h6l-1 8 8-12h-6l1-8z"/>
    </svg>

@elseif($icon === 'card')
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <rect x="4" y="6" width="16" height="12" rx="2" stroke-width="1.8"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14l2-2 2 2 3-3 2 2"/>
    </svg>

@elseif($icon === 'shirt')
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 4l3 2 3-2 4 3-2 4h-2v9H9v-9H7L5 7l4-3z"/>
    </svg>

@elseif($icon === 'tshirt')
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 5l4 2 4-2 3 3-2 4h-2v7H9v-7H7L5 8l3-3z"/>
    </svg>

@else
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <circle cx="12" cy="12" r="8" stroke-width="1.8"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l2.5 2.5"/>
    </svg>
@endif