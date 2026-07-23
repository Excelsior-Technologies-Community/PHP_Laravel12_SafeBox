@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}

        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold">
                🔐 SafeBox Dashboard
            </h2>

            <small class="text-muted">
                Store and manage your encrypted secrets.
            </small>
        </div>

        <div class="d-flex gap-2">

            <a href="{{ route('safebox.export') }}"
                class="btn btn-success">
                📥 Export CSV
            </a>

            <a href="{{ route('safebox.trash') }}"
                class="btn btn-dark">
                🗑 Trash
            </a>

        </div>

    </div>

    {{-- Statistics --}}
    <div class="row mb-4">

        <div class="col-md-3">

            <div class="card shadow border-0 bg-primary text-white">

                <div class="card-body">

                    <h6>Total Secrets</h6>

                    <h2>{{ $stats['total'] }}</h2>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow border-0 bg-success text-white">

                <div class="card-body">

                    <h6>Active</h6>

                    <h2>{{ $stats['active'] }}</h2>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow border-0 bg-warning text-dark">

                <div class="card-body">

                    <h6>Locked</h6>

                    <h2>{{ $stats['locked'] }}</h2>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow border-0 bg-secondary text-white">

                <div class="card-body">

                    <h6>Archived</h6>

                    <h2>{{ $stats['archived'] }}</h2>

                </div>

            </div>

        </div>

    </div>



    {{-- Add SafeBox --}}
    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <h5 class="mb-0">
                ➕ Add New Secret
            </h5>

        </div>

        <div class="card-body">

            <form method="POST"
                action="{{ route('safebox.store') }}">

                @csrf

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Title
                        </label>

                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            required>

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Secret
                        </label>

                        <input
                            type="text"
                            name="secret"
                            class="form-control"
                            required>

                    </div>

                    <div class="col-md-2 mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select">

                            <option value="Active">
                                Active
                            </option>

                            <option value="Locked">
                                Locked
                            </option>

                            <option value="Archived">
                                Archived
                            </option>

                        </select>

                    </div>

                    <div class="col-md-2 mb-3">

                        <label class="form-label invisible">
                            Save
                        </label>

                        <button type="submit" class="btn btn-success w-100">
                            Save
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- Search & Filter --}}
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="GET"
                action="{{ route('safebox.index') }}">

                <div class="row">

                    <div class="col-md-5">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search title or status..."
                            value="{{ request('search') }}">

                    </div>

                    <div class="col-md-3">

                        <select
                            name="status"
                            class="form-select">

                            <option value="">
                                All Status
                            </option>

                            <option value="Active"
                                {{ request('status')=='Active' ? 'selected':'' }}>
                                Active
                            </option>

                            <option value="Locked"
                                {{ request('status')=='Locked' ? 'selected':'' }}>
                                Locked
                            </option>

                            <option value="Archived"
                                {{ request('status')=='Archived' ? 'selected':'' }}>
                                Archived
                            </option>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <button class="btn btn-primary">
                            Search
                        </button>

                        <a href="{{ route('safebox.index') }}"
                            class="btn btn-secondary">
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- Data Table --}}
    <div class="card shadow-sm">

        <div class="card-header">

            <h5 class="mb-0">

                📋 Stored Secrets

            </h5>

        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead class="table-dark">

                    <tr>

                        <th>#</th>

                        <th>Title</th>

                        <th>Secret</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($data as $item)

                    <tr>

                        <td>
                            {{ $loop->iteration + ($data->firstItem() - 1) }}
                        </td>

                        <td>

                            {{ $item->title }}

                        </td>

                        <td>

                            <div class="d-flex align-items-center gap-2">

                                <span
                                    id="secret-{{ $item->id }}"
                                    class="secret-text"
                                    data-secret="{{ Crypt::decryptString($item->secret) }}">
                                    ************
                                </span>

                                {{-- Show / Hide --}}
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary toggle-secret"
                                    data-id="{{ $item->id }}"
                                    title="Show / Hide Secret">

                                    👁

                                </button>

                                {{-- Copy --}}
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary copy-secret"
                                    data-secret="{{ Crypt::decryptString($item->secret) }}"
                                    title="Copy Secret">

                                    📋

                                </button>

                            </div>

                        </td>

                        <td>

                            @if($item->status=='Active')

                            <span class="badge bg-success">
                                Active
                            </span>

                            @elseif($item->status=='Locked')

                            <span class="badge bg-warning text-dark">
                                Locked
                            </span>

                            @else

                            <span class="badge bg-secondary">
                                Archived
                            </span>

                            @endif

                        </td>

                        <td>

                            <form
                                method="POST"
                                action="{{ route('safebox.destroy',$item->id) }}"
                                onsubmit="return confirm('Delete this secret?')">

                                @csrf
                                @method('DELETE')

                                <button
                                    class="btn btn-danger btn-sm">

                                    Delete

                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td
                            colspan="5"
                            class="text-center text-muted">

                            No SafeBox records found.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer">

            <div class="d-flex justify-content-between align-items-center">

                <small>

                    Showing
                    {{ $data->firstItem() ?? 0 }}
                    to
                    {{ $data->lastItem() ?? 0 }}
                    of
                    {{ $data->total() }}
                    records

                </small>

                <div class="card-footer">

                    <nav>
                        <ul class="pagination justify-content-center mb-0">

                            @for ($i = 1; $i <= $data->lastPage(); $i++)

                                <li class="page-item {{ $data->currentPage() == $i ? 'active' : '' }}">

                                    <a class="page-link"
                                        href="{{ $data->url($i) }}">
                                        {{ $i }}
                                    </a>

                                </li>

                                @endfor

                        </ul>
                    </nav>

                </div>
            </div>

        </div>

    </div>

</div>

<!-- Copy Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3"
    style="z-index:1080">

    <div
        id="copyToast"
        class="toast align-items-center text-bg-success border-0"
        role="alert">

        <div class="d-flex">

            <div class="toast-body" id="copyToastBody">

                📋 Secret copied successfully!

            </div>
            <button
                type="button"
                class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast">
            </button>

        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Show / Hide Secret
        document.querySelectorAll('.toggle-secret').forEach(function(button) {

            button.addEventListener('click', function() {

                let id = this.dataset.id;

                let secret = document.getElementById('secret-' + id);

                if (secret.innerText === '************') {

                    secret.innerText = secret.dataset.secret;

                    this.innerHTML = '🙈';

                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-outline-danger');

                } else {

                    secret.innerText = '************';

                    this.innerHTML = '👁';

                    this.classList.remove('btn-outline-danger');
                    this.classList.add('btn-outline-secondary');

                }

            });

        });

        // Copy Secret
        document.querySelectorAll('.copy-secret').forEach(function(button) {

            button.addEventListener('click', function() {

                navigator.clipboard.writeText(this.dataset.secret)
                    .then(() => {

                        // Update toast message
                        document.getElementById('copyToastBody').innerHTML =
                            '📋 Secret copied successfully!';

                        // Show toast
                        const toastElement = document.getElementById('copyToast');

                        const toast = bootstrap.Toast.getOrCreateInstance(toastElement);

                        toast.show();

                    })
                    .catch(() => {

                        alert('Failed to copy secret.');

                    });

            });

        });

    });
</script>

@endsection