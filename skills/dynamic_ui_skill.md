# Real-time Table Updates & Dynamic UI Rendering (Avoid location.reload)

## The Problem
When performing CRUD operations (like creating folders, uploading PDFs, or deleting files), it is common practice to simply do `setTimeout(() => window.location.reload(), 1500);` after a successful API response to refresh the view. However, this causes a full page reload, leading to a slow and clunky user experience.

## The Solution
Instead of reloading the page, we use a **Local State Management Pattern** using `window` objects (like `window.estructura`, `window.historiales`, `window.todasLasOTs`, etc.) that act as our "Source of Truth".

### 1. Update the Backend (Controller)
Ensure your controller returns the necessary identifiers in the JSON response, so the frontend knows exactly what was created, deleted, or modified.

**Example for creating a folder:**
```php
if ($request->expectsJson()) {
    return response()->json([
        'success' => true,
        'message' => 'Carpeta creada correctamente.',
        'ot' => $otName,
        'clase' => $claseName
    ]);
}
```

**Example for linking alerts/records:**
```php
if ($request->expectsJson()) {
    return response()->json([
        'success' => true,
        'message' => 'Ayudas vinculadas correctamente.',
        'ayudasLinked' => $ayudasFinales,
        'ot' => $ot
    ]);
}
```

### 2. Update the Frontend (JavaScript)
In the `.then(data => { ... })` block of your `fetch` request, parse the identifiers sent by the controller and update the global `window.*` objects accordingly. Then, call the rendering functions to surgically update the DOM.

**Example for Folder Creation:**
```javascript
if (data.success) {
    mostrarNotificacion(data.message);
    
    // 1. Mutate local state
    if (data.ot && data.clase) {
        if (!window.estructura[data.ot]) window.estructura[data.ot] = [];
        if (!window.estructura[data.ot].includes(data.clase)) {
            window.estructura[data.ot].push(data.clase);
        }
    }
    
    // 2. Call surgical render functions (DO NOT RELOAD PAGE)
    if (typeof renderEstructuraTable === 'function') renderEstructuraTable();
    updateAdminUI();
    actualizarBadge(payload.param1, payload.param2);
    if (typeof loadAuditLog === 'function') loadAuditLog();
}
```

**Example for Deleting:**
```javascript
if (data.success) {
    mostrarNotificacion(data.message);
    
    // 1. Mutate local state
    if (window.estructura[folder.p1]) {
        window.estructura[folder.p1] = window.estructura[folder.p1].filter(c => c !== folder.p2);
        if (window.estructura[folder.p1].length === 0) delete window.estructura[folder.p1];
    }
    
    // 2. Refresh UI Components
    updateAdminUI();
    loadBadgeCounts();
    loadAuditLog();
    if (typeof renderEstructuraTable === 'function') renderEstructuraTable();
}
```

### Key Principles
1. **Never use `location.reload()`** if you can easily update the DOM dynamically.
2. **Never refetch the entire structure** from the server (`fetch(routes['doc.estructura'])`) unless absolutely necessary, as it can be very slow if there are many directories.
3. Always make sure the backend returns the specific identifiers (`ot`, `clase`, `proceso`, `ayudasLinked`) needed to update the `window.*` cache.
4. **Abstract rendering logic** into reusable functions like `renderEstructuraTable()` and `renderAlertasTable()` so that you can call them from anywhere in the script once the state is updated.
