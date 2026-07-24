<x-layouts.app>
    <h1>{{ $page->title }}</h1>

    @sanitize($page->content)
</x-layouts.app>
