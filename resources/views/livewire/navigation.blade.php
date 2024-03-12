<nav class="navigation" style="background-color: #f0f0f0; padding: 1rem 0;">
    <div class="container" style="max-width: 1200px; margin: auto; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('homepage') }}" style="font-weight: bold; text-decoration: none; color: #333;">CMS Laravel</a>
        <ul style="list-style: none; display: flex; gap: 20px; margin: 0; padding: 0;">
            <li><a href="{{ route('homepage') }}" style="text-decoration: none; color: #333;">Home</a></li>
            <li><a href="{{ route('about') }}" style="text-decoration: none; color: #333;">About</a></li>
            <li><a href="{{ route('blog') }}" style="text-decoration: none; color: #333;">Blog</a></li>
            <li><a href="{{ route('contact') }}" style="text-decoration: none; color: #333;">Contact</a></li>
        </ul>
    </div>
</nav>
