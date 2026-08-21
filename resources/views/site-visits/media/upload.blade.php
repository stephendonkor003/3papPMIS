<form method="POST" enctype="multipart/form-data" action="{{ route('site-visits.media.store', $visit) }}">
    @csrf

    <input type="file" name="file" required>
    <button class="btn btn-sm btn-secondary">Upload</button>
</form>
