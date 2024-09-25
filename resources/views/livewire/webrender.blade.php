<x-guest-layout>
    <div>
        @if ($contents)
            @foreach ($contents as $element)
                <?php echo $element['content']; ?>
            @endforeach
        @else
            <p>No content found</p>
        @endif
    </div>
</x-guest-layout>
