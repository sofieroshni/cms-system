<?php
include('includes/config.php');
include('includes/database.php');
include('includes/functions.php');
// secure();

include('includes/header.php');

?>
<div class="container-fluid mt-5">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <input type="text" id="page-title" class="form-control w-25" placeholder="Sidens titel...">
            <button id="save-btn" class="btn btn-success">💾 Gem side</button>
        </div>
    </div>

    <div class="row">
        <!-- LIVE PREVIEW -->
        <div class="col-md-8">
            <h5 class="mb-3">Preview</h5>
            <div id="preview-canvas" class="border rounded p-3" style="min-height: 600px; background: #fff;"></div>
        </div>

        <!-- BLOKKE -->
        <div class="col-md-4">
            <h5 class="mb-3">Blokke</h5>
            <div class="block-item border rounded p-3 mb-2 bg-light" draggable="true" data-type="heading">Overskrift</div>
            <div class="block-item border rounded p-3 mb-2 bg-light" draggable="true" data-type="text">Tekstblok</div>
            <div class="block-item border rounded p-3 mb-2 bg-light" draggable="true" data-type="button">Knap</div>
            <div class="block-item border rounded p-3 mb-2 bg-light" draggable="true" data-type="divider">Adskiller</div>
        </div>
    </div>
</div>

<style>
    .block-item { cursor: grab; user-select: none; }
    .block-item:active { cursor: grabbing; }
    #preview-canvas.drag-over { background: #f0f8ff; outline: 2px dashed #0d6efd; }
    .canvas-block { position: relative; padding: 10px; margin-bottom: 8px; border: 1px solid transparent; }
    .canvas-block:hover { border: 1px dashed #ccc; }
    .block-toolbar { position: absolute; top: 2px; right: 2px; display: none; background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 2px 4px; }
    .canvas-block:hover .block-toolbar { display: flex; gap: 6px; align-items: center; }
    .block-toolbar span { cursor: pointer; }
    .block-toolbar input[type="color"] { width: 22px; height: 22px; padding: 0; border: none; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('preview-canvas');
    let blocks = [];
    let counter = 0;

    function newBlock(type) {
        counter++;
        const base = { id: 'b' + counter, type };
        switch (type) {
            case 'heading': return { ...base, text: 'Din overskrift her', color: '#000000' };
            case 'text': return { ...base, text: 'Din tekst her.', color: '#000000' };
            case 'button': return { ...base, text: 'Knaptekst', bg: '#0d6efd', color: '#ffffff' };
            case 'divider': return { ...base };
        }
    }

    function render() {
        canvas.innerHTML = '';
        if (blocks.length === 0) {
            canvas.innerHTML = '<p class="text-muted">Hiv blokke herover fra højre →</p>';
            return;
        }
        blocks.forEach(block => {
            const wrap = document.createElement('div');
            wrap.className = 'canvas-block';
            wrap.dataset.id = block.id;

            let inner = '';
            let toolbarExtra = '';

            if (block.type === 'heading' || block.type === 'text') {
                const tag = block.type === 'heading' ? 'h2' : 'p';
                inner = `<${tag} contenteditable="true" class="editable-text" style="color:${block.color}">${block.text}</${tag}>`;
                toolbarExtra = `<input type="color" class="color-picker" value="${block.color}" data-prop="color" title="Tekstfarve">`;
            } else if (block.type === 'button') {
                inner = `<button class="btn" contenteditable="true" style="background:${block.bg}; color:${block.color}; border:none;">${block.text}</button>`;
                toolbarExtra = `
                    <input type="color" class="color-picker" value="${block.bg}" data-prop="bg" title="Baggrund">
                    <input type="color" class="color-picker" value="${block.color}" data-prop="color" title="Tekstfarve">`;
            } else if (block.type === 'divider') {
                inner = `<hr>`;
            }

            wrap.innerHTML = `
                ${inner}
                <div class="block-toolbar">
                    ${toolbarExtra}
                    <span class="remove-block" title="Slet">✕</span>
                </div>`;
            canvas.appendChild(wrap);
        });
    }

    function findBlock(id) {
        return blocks.find(b => b.id === id);
    }

    document.querySelectorAll('.block-item').forEach(item => {
        item.addEventListener('dragstart', e => e.dataTransfer.setData('text/plain', item.dataset.type));
    });

    canvas.addEventListener('dragover', e => { e.preventDefault(); canvas.classList.add('drag-over'); });
    canvas.addEventListener('dragleave', () => canvas.classList.remove('drag-over'));
    canvas.addEventListener('drop', e => {
        e.preventDefault();
        canvas.classList.remove('drag-over');
        const type = e.dataTransfer.getData('text/plain');
        if (!type) return;
        blocks.push(newBlock(type));
        render();
    });

    canvas.addEventListener('click', e => {
        if (e.target.classList.contains('remove-block')) {
            const id = e.target.closest('.canvas-block').dataset.id;
            blocks = blocks.filter(b => b.id !== id);
            render();
        }
    });

    canvas.addEventListener('input', e => {
        if (e.target.classList.contains('editable-text')) {
            const id = e.target.closest('.canvas-block').dataset.id;
            findBlock(id).text = e.target.innerHTML;
        }
    });

    canvas.addEventListener('change', e => {
        const id = e.target.closest('.canvas-block')?.dataset.id;
        if (!id) return;
        const block = findBlock(id);

        if (e.target.classList.contains('color-picker')) {
            block[e.target.dataset.prop] = e.target.value;
            render();
        }
    });

    function buildHtml() {
        return blocks.map(block => {
            if (block.type === 'heading') return `<h2 style="color:${block.color}">${block.text}</h2>`;
            if (block.type === 'text') return `<p style="color:${block.color}">${block.text}</p>`;
            if (block.type === 'button') return `<button class="btn" style="background:${block.bg}; color:${block.color}; border:none;">${block.text}</button>`;
            if (block.type === 'divider') return `<hr>`;
        }).join('\n');
    }

    document.getElementById('save-btn').addEventListener('click', () => {
        const title = document.getElementById('page-title').value.trim();
        if (!title) { alert('Udfyld en titel til siden'); return; }
        if (blocks.length === 0) { alert('Tilføj mindst én blok'); return; }

        fetch('save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title: title, html: buildHtml() })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Side gemt!');
                window.location.href = 'pages.php';
            } else {
                alert('Fejl: ' + data.message);
            }
        });
    });

    render();
});
</script>

<?php
include('includes/footer.php');
?>