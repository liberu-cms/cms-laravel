@if($imageUrl)
    <img src="{{ Storage::url($imageUrl) }}" alt="Featured Image Preview" class="mt-2 rounded-lg shadow-md max-w-full h-auto" />
@endif