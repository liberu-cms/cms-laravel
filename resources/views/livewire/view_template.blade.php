<x-guest-layout>
    <section>
        @if(session('contents'))
            @foreach(session('contents') as $content)
                <p>{{ $content->content }}</p>
            @endforeach
        @endif
    </section>
</x-guest-layout>