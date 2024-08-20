<x-guest-layout>
    <section> 
        @foreach($contents as $element)
            {!! $element['content'] !!}
        @endforeach 
    </section>
</x-guest-layout>