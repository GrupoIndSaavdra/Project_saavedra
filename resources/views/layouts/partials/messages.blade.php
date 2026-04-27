@if (isset($errors) && count($errors) > 0)
    <div class="alert alert-danger custom-alert">
        <ul class="list-unstyled mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    @php $data = session('success') @endphp
    @if (is_array($data))
        @foreach ($data as $message)
            <div class="alert alert-success custom-alert">
                {{ $message }}
            </div>
        @endforeach
    @else
        <div class="alert alert-success custom-alert">
            {{ $data }}
        </div>
    @endif
@endif

@if (session('error'))
    <div class="alert alert-danger custom-alert">
        {{ session('error') }}
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning custom-alert">
        {{ session('warning') }}
    </div>
@endif