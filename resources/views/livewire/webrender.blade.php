<x-guest-layout>
    <section>
        @if ($contents)
            @foreach ($contents as $element)
                {!! $element['content'] !!}
            @endforeach
        @else
            <p>No content found</p>
        @endif
    </section>
</x-guest-layout>
