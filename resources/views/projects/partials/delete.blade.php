<div class="modal fade" id="deleteModal{{ $project->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Project</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete <strong>{{ $project->name }}</strong>?
                <br><br>
                This action cannot be undone.
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                <form method="POST" action="{{ route('projects.destroy', $project->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>

        </div>
    </div>
</div>
