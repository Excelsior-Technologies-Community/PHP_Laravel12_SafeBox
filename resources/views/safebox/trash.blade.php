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

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold">🗑 SafeBox Trash</h2>
            <small class="text-muted">
                Restore or permanently delete your secrets.
            </small>
        </div>

        <a href="{{ route('safebox.index') }}"
            class="btn btn-primary">
            ← Back to Dashboard
        </a>

    </div>

    {{-- Trash Table --}}
    <div class="card shadow">

        <div class="card-header bg-dark text-white">
            Deleted Secrets
        </div>

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th width="70">#</th>

                        <th>Title</th>

                        <th>Secret</th>

                        <th>Status</th>

                        <th>Deleted At</th>

                        <th width="220">Action</th>

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

                            {{ Crypt::decryptString($item->secret) }}

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

                            {{ $item->deleted_at->format('d M Y h:i A') }}

                        </td>

                        <td>

                            <div class="d-flex gap-2">

                                {{-- Restore --}}
                                <form
                                    method="POST"
                                    action="{{ route('safebox.restore',$item->id) }}">

                                    @csrf
                                    @method('PUT')

                                    <button
                                        class="btn btn-success btn-sm">

                                        ♻ Restore

                                    </button>

                                </form>

                                {{-- Permanent Delete --}}
                                <form
                                    method="POST"
                                    action="{{ route('safebox.forceDelete',$item->id) }}"
                                    onsubmit="return confirm('Permanently delete this secret?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-danger btn-sm">

                                        ❌ Delete Forever

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="6"
                            class="text-center text-muted py-4">

                            🗑 Trash is empty.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">

            <small>

                Showing

                {{ $data->firstItem() ?? 0 }}

                to

                {{ $data->lastItem() ?? 0 }}

                of

                {{ $data->total() }}

                deleted records

            </small>

            {{ $data->links() }}

        </div>

    </div>

</div>

@endsection