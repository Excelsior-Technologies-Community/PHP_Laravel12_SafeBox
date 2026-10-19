@extends('layouts.app')

@section('content')

<div class="container-fluid pb-5">

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Header Banner --}}
    <div class="card bg-dark text-white shadow-sm border-0 mb-4">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="bi bi-shield-lock-fill text-warning me-2"></i>SafeBox Vault & Security Manager
                </h3>
                <p class="text-light text-opacity-75 mb-0 small">
                    Securely manage your encrypted passwords, credit cards, API keys, and private credentials with AES-256 military-grade encryption.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- Vault PIN Status & Lock/Unlock Buttons --}}
                @if(!$hasVaultPin)
                    <button type="button" class="btn btn-warning fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#setupPinModal">
                        <i class="bi bi-shield-plus me-1"></i> Setup Master PIN
                    </button>
                @elseif($isVaultUnlocked)
                    <div class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 d-flex align-items-center gap-2">
                        <i class="bi bi-unlock-fill"></i>
                        <span>Vault Unlocked (<span id="autoLockTimer">10:00</span>)</span>
                    </div>
                    <form method="POST" action="{{ route('safebox.lock-vault') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-lock-fill me-1"></i> Lock Vault Now
                        </button>
                    </form>
                @else
                    <div class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-3 py-2 d-flex align-items-center gap-2">
                        <i class="bi bi-lock-fill"></i>
                        <span>Vault Locked</span>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm fw-semibold shadow-sm" onclick="openPinModal()">
                        <i class="bi bi-key-fill me-1"></i> Unlock with PIN
                    </button>
                @endif

                {{-- Global Password Generator Button --}}
                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#generatorModal">
                    <i class="bi bi-dice-5-fill me-1"></i> Password Generator
                </button>

                <a href="{{ route('safebox.export') }}" class="btn btn-success shadow-sm">
                    <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Export CSV
                </a>

                <a href="{{ route('safebox.trash') }}" class="btn btn-outline-light shadow-sm">
                    <i class="bi bi-trash3-fill me-1"></i> Trash
                </a>
            </div>
        </div>
    </div>

    {{-- Security Audit & Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3 col-xl-2">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Total Secrets</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3 col-xl-2">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Active Secrets</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3 col-xl-2">
            <a href="{{ route('safebox.index', ['filter' => 'expired']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 border-start border-danger border-4 h-100 {{ request('filter') === 'expired' ? 'bg-danger bg-opacity-10' : '' }}">
                    <div class="card-body p-3">
                        <span class="text-danger small fw-semibold text-uppercase">Expired Secrets</span>
                        <h3 class="fw-bold text-danger mb-0 mt-1">{{ $stats['expired'] }}</h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-md-3 col-xl-2">
            <a href="{{ route('safebox.index', ['filter' => 'expiring_soon']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 {{ request('filter') === 'expiring_soon' ? 'bg-warning bg-opacity-10' : '' }}">
                    <div class="card-body p-3">
                        <span class="text-warning text-dark small fw-semibold text-uppercase">Expiring Soon (≤7d)</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['expiring_soon'] }}</h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-md-3 col-xl-2">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-danger small fw-semibold text-uppercase">Weak Passwords</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1">{{ $stats['weak'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3 col-xl-2">
            <div class="card shadow-sm border-0 border-start border-secondary border-4 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Locked / Archived</span>
                    <h3 class="fw-bold text-secondary mb-0 mt-1">{{ $stats['locked'] + $stats['archived'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Filter Pills & Search Form --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                {{-- Categories Quick Filter Pills --}}
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('safebox.index') }}" class="btn btn-sm {{ !request('category') && !request('filter') ? 'btn-dark' : 'btn-outline-secondary' }}">
                        All ({{ $stats['total'] }})
                    </a>
                    <a href="{{ route('safebox.index', ['category' => 'Password']) }}" class="btn btn-sm {{ request('category') === 'Password' ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="bi bi-key-fill me-1"></i> Passwords ({{ $stats['password_count'] }})
                    </a>
                    <a href="{{ route('safebox.index', ['category' => 'Card']) }}" class="btn btn-sm {{ request('category') === 'Card' ? 'btn-success' : 'btn-outline-success' }}">
                        <i class="bi bi-credit-card-fill me-1"></i> Bank Cards ({{ $stats['card_count'] }})
                    </a>
                    <a href="{{ route('safebox.index', ['category' => 'API_Key']) }}" class="btn btn-sm {{ request('category') === 'API_Key' ? 'btn-info text-white' : 'btn-outline-info' }}">
                        <i class="bi bi-cpu-fill me-1"></i> API Keys ({{ $stats['api_count'] }})
                    </a>
                    <a href="{{ route('safebox.index', ['category' => 'Note']) }}" class="btn btn-sm {{ request('category') === 'Note' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                        <i class="bi bi-journal-text me-1"></i> Secure Notes ({{ $stats['note_count'] }})
                    </a>
                </div>

                {{-- Search & Filter Controls --}}
                <form method="GET" action="{{ route('safebox.index') }}" class="d-flex align-items-center gap-2 flex-grow-1 flex-md-grow-0">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search title, category, status..." value="{{ request('search') }}">
                        <select name="status" class="form-select form-select-sm" style="max-width: 130px;">
                            <option value="">All Statuses</option>
                            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Locked" {{ request('status') === 'Locked' ? 'selected' : '' }}>Locked</option>
                            <option value="Archived" {{ request('status') === 'Archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                        <button class="btn btn-dark" type="submit"><i class="bi bi-search"></i></button>
                        <a href="{{ route('safebox.index') }}" class="btn btn-outline-secondary" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add New Secret Form (Accordion) --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 py-3" data-bs-toggle="collapse" data-bs-target="#addNewSecretCollapse" style="cursor: pointer;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-plus-circle-fill text-success me-2"></i>Add New Secret
                </h5>
                <span class="badge bg-light text-dark border"><i class="bi bi-chevron-down"></i> Expand / Collapse Form</span>
            </div>
        </div>

        <div id="addNewSecretCollapse" class="collapse {{ $errors->any() ? 'show' : '' }}">
            <div class="card-body border-top">
                <form method="POST" action="{{ route('safebox.store') }}" id="addSecretForm">
                    @csrf

                    <div class="row g-3">
                        {{-- 1. Category Selector --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Category / Secret Type *</label>
                            <select name="category" id="createCategorySelect" class="form-select" required onchange="toggleCategoryFields('create')">
                                <option value="Password" {{ old('category') === 'Password' ? 'selected' : '' }}>🔐 Login / Password</option>
                                <option value="Card" {{ old('category') === 'Card' ? 'selected' : '' }}>💳 Bank Card / CVV</option>
                                <option value="API_Key" {{ old('category') === 'API_Key' ? 'selected' : '' }}>🔑 API Key & Secret</option>
                                <option value="Note" {{ old('category') === 'Note' ? 'selected' : '' }}>📝 Secure Note</option>
                            </select>
                        </div>

                        {{-- 2. Title --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Title / Account Name *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Google Workspace, AWS Production, Chase Card" value="{{ old('title') }}" required>
                        </div>

                        {{-- 3. Status --}}
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="Active" {{ old('status') === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Locked" {{ old('status') === 'Locked' ? 'selected' : '' }}>Locked</option>
                                <option value="Archived" {{ old('status') === 'Archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>

                        {{-- 4. Expiration Date --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Expiration Date (Optional)</label>
                            <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                        </div>

                        {{-- DYNAMIC CATEGORY FIELDS --}}
                        {{-- For Password: Username & Website URL --}}
                        <div class="col-md-6 create-category-field create-field-password">
                            <label class="form-label fw-semibold">Username / Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="meta_username" class="form-control" placeholder="admin@domain.com or johndoe" value="{{ old('meta_username') }}">
                            </div>
                        </div>
                        <div class="col-md-6 create-category-field create-field-password">
                            <label class="form-label fw-semibold">Website / Login URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                <input type="url" name="meta_url" class="form-control" placeholder="https://app.example.com/login" value="{{ old('meta_url') }}">
                            </div>
                        </div>

                        {{-- For Card: Holder, Card Number, Expiry, CVV --}}
                        <div class="col-md-4 create-category-field create-field-card d-none">
                            <label class="form-label fw-semibold">Card Holder Name</label>
                            <input type="text" name="meta_card_holder" class="form-control" placeholder="JOHN DOE" value="{{ old('meta_card_holder') }}">
                        </div>
                        <div class="col-md-4 create-category-field create-field-card d-none">
                            <label class="form-label fw-semibold">Card Number (16 Digits)</label>
                            <input type="text" name="meta_card_number" class="form-control" placeholder="4532 •••• •••• 8899" value="{{ old('meta_card_number') }}">
                        </div>
                        <div class="col-md-2 create-category-field create-field-card d-none">
                            <label class="form-label fw-semibold">Expiry Date</label>
                            <input type="text" name="meta_card_expiry" class="form-control" placeholder="MM/YY" value="{{ old('meta_card_expiry') }}">
                        </div>
                        <div class="col-md-2 create-category-field create-field-card d-none">
                            <label class="form-label fw-semibold">CVV</label>
                            <input type="password" name="meta_card_cvv" class="form-control" placeholder="•••" maxlength="4" value="{{ old('meta_card_cvv') }}">
                        </div>

                        {{-- For API Key: Client ID & Environment --}}
                        <div class="col-md-6 create-category-field create-field-api d-none">
                            <label class="form-label fw-semibold">API Key / Client ID</label>
                            <input type="text" name="meta_api_key" class="form-control" placeholder="pk_live_51M..." value="{{ old('meta_api_key') }}">
                        </div>
                        <div class="col-md-6 create-category-field create-field-api d-none">
                            <label class="form-label fw-semibold">Environment</label>
                            <select name="meta_environment" class="form-select">
                                <option value="Production">Production</option>
                                <option value="Staging">Staging</option>
                                <option value="Development">Development</option>
                            </select>
                        </div>

                        {{-- 5. Secret Input + Realtime Strength Meter + Inline Generator --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0" id="secretFieldLabel">
                                    Secret Value (Password / Private Token / Note Content) *
                                </label>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" onclick="generateInlinePassword('create')">
                                    <i class="bi bi-magic me-1"></i> Quick Generate Password
                                </button>
                            </div>

                            <div class="input-group">
                                <input type="text" name="secret" id="createSecretInput" class="form-control font-monospace" placeholder="Enter password or secret content..." value="{{ old('secret') }}" required oninput="evaluateStrength(this.value, 'createStrengthMeter', 'createStrengthText')">
                                <button type="button" class="btn btn-outline-secondary" onclick="generateInlinePassword('create')">
                                    <i class="bi bi-arrow-repeat"></i> Auto-Generate
                                </button>
                            </div>

                            {{-- Password Strength Progress Bar --}}
                            <div class="mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Password Strength Meter:</small>
                                    <small class="fw-bold" id="createStrengthText">None</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div id="createStrengthMeter" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Save Button --}}
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-success px-4 fw-semibold shadow-sm">
                                <i class="bi bi-shield-check me-1"></i> Save Secret Securely
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Secrets Stored Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-database-lock me-2 text-primary"></i>Stored Secrets ({{ $data->total() }})
            </h5>
            <small class="text-muted">
                {{ $isVaultUnlocked ? '🔓 Vault is unlocked' : '🔒 Secrets are masked for security' }}
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 200px;">Secret & Account Info</th>
                        <th style="min-width: 260px;">Secret Value (Password / Key)</th>
                        <th>Expiry / Validity</th>
                        <th>Status</th>
                        <th class="text-end" style="min-width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    @php
                        $meta = $item->decrypted_meta;
                        $isExp = $item->isExpired();
                        $isExpSoon = $item->isExpiringSoon();
                    @endphp
                    <tr class="{{ $isExp ? 'table-danger bg-opacity-25' : '' }}">
                        <td class="text-muted fw-semibold">
                            {{ $loop->iteration + ($data->firstItem() - 1) }}
                        </td>

                        {{-- Title & Meta details --}}
                        <td>
                            <div class="d-flex align-items-start gap-2">
                                {{-- Category Icon Badge --}}
                                @if($item->category === 'Password')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle p-2 rounded-3" title="Password">
                                        <i class="bi bi-key-fill fs-6"></i>
                                    </span>
                                @elseif($item->category === 'Card')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle p-2 rounded-3" title="Bank Card">
                                        <i class="bi bi-credit-card-fill fs-6"></i>
                                    </span>
                                @elseif($item->category === 'API_Key')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle p-2 rounded-3" title="API Key">
                                        <i class="bi bi-cpu-fill fs-6"></i>
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle p-2 rounded-3" title="Secure Note">
                                        <i class="bi bi-journal-text fs-6"></i>
                                    </span>
                                @endif

                                <div>
                                    <div class="fw-bold text-dark">{{ $item->title }}</div>

                                    {{-- Sub-details based on category --}}
                                    @if($item->category === 'Password')
                                        @if(!empty($meta['username']))
                                            <div class="small text-muted"><i class="bi bi-person me-1"></i>{{ $meta['username'] }}</div>
                                        @endif
                                        @if(!empty($meta['url']))
                                            <div class="small">
                                                <a href="{{ $meta['url'] }}" target="_blank" class="text-decoration-none text-primary">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i>Open URL
                                                </a>
                                            </div>
                                        @endif
                                    @elseif($item->category === 'Card')
                                        @if(!empty($meta['card_number']))
                                            <div class="small font-monospace text-muted">
                                                •••• {{ substr(preg_replace('/\s+/', '', $meta['card_number']), -4) }}
                                                @if(!empty($meta['card_expiry'])) (Exp: {{ $meta['card_expiry'] }}) @endif
                                            </div>
                                        @endif
                                    @elseif($item->category === 'API_Key')
                                        @if(!empty($meta['environment']))
                                            <span class="badge bg-light text-dark border small">{{ $meta['environment'] }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Secret Content & Reveal / Copy --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span id="secret-{{ $item->id }}"
                                      class="secret-text font-monospace {{ $isVaultUnlocked ? '' : 'user-select-none' }}"
                                      data-secret="{{ $item->decrypted_secret }}">
                                    ************
                                </span>

                                {{-- Show / Hide Toggle --}}
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary toggle-secret-btn"
                                        data-id="{{ $item->id }}"
                                        title="Show / Hide">
                                    <i class="bi bi-eye"></i>
                                </button>

                                {{-- Copy Button --}}
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary copy-secret-btn"
                                        data-secret="{{ $item->decrypted_secret }}"
                                        title="Copy Secret">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </td>

                        {{-- Expiry Status --}}
                        <td>
                            @if($item->expires_at)
                                @if($isExp)
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle me-1"></i>Expired ({{ $item->expires_at->format('d M Y') }})
                                    </span>
                                @elseif($isExpSoon)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-exclamation-circle me-1"></i>Expires in {{ round(now()->diffInDays($item->expires_at)) }} days
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $item->expires_at->format('d M Y') }}
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-light text-muted border">No Expiry</span>
                            @endif
                        </td>

                        {{-- Status Badge --}}
                        <td>
                            @if($item->status === 'Active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @elseif($item->status === 'Locked')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Locked</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Archived</span>
                            @endif
                        </td>

                        {{-- Actions: Versions, Edit, Delete --}}
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                {{-- Version History Button --}}
                                <button type="button"
                                        class="btn btn-outline-info"
                                        onclick="openVersionHistory({{ $item->id }})"
                                        title="Version History ({{ $item->versions_count }})">
                                    <i class="bi bi-clock-history"></i>
                                    @if($item->versions_count > 0)
                                        <span class="badge bg-info text-white ms-1">{{ $item->versions_count }}</span>
                                    @endif
                                </button>

                                {{-- Edit Button --}}
                                <button type="button"
                                        class="btn btn-outline-primary"
                                        onclick="openEditModal({{ json_encode([
                                            'id' => $item->id,
                                            'title' => $item->title,
                                            'category' => $item->category,
                                            'secret' => $item->decrypted_secret,
                                            'status' => $item->status,
                                            'expires_at' => $item->expires_at ? $item->expires_at->format('Y-m-d') : '',
                                            'meta' => $meta
                                        ]) }})"
                                        title="Edit Secret">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                {{-- Delete Form --}}
                                <form method="POST"
                                      action="{{ route('safebox.destroy', $item->id) }}"
                                      onsubmit="return confirm('Are you sure you want to move this secret to Trash?')"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-x fs-1 d-block mb-2 text-secondary"></i>
                            No secret records found in your vault.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($data->hasPages())
        <div class="card-footer bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">
                    Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} records
                </small>
                <div>
                    {{ $data->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

{{-- MODAL 1: Master Vault PIN Unlock Prompt --}}
<div class="modal fade" id="pinAuthModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-shield-lock-fill text-warning me-1"></i> Enter Master PIN
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="small text-muted mb-3">
                    Enter your 4-6 digit Master PIN to view or manage protected secrets.
                </p>

                <div id="pinAlertBox" class="alert alert-danger py-2 small d-none"></div>

                <input type="password" id="vaultPinInput" class="form-control text-center fs-4 fw-bold mb-3 font-monospace" placeholder="••••" maxlength="6" autofocus>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-warning fw-semibold" onclick="submitVerifyPin()">
                        <i class="bi bi-unlock-fill me-1"></i> Unlock Vault
                    </button>
                    <button type="button" class="btn btn-light btn-sm text-muted" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: Set Master Vault PIN Modal --}}
<div class="modal fade" id="setupPinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('safebox.set-pin') }}">
                @csrf
                <div class="modal-header bg-warning py-3">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-shield-lock me-1"></i> Configure Master Security PIN
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        This Master PIN secures all your encrypted passwords and credentials. Whenever you reveal or edit credentials, you will be prompted for this PIN.
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New 4-6 Digit PIN</label>
                        <input type="password" name="pin" class="form-control text-center fs-5 font-monospace" placeholder="••••" maxlength="6" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm PIN</label>
                        <input type="password" name="pin_confirmation" class="form-control text-center fs-5 font-monospace" placeholder="••••" maxlength="6" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-semibold">Save & Secure Vault</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 3: Edit Secret Modal with Version History snapshot trigger --}}
<div class="modal fade" id="editSecretModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="editSecretForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>Edit Secret
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category" id="editCategorySelect" class="form-select" required onchange="toggleCategoryFields('edit')">
                                <option value="Password">🔐 Login / Password</option>
                                <option value="Card">💳 Bank Card</option>
                                <option value="API_Key">🔑 API Key</option>
                                <option value="Note">📝 Secure Note</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Title *</label>
                            <input type="text" name="title" id="editTitleInput" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status *</label>
                            <select name="status" id="editStatusSelect" class="form-select" required>
                                <option value="Active">Active</option>
                                <option value="Locked">Locked</option>
                                <option value="Archived">Archived</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expiration Date (Optional)</label>
                            <input type="date" name="expires_at" id="editExpiresInput" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reason for Change (Saved in History)</label>
                            <input type="text" name="change_reason" class="form-control" placeholder="e.g. Regular 90-day password rotation">
                        </div>

                        {{-- Dynamic Fields for Edit --}}
                        <div class="col-md-6 edit-category-field edit-field-password">
                            <label class="form-label fw-semibold">Username / Email</label>
                            <input type="text" name="meta_username" id="editMetaUsername" class="form-control">
                        </div>
                        <div class="col-md-6 edit-category-field edit-field-password">
                            <label class="form-label fw-semibold">Website / Login URL</label>
                            <input type="url" name="meta_url" id="editMetaUrl" class="form-control">
                        </div>

                        <div class="col-md-4 edit-category-field edit-field-card d-none">
                            <label class="form-label fw-semibold">Card Holder Name</label>
                            <input type="text" name="meta_card_holder" id="editMetaCardHolder" class="form-control">
                        </div>
                        <div class="col-md-4 edit-category-field edit-field-card d-none">
                            <label class="form-label fw-semibold">Card Number</label>
                            <input type="text" name="meta_card_number" id="editMetaCardNumber" class="form-control">
                        </div>
                        <div class="col-md-2 edit-category-field edit-field-card d-none">
                            <label class="form-label fw-semibold">Expiry Date</label>
                            <input type="text" name="meta_card_expiry" id="editMetaCardExpiry" class="form-control">
                        </div>
                        <div class="col-md-2 edit-category-field edit-field-card d-none">
                            <label class="form-label fw-semibold">CVV</label>
                            <input type="password" name="meta_card_cvv" id="editMetaCardCvv" class="form-control" maxlength="4">
                        </div>

                        <div class="col-md-6 edit-category-field edit-field-api d-none">
                            <label class="form-label fw-semibold">API Key / Client ID</label>
                            <input type="text" name="meta_api_key" id="editMetaApiKey" class="form-control">
                        </div>
                        <div class="col-md-6 edit-category-field edit-field-api d-none">
                            <label class="form-label fw-semibold">Environment</label>
                            <select name="meta_environment" id="editMetaEnvironment" class="form-select">
                                <option value="Production">Production</option>
                                <option value="Staging">Staging</option>
                                <option value="Development">Development</option>
                            </select>
                        </div>

                        {{-- Secret Input with Strength meter --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Secret / Password Value *</label>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" onclick="generateInlinePassword('edit')">
                                    <i class="bi bi-dice-5 me-1"></i> Generate New Password
                                </button>
                            </div>
                            <input type="text" name="secret" id="editSecretInput" class="form-control font-monospace" required oninput="evaluateStrength(this.value, 'editStrengthMeter', 'editStrengthText')">

                            <div class="mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Password Strength:</small>
                                    <small class="fw-bold" id="editStrengthText">None</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div id="editStrengthMeter" class="progress-bar bg-danger" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Update & Snapshot Version</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 4: Version History Modal --}}
<div class="modal fade" id="versionHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clock-history text-info me-2"></i>Secret Version History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0" id="historyModalTitle">Loading...</h6>
                        <small class="text-muted" id="historyModalCategory"></small>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active Revision Protected</span>
                </div>

                <div class="border rounded p-3 mb-3 bg-light">
                    <span class="small text-muted fw-semibold">Current Active Secret:</span>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <input type="text" id="historyCurrentSecret" class="form-control form-control-sm font-monospace bg-white" readonly>
                        <button class="btn btn-sm btn-outline-primary" onclick="copyText(document.getElementById('historyCurrentSecret').value)">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>

                <h6 class="fw-bold mb-2">Previous Revisions (Audit Log):</h6>
                <div id="historyListContainer" class="list-group">
                    {{-- AJAX filled --}}
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 5: Standalone Password Generator Tool --}}
<div class="modal fade" id="generatorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-shield-shaded me-2"></i>Strong Password Generator
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Generated Output Display --}}
                <div class="input-group mb-3">
                    <input type="text" id="genResultInput" class="form-control font-monospace fs-5 text-center fw-bold bg-light" readonly>
                    <button class="btn btn-outline-primary" onclick="copyText(document.getElementById('genResultInput').value)">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>

                {{-- Strength Display --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted">Password Security Health:</small>
                        <small class="fw-bold text-success" id="genHealthText">Strong</small>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div id="genHealthBar" class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                </div>

                {{-- Controls --}}
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold mb-0">Password Length:</label>
                        <span class="badge bg-primary fs-6" id="genLengthDisplay">16</span>
                    </div>
                    <input type="range" class="form-range" id="genLengthSlider" min="8" max="64" value="16" oninput="document.getElementById('genLengthDisplay').innerText = this.value; generateCustomPassword();">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="genUpper" checked onchange="generateCustomPassword()">
                            <label class="form-check-label small" for="genUpper">Uppercase (A-Z)</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="genLower" checked onchange="generateCustomPassword()">
                            <label class="form-check-label small" for="genLower">Lowercase (a-z)</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="genNumbers" checked onchange="generateCustomPassword()">
                            <label class="form-check-label small" for="genNumbers">Numbers (0-9)</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="genSymbols" checked onchange="generateCustomPassword()">
                            <label class="form-check-label small" for="genSymbols">Special Symbols (!@#$)</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="button" class="btn btn-dark fw-semibold" onclick="generateCustomPassword()">
                        <i class="bi bi-arrow-repeat me-1"></i> Generate Password
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast Notification --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1090">
    <div id="actionToast" class="toast align-items-center text-bg-success border-0 shadow" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">
                📋 Copied to clipboard successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
    // State variables
    let isVaultUnlocked = {{ $isVaultUnlocked ? 'true' : 'false' }};
    let hasVaultPin = {{ $hasVaultPin ? 'true' : 'false' }};
    let pendingAction = null;
    let autoLockSeconds = 600; // 10 minutes

    document.addEventListener('DOMContentLoaded', function() {
        // Init password generator preview
        generateCustomPassword();

        // Auto-lock countdown timer if unlocked
        if (isVaultUnlocked) {
            startAutoLockTimer();
        }

        // Toggle Secret visibility button
        document.querySelectorAll('.toggle-secret-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const secretElem = document.getElementById('secret-' + id);

                if (!isVaultUnlocked) {
                    pendingAction = () => toggleSecretVisibility(btn, secretElem);
                    openPinModal();
                    return;
                }

                toggleSecretVisibility(btn, secretElem);
            });
        });

        // Copy Secret button
        document.querySelectorAll('.copy-secret-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const secret = this.dataset.secret;

                if (!isVaultUnlocked) {
                    pendingAction = () => copyText(secret);
                    openPinModal();
                    return;
                }

                copyText(secret);
            });
        });
    });

    // Toggle Secret Element Text
    function toggleSecretVisibility(btn, secretElem) {
        if (secretElem.innerText === '************') {
            secretElem.innerText = secretElem.dataset.secret;
            btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
            btn.classList.replace('btn-outline-secondary', 'btn-outline-danger');
        } else {
            secretElem.innerText = '************';
            btn.innerHTML = '<i class="bi bi-eye"></i>';
            btn.classList.replace('btn-outline-danger', 'btn-outline-secondary');
        }
    }

    // Copy helper
    function copyText(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            showToast('📋 Copied to clipboard successfully!');
        }).catch(() => {
            showToast('❌ Failed to copy to clipboard.');
        });
    }

    function showToast(msg) {
        document.getElementById('toastMessage').innerText = msg;
        const toastEl = document.getElementById('actionToast');
        const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
        toast.show();
    }

    // Master PIN Modal Handler
    function openPinModal() {
        if (!hasVaultPin) {
            const setupModal = new bootstrap.Modal(document.getElementById('setupPinModal'));
            setupModal.show();
            return;
        }

        document.getElementById('pinAlertBox').classList.add('d-none');
        document.getElementById('vaultPinInput').value = '';
        const modal = new bootstrap.Modal(document.getElementById('pinAuthModal'));
        modal.show();
        setTimeout(() => document.getElementById('vaultPinInput').focus(), 300);
    }

    // Submit PIN via AJAX
    function submitVerifyPin() {
        const pin = document.getElementById('vaultPinInput').value;
        const alertBox = document.getElementById('pinAlertBox');

        if (!pin) {
            alertBox.innerText = 'Please enter your Master PIN.';
            alertBox.classList.remove('d-none');
            return;
        }

        fetch("{{ route('safebox.verify-pin') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ pin: pin })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                isVaultUnlocked = true;
                const modalEl = document.getElementById('pinAuthModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                showToast('🔓 Vault unlocked successfully!');

                if (pendingAction) {
                    pendingAction();
                    pendingAction = null;
                }

                setTimeout(() => location.reload(), 800);
            } else {
                alertBox.innerText = data.message || 'Invalid PIN entered!';
                alertBox.classList.remove('d-none');
            }
        })
        .catch(err => {
            alertBox.innerText = 'Server connection error. Please try again.';
            alertBox.classList.remove('d-none');
        });
    }

    // Auto-Lock Timer Countdown
    function startAutoLockTimer() {
        const timerElem = document.getElementById('autoLockTimer');
        if (!timerElem) return;

        let interval = setInterval(() => {
            autoLockSeconds--;
            let mins = Math.floor(autoLockSeconds / 60);
            let secs = autoLockSeconds % 60;
            timerElem.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

            if (autoLockSeconds <= 0) {
                clearInterval(interval);
                fetch("{{ route('safebox.lock-vault') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(() => {
                    location.reload();
                });
            }
        }, 1000);
    }

    // Dynamic Category Form Switcher
    function toggleCategoryFields(mode) {
        const category = document.getElementById(mode + 'CategorySelect').value;
        const container = mode === 'create' ? document.getElementById('addSecretForm') : document.getElementById('editSecretForm');

        container.querySelectorAll(`.${mode}-category-field`).forEach(el => el.classList.add('d-none'));

        if (category === 'Password') {
            container.querySelectorAll(`.${mode}-field-password`).forEach(el => el.classList.remove('d-none'));
        } else if (category === 'Card') {
            container.querySelectorAll(`.${mode}-field-card`).forEach(el => el.classList.remove('d-none'));
        } else if (category === 'API_Key') {
            container.querySelectorAll(`.${mode}-field-api`).forEach(el => el.classList.remove('d-none'));
        }
    }

    // Edit Modal Open & Populate
    function openEditModal(item) {
        if (!isVaultUnlocked) {
            pendingAction = () => openEditModal(item);
            openPinModal();
            return;
        }

        const form = document.getElementById('editSecretForm');
        form.action = `/safebox/${item.id}`;

        document.getElementById('editTitleInput').value = item.title;
        document.getElementById('editCategorySelect').value = item.category;
        document.getElementById('editStatusSelect').value = item.status;
        document.getElementById('editExpiresInput').value = item.expires_at || '';
        document.getElementById('editSecretInput').value = item.secret;

        // Meta fields
        const meta = item.meta || {};
        document.getElementById('editMetaUsername').value = meta.username || '';
        document.getElementById('editMetaUrl').value = meta.url || '';
        document.getElementById('editMetaCardHolder').value = meta.card_holder || '';
        document.getElementById('editMetaCardNumber').value = meta.card_number || '';
        document.getElementById('editMetaCardExpiry').value = meta.card_expiry || '';
        document.getElementById('editMetaCardCvv').value = meta.card_cvv || '';
        document.getElementById('editMetaApiKey').value = meta.api_key || '';
        if (meta.environment) {
            document.getElementById('editMetaEnvironment').value = meta.environment;
        }

        toggleCategoryFields('edit');
        evaluateStrength(item.secret, 'editStrengthMeter', 'editStrengthText');

        const editModal = new bootstrap.Modal(document.getElementById('editSecretModal'));
        editModal.show();
    }

    // Version History Modal
    function openVersionHistory(id) {
        if (!isVaultUnlocked) {
            pendingAction = () => openVersionHistory(id);
            openPinModal();
            return;
        }

        const container = document.getElementById('historyListContainer');
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary spinner-border-sm"></div> Loading version history...</div>';

        const modal = new bootstrap.Modal(document.getElementById('versionHistoryModal'));
        modal.show();

        fetch(`/safebox/${id}/versions`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('historyModalTitle').innerText = data.title;
                    document.getElementById('historyModalCategory').innerText = `Category: ${data.category}`;
                    document.getElementById('historyCurrentSecret').value = data.current;

                    if (data.versions.length === 0) {
                        container.innerHTML = '<div class="alert alert-light text-muted small text-center mb-0">No previous revisions recorded for this secret. An automatic snapshot will be saved whenever you update this secret.</div>';
                        return;
                    }

                    let html = '';
                    data.versions.forEach((ver, idx) => {
                        html += `
                        <div class="list-group-item list-group-item-action p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-secondary">Revision #${data.versions.length - idx}</span>
                                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>${ver.created_at}</small>
                            </div>
                            <div class="fw-semibold text-dark small mb-2"><i class="bi bi-info-circle me-1"></i>${ver.reason}</div>
                            <div class="input-group input-group-sm">
                                <input type="password" id="verSecret-${ver.id}" class="form-control font-monospace" value="${ver.secret}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleVerSecret(${ver.id})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-primary" type="button" onclick="copyText('${ver.secret}')">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </div>
                        </div>`;
                    });
                    container.innerHTML = html;
                }
            })
            .catch(() => {
                container.innerHTML = '<div class="alert alert-danger small">Failed to load version history.</div>';
            });
    }

    function toggleVerSecret(id) {
        const input = document.getElementById(`verSecret-${id}`);
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    // Password Generation Algorithm
    function generatePasswordString(len, upper, lower, nums, syms) {
        let chars = '';
        if (upper) chars += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (lower) chars += 'abcdefghijklmnopqrstuvwxyz';
        if (nums) chars += '0123456789';
        if (syms) chars += '!@#$%^&*()_+~`|}{[]:;?><,./-=';

        if (!chars) chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        let res = '';
        const cryptoObj = window.crypto || window.msCrypto;
        const randomValues = new Uint32Array(len);
        cryptoObj.getRandomValues(randomValues);

        for (let i = 0; i < len; i++) {
            res += chars[randomValues[i] % chars.length];
        }
        return res;
    }

    // Inline generator in add/edit form
    function generateInlinePassword(mode) {
        const pwd = generatePasswordString(16, true, true, true, true);
        const input = document.getElementById(`${mode}SecretInput`);
        input.value = pwd;
        evaluateStrength(pwd, `${mode}StrengthMeter`, `${mode}StrengthText`);
        showToast('🎲 Strong password generated!');
    }

    // Standalone Modal Password Generator
    function generateCustomPassword() {
        const len = parseInt(document.getElementById('genLengthSlider').value);
        const upper = document.getElementById('genUpper').checked;
        const lower = document.getElementById('genLower').checked;
        const nums = document.getElementById('genNumbers').checked;
        const syms = document.getElementById('genSymbols').checked;

        const pwd = generatePasswordString(len, upper, lower, nums, syms);
        document.getElementById('genResultInput').value = pwd;
        evaluateStrength(pwd, 'genHealthBar', 'genHealthText');
    }

    // Realtime Password Strength Evaluator
    function evaluateStrength(password, barId, textId) {
        const bar = document.getElementById(barId);
        const text = document.getElementById(textId);
        if (!bar || !text) return;

        if (!password || password.length === 0) {
            bar.style.width = '0%';
            bar.className = 'progress-bar bg-danger';
            text.innerText = 'Empty';
            text.className = 'fw-bold text-muted';
            return;
        }

        let score = 0;
        if (password.length >= 8) score += 25;
        if (password.length >= 12) score += 15;
        if (password.length >= 16) score += 10;
        if (/[A-Z]/.test(password)) score += 15;
        if (/[a-z]/.test(password)) score += 10;
        if (/[0-9]/.test(password)) score += 15;
        if (/[^a-zA-Z0-9]/.test(password)) score += 10;

        if (score < 40) {
            bar.style.width = '25%';
            bar.className = 'progress-bar bg-danger';
            text.innerText = 'Weak';
            text.className = 'fw-bold text-danger';
        } else if (score < 65) {
            bar.style.width = '50%';
            bar.className = 'progress-bar bg-warning';
            text.innerText = 'Fair';
            text.className = 'fw-bold text-warning text-dark';
        } else if (score < 85) {
            bar.style.width = '75%';
            bar.className = 'progress-bar bg-info';
            text.innerText = 'Good';
            text.className = 'fw-bold text-info';
        } else {
            bar.style.width = '100%';
            bar.className = 'progress-bar bg-success';
            text.innerText = 'Strong';
            text.className = 'fw-bold text-success';
        }
    }
</script>

@endsection