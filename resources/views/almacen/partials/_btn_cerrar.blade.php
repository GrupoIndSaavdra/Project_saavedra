{{-- Reusable Premium Close Button with Hover Rotate Micro-Animation --}}
<button type="button" class="btn-cerrar btn-cerrar-alerta" 
    onclick="{{ $onclick ?? '' }}" 
    style="{{ $style ?? 'position: absolute; top: 25px; right: 25px; background: rgba(255, 255, 255, 0.18); border: 1.5px solid rgba(255, 255, 255, 0.45); border-radius: 50%; width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; padding: 0; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); cursor: pointer;' }}"
    @if(isset($id)) id="{{ $id }}" @endif>
    <img class="img-cerrar" src="{{ asset('images/cerrar.png') }}" alt="Cerrar" 
        style="width: 18px; height: 18px; filter: brightness(0) invert(1); transition: transform 0.25s ease;">
</button>
