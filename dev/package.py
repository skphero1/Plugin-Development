"""Build the installable plugin archives without development dependencies."""
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile
import hashlib
import re

root = Path(__file__).resolve().parent.parent
version = re.search(r'^ \* Version: ([0-9.]+)$', (root / 'atelier-builder.php').read_text(), re.M).group(1)
dist = root / 'dist'
dist.mkdir(exist_ok=True)
files = ['atelier-builder.php', 'README.md', 'LICENSE', 'assets/editor.js', 'assets/editor.css']
versioned = dist / f'atelier-builder-{version}.zip'
if versioned.exists():
    raise SystemExit(f'{versioned.name} already exists; preserve releases and bump the version for changes.')
with ZipFile(versioned, 'w', ZIP_DEFLATED) as archive:
    for name in files:
        archive.write(root / name, 'atelier-builder/' + name)
with ZipFile(versioned) as archive:
    assert archive.testzip() is None
latest = dist / 'atelier-builder.zip'
latest.write_bytes(versioned.read_bytes())
checksums = dist / 'SHA256SUMS'
checksums.write_text(''.join(f'{hashlib.sha256(p.read_bytes()).hexdigest()}  {p.name}\n' for p in sorted(dist.glob('*.zip'))))
print(f'Packaged Atelier {version}: {len(files)} files. Checksums written to dist/SHA256SUMS.')
