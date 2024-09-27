<x-guest-layout>
        @if ($contents)
            @foreach ($contents as $element)
                @include('partials.elements.' . $element['name'])
            @endforeach
        @else
            <p>No content found</p>
        @endif
</x-guest-layout>
