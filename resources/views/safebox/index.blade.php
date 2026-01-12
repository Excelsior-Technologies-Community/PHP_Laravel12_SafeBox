@extends('layouts.app')

@section('content')

<h3 class="mb-3">🔐 My SafeBox</h3>

<!-- Add SafeBox Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="/safebox">
            @csrf

            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Secret</label>
                <input type="text" name="secret" class="form-control" required>
            </div>

            <button class="btn btn-primary">Save to SafeBox</button>
        </form>
    </div>
</div>

<!-- SafeBox List -->
<div class="card">
    <div class="card-header">
        Stored Secrets
    </div>

    <div class="card-body">
        @forelse($data as $item)
            <div class="border p-2 mb-2 d-flex justify-content-between align-items-center">
                <div>
                    <b>{{ $item->title }}</b><br>
                    <small>{{ Crypt::decryptString($item->secret) }}</small>
                </div>

                <form method="POST" action="/safebox/{{ $item->id }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        @empty
            <p>No data saved.</p>
        @endforelse
    </div>
</div>

@endsection
