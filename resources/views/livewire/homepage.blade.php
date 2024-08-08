<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
</head>

<body>
    <div class="">
        <livewire:navigation />
        <main>
            <section class="featured-posts">
                @if ($featuredPosts->isEmpty())
                    <section class="py-8 flex justify-center">
                        <p>No featured posts yet.</p>
                    </section>
                @endif
                @foreach ($featuredPosts as $post)
                    <article class="post">
                        <h2>{{ $post->title }}</h2>
                        <p>{{ $post->summary }}</p>
                        <a href="{{ route('posts.show', $post->id) }}">Read More</a>
                    </article>
                @endforeach
            </section>
        </main>
        @livewire('footer')
    </div>
</body>

</html>
