(() => {
  'use strict';
  const root = document.getElementById('atelier-app');
  const input = document.getElementById('atelier-layout');
  if (!root || !input) return;
  let blocks;
  try { blocks = JSON.parse(input.value || '[]'); } catch { blocks = []; }
  let selected = -1;
  let viewport = '100%';
  const make = (tag, text, cls) => {
    const el = document.createElement(tag);
    if (text) el.textContent = text;
    if (cls) el.className = cls;
    return el;
  };
  const button = (text, fn) => {
    const el = make('button', text); el.type = 'button'; el.onclick = fn; return el;
  };
  const persist = () => { input.value = JSON.stringify(blocks); input.dispatchEvent(new Event('change', { bubbles: true })); };
  const fresh = type => ({ type, content: { heading: 'Make something remarkable.', text: 'Your story starts here. Add a little personality to every page.', button: 'Explore more', image: 'Describe your image', spacer: '' }[type], url: '', align: 'left', color: '#222222', background: '#ffffff', size: type === 'heading' ? 48 : 20, padding: 24 });
  function render() {
    root.replaceChildren();
    const header = make('header', null, 'atelier-header');
    header.append(make('strong', '◈ ATELIER'), make('span', 'Your ideas. A canvas of their own.'));
    header.append(button('Desktop', () => { viewport = '100%'; canvas.style.maxWidth = viewport; }), button('Mobile', () => { viewport = '375px'; canvas.style.maxWidth = viewport; }));
    root.append(header);
    const workspace = make('div', null, 'atelier-workspace');
    const library = make('aside', null, 'atelier-library');
    library.append(make('h3', 'Build your page'), make('p', 'Choose a block to add to your canvas.'));
    ['heading', 'text', 'button', 'image', 'spacer'].forEach(type => library.append(button('+ ' + type[0].toUpperCase() + type.slice(1), () => { blocks.push(fresh(type)); selected = blocks.length - 1; persist(); render(); })));
    library.append(make('p', 'Drag blocks to reorder. Use WordPress Update or Publish to save.'));
    const canvasWrap = make('main', null, 'atelier-canvas-wrap');
    const canvas = make('div', null, 'atelier-canvas');
    canvas.style.maxWidth = viewport;
    if (!blocks.length) canvas.append(make('div', 'A fresh start. Add your first block →', 'atelier-empty'));
    blocks.forEach((block, i) => {
      const item = make('div', null, 'atelier-item' + (selected === i ? ' is-selected' : ''));
      item.tabIndex = 0; item.setAttribute('role', 'button'); item.setAttribute('aria-label', 'Select ' + block.type + ' block ' + (i + 1));
      item.draggable = true;
      item.ondragstart = e => e.dataTransfer.setData('text/plain', String(i));
      item.ondragover = e => e.preventDefault();
      item.ondrop = e => { e.preventDefault(); const from = Number(e.dataTransfer.getData('text/plain')); if (!Number.isInteger(from) || from < 0 || from >= blocks.length) return; blocks.splice(i, 0, blocks.splice(from, 1)[0]); selected = i; persist(); render(); };
      item.onclick = () => { selected = i; render(); };
      item.onkeydown = e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selected = i; render(); } };
      Object.assign(item.style, { textAlign: block.align, color: block.color, background: block.background, fontSize: block.size + 'px', padding: block.padding + 'px' });
      let child = make(block.type === 'heading' ? 'h2' : 'p', block.content);
      if (block.type === 'button') { child = make('span', block.content, 'atelier-preview-button'); child.style.background = block.color; }
      if (block.type === 'image') {
        child = make('div', 'Add an image URL in the inspector', 'atelier-image-placeholder');
        if (/^https?:\/\//i.test(block.url)) { child = make('img'); child.src = block.url; child.alt = block.content; }
      }
      if (block.type === 'spacer') { child = make('div', 'Spacer', 'atelier-spacer'); child.style.height = block.size + 'px'; }
      item.append(child); canvas.append(item);
    });
    canvasWrap.append(canvas);
    const inspector = make('aside', null, 'atelier-inspector');
    inspector.append(make('h3', 'Block settings'));
    if (selected < 0 || !blocks[selected]) inspector.append(make('p', 'Select a block to make it yours.'));
    else {
      const block = blocks[selected];
      const field = (title, key, type = 'text') => {
        const label = make('label', title); const control = make(type === 'textarea' ? 'textarea' : 'input');
        if (type !== 'textarea') control.type = type;
        control.value = block[key];
        if (type === 'number') { control.min = key === 'padding' ? 0 : 12; control.max = key === 'padding' ? 120 : 96; }
        control.onchange = () => { block[key] = type === 'number' ? Math.max(Number(control.min), Math.min(Number(control.max), Number(control.value))) : control.value; persist(); render(); };
        label.append(control); inspector.append(label);
      };
      field(block.type === 'image' ? 'Alternative text' : 'Content', 'content', 'textarea');
      if (['image', 'button'].includes(block.type)) field('URL (https://…)', 'url', 'url');
      field('Text color', 'color', 'color'); field('Background', 'background', 'color'); field('Size', 'size', 'number'); field('Padding', 'padding', 'number');
      const alignment = make('div');
      ['left', 'center', 'right'].forEach(align => alignment.append(button(align, () => { block.align = align; persist(); render(); })));
      inspector.append(alignment);
      inspector.append(button('Move up', () => { if (selected > 0) { [blocks[selected - 1], blocks[selected]] = [blocks[selected], blocks[selected - 1]]; selected--; persist(); render(); } }));
      inspector.append(button('Move down', () => { if (selected < blocks.length - 1) { [blocks[selected + 1], blocks[selected]] = [blocks[selected], blocks[selected + 1]]; selected++; persist(); render(); } }));
      inspector.append(button('Duplicate', () => { blocks.splice(selected + 1, 0, { ...block }); selected++; persist(); render(); }));
      inspector.append(button('Remove block', () => { blocks.splice(selected, 1); selected = -1; persist(); render(); }));
    }
    workspace.append(library, canvasWrap, inspector); root.append(workspace);
  }
  render();
})();
