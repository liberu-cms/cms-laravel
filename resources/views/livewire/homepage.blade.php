<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
</head>
<body>
   <div class="">
    @livewire('header')

    {{-- @livewire('navigation') --}}

    <main>
        <section class="featured-posts">
            
            @foreach($featuredPosts as $post)
                <article class="post">
                    <h2>{{ $post->title }}</h2>
                    <p>{{ $post->summary }}</p>
                    <a href="{{ route('posts.show', $post->id) }}">Read More</a>
                </article>
            @endforeach
        </section>
    </main>
   </div>

    {{-- @livewire('footer') --}}
</body>
</html>
