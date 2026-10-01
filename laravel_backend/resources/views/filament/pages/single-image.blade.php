@php
    $state = $getState();

    if (is_string($state)) {
        $state = stripslashes($state);
        $decoded = json_decode($state, true);
        $images = (json_last_error() === JSON_ERROR_NONE) ? $decoded : [$state];
    } elseif (is_array($state)) {
        $images = $state;
    } else {
        $images = [];
    }

    $first = $images[0] ?? null;

    // ✅ FIXED URL handling
    if ($first && !str_starts_with($first, 'http')) {
        $first = url('/' . ltrim($first, '/'));
    }
@endphp

@if($first)
    <img src="{{ $first }}" style="width:80px;height:80px;object-fit:cover;border-radius:6px;" />
@else
    <span>No Image</span>
@endif