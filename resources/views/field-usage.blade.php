@props([
  'name',
  'label',
  'options' => '',
  'native' => null,
])

@php($field = $native ? "{$native}('<fg=blue>{$label}</>'" : "addField('<fg=blue>{$name}</>', '<fg=blue>{$label}</>'")

<fg=gray>$fields
    ->{!! $field !!}, [
{!! $options !!}
    ]);</>
