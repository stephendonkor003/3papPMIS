@props([
    'editRoute' => null,
    'deleteRoute' => null,
    'viewRoute' => null,
    'customActions' => null,
    'editText' => 'Edit',
    'deleteText' => 'Delete',
    'viewText' => 'View',
    'confirmDelete' => true,
    'deleteMessage' => 'Are you sure you want to delete this item?'
])

<div class="d-flex gap-1 align-items-center no-export">
    @if($viewRoute)
        <a href="{{ $viewRoute }}" class="btn btn-sm btn-outline-info" title="{{ $viewText }}">
            <i class="feather-eye"></i>
        </a>
    @endif

    @if($editRoute)
        <a href="{{ $editRoute }}" class="btn btn-sm btn-outline-primary" title="{{ $editText }}">
            <i class="feather-edit"></i>
        </a>
    @endif

    @if($deleteRoute)
        <form action="{{ $deleteRoute }}" method="POST" class="d-inline"
              @if($confirmDelete) onsubmit="return confirm('{{ $deleteMessage }}')" @endif>
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ $deleteText }}">
                <i class="feather-trash-2"></i>
            </button>
        </form>
    @endif

    @if($customActions)
        {{ $customActions }}
    @endif

    {{ $slot }}
</div>
