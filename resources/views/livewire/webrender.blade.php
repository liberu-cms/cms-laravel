<x-guest-layout>
    <div>
        @if ($contents)
            @foreach ($contents as $element)
                @include('partials.content-element', ['content' => $element['content']])
            @endforeach
        @else
            <p>No content found</p>
        @endif
    </div>
</x-guest-layout>
