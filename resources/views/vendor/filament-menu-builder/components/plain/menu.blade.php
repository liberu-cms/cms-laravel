<ul class="flex items-center space-x-4">
    @foreach($menuItems as $menuItem)
        @include('filament-menu-builder::components.plain.menu-item', ['item' => $menuItem])
    @endforeach
</ul>
