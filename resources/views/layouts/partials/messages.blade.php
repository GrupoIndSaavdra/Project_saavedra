@if (isset($errors) && count($errors) > 0)
    <div class="alert alert-danger custom-alert">
        <ul class="list-unstyled mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button class="close-alert">&times;</button>
    </div>
@endif

@if (session('success'))
    @php $data = session('success') @endphp
    @if (is_array($data))
        @foreach ($data as $message)
            <div class="alert alert-success custom-alert">
                <button class="close-alert">&times;</button>
                {{ $message }}
            </div>
        @endforeach
    @else
        <div class="alert alert-success custom-alert">
            <button class="close-alert">&times;</button>
            {{ $data }}
        </div>
    @endif
@endif

@if (session('error'))
    <div class="alert alert-danger custom-alert">
        <button class="close-alert">&times;</button>
        {{ session('error') }}
    </div>
@endif