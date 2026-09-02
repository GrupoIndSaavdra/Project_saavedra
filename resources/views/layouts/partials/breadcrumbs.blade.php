@php
    $steps = $steps ?? [];
    $activeStep = $activeStep ?? 0;
    $onclickSteps = $onclickSteps ?? [];
@endphp

@if(count($steps) > 0)
    <div class="breadcrumbs-wrapper" style="display: flex; align-items: center; justify-content: space-between; width: 100%; margin-bottom: 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.15); padding-bottom: 8px; box-sizing: border-box;">
        <div class="breadcrumbs-steps" style="display: flex; align-items: center; gap: 8px; font-family: 'Poppins', sans-serif; font-size: 0.85em; font-weight: 600; color: rgba(255, 255, 255, 0.6);">
            @foreach($steps as $idx => $step)
                @php
                    $isActive = $idx === $activeStep;
                    $color = $isActive ? '#ffffff' : 'rgba(255, 255, 255, 0.55)';
                    $weight = $isActive ? '700' : '600';
                    $shadow = $isActive ? 'text-shadow: 0 0 10px rgba(255, 255, 255, 0.4);' : '';
                    $onclick = $onclickSteps[$idx] ?? null;
                    $cursor = $onclick ? 'cursor: pointer;' : 'cursor: default;';
                @endphp
                <span 
                    @if($onclick) onclick="{{ $onclick }}" @endif
                    style="color: {{ $color }}; font-weight: {{ $weight }}; {{ $shadow }} {{ $cursor }}">
                    {{ $step }}
                </span>
                @if(!$loop->last)
                    <span style="color: rgba(255, 255, 255, 0.3); margin: 0 2px;">➔</span>
                @endif
            @endforeach
        </div>
    </div>
@endif
