<form method="post" action="{{ route('profile.destroy') }}">
    @csrf
    @method('delete')

    <p class="text-muted">
        Once your account is deleted, all of its resources and data will be permanently deleted.
    </p>

    <div class="mb-3">
        <label class="form-label text-danger">Confirm Password</label>
        <input type="password" name="password" class="form-control">
    </div>

    <button class="btn btn-danger">
        Delete Account
    </button>
</form>
