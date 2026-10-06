
import codecs
def mixed_decoder(exc):
    if isinstance(exc, UnicodeDecodeError):
        offending_byte = exc.object[exc.start:exc.end]
        return (offending_byte.decode('windows-1252'), exc.end)
    raise TypeError('dont know how to handle %r' % exc)
codecs.register_error('mixed', mixed_decoder)
f='resources/views/calidad/partials/sidebar_legend.blade.php'
raw=open(f,'rb').read()
text=raw.decode('utf-8', errors='mixed')
open(f,'w', encoding='utf-8').write(text)
print('Fixed!')

