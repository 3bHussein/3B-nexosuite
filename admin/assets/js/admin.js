document.addEventListener('click', function (event) {
  const target = event.target.closest('[data-confirm]');
  if (target && !confirm(target.getAttribute('data-confirm'))) {
    event.preventDefault();
  }
});

function erpNumber(value) {
  const n = parseFloat(value || 0);
  return Number.isFinite(n) ? n : 0;
}

function recalcInvoiceBuilder() {
  const rows = document.querySelectorAll('[data-invoice-row]');
  let subtotal = 0;
  let tax = 0;
  rows.forEach((row) => {
    const qty = erpNumber(row.querySelector('[name="quantity[]"]')?.value);
    const price = erpNumber(row.querySelector('[name="unit_price[]"]')?.value);
    const taxRate = erpNumber(row.querySelector('[name="tax_rate[]"]')?.value);
    const line = qty * price;
    const taxValue = line * (taxRate / 100);
    subtotal += line;
    tax += taxValue;
    const lineTarget = row.querySelector('[data-line-total]');
    if (lineTarget) lineTarget.textContent = line.toFixed(2);
  });
  const discount = erpNumber(document.querySelector('[name="discount"]')?.value);
  const shipping = erpNumber(document.querySelector('[name="shipping"]')?.value);
  const total = Math.max(0, subtotal - discount + tax + shipping);
  const subtotalTarget = document.querySelector('[data-summary-subtotal]');
  const taxTarget = document.querySelector('[data-summary-tax]');
  const totalTarget = document.querySelector('[data-summary-total]');
  if (subtotalTarget) subtotalTarget.textContent = subtotal.toFixed(2);
  if (taxTarget) taxTarget.textContent = tax.toFixed(2);
  if (totalTarget) totalTarget.textContent = total.toFixed(2);
}

document.addEventListener('input', function (event) {
  if (event.target.closest('[data-invoice-builder]')) recalcInvoiceBuilder();
});

document.addEventListener('change', function (event) {
  const productSelect = event.target.closest('[data-product-select]');
  if (productSelect) {
    const option = productSelect.selectedOptions[0];
    const row = productSelect.closest('[data-invoice-row]');
    const desc = row?.querySelector('[name="description[]"]');
    const price = row?.querySelector('[name="unit_price[]"]');
    if (option && option.dataset.name && desc && !desc.value) desc.value = option.dataset.name;
    if (option && option.dataset.price && price && (!price.value || price.value === '0')) price.value = option.dataset.price;
    recalcInvoiceBuilder();
  }
});

document.addEventListener('click', function (event) {
  const add = event.target.closest('[data-add-invoice-row]');
  if (add) {
    event.preventDefault();
    const template = document.querySelector('#invoice-row-template');
    const target = document.querySelector('[data-invoice-rows]');
    if (template && target) {
      target.insertAdjacentHTML('beforeend', template.innerHTML);
      recalcInvoiceBuilder();
    }
  }
  const remove = event.target.closest('[data-remove-invoice-row]');
  if (remove) {
    event.preventDefault();
    const rows = document.querySelectorAll('[data-invoice-row]');
    if (rows.length > 1) remove.closest('[data-invoice-row]')?.remove();
    recalcInvoiceBuilder();
  }
});

document.addEventListener('DOMContentLoaded', recalcInvoiceBuilder);

document.addEventListener('input', function (event) {
  const search = event.target.closest('[data-table-search]');
  if (!search) return;
  const target = document.querySelector(search.getAttribute('data-table-search'));
  if (!target) return;
  const query = String(search.value || '').toLowerCase().trim();
  target.querySelectorAll('tbody tr').forEach((row) => {
    const text = String(row.textContent || '').toLowerCase();
    row.style.display = !query || text.includes(query) ? '' : 'none';
  });
});

document.addEventListener('click', function (event) {
  const toggle = event.target.closest('[data-toggle-panel]');
  if (!toggle) return;
  event.preventDefault();
  const selector = toggle.getAttribute('data-toggle-panel');
  const panel = selector ? document.querySelector(selector) : null;
  if (panel) panel.classList.toggle('d-none');
});

function syncRichEditor(textarea, editor) {
  textarea.value = editor.innerHTML;
}

function richEditorCommand(command, value = null) {
  document.execCommand(command, false, value);
}

function buildRichEditor(textarea) {
  if (!textarea || textarea.dataset.richReady === '1') return;
  textarea.dataset.richReady = '1';

  const shell = document.createElement('div');
  shell.className = 'rich-editor-shell';

  const toolbar = document.createElement('div');
  toolbar.className = 'rich-editor-toolbar';
  toolbar.innerHTML = `
    <button type="button" data-rich-command="bold" title="Bold"><strong>B</strong></button>
    <button type="button" data-rich-command="italic" title="Italic"><em>I</em></button>
    <button type="button" data-rich-command="underline" title="Underline"><u>U</u></button>
    <button type="button" data-rich-block="h2" title="Heading 2">H2</button>
    <button type="button" data-rich-block="h3" title="Heading 3">H3</button>
    <button type="button" data-rich-block="p" title="Paragraph">P</button>
    <button type="button" data-rich-command="insertUnorderedList" title="Bullet list">• List</button>
    <button type="button" data-rich-command="insertOrderedList" title="Numbered list">1. List</button>
    <button type="button" data-rich-link title="Insert link">Link</button>
    <button type="button" data-rich-command="removeFormat" title="Clear format">Clear</button>
  `;

  const editor = document.createElement('div');
  editor.className = 'rich-editor-surface';
  editor.contentEditable = 'true';
  editor.innerHTML = textarea.value || '<p></p>';

  textarea.parentNode.insertBefore(shell, textarea);
  shell.appendChild(toolbar);
  shell.appendChild(editor);
  shell.appendChild(textarea);
  textarea.classList.add('rich-editor-source');

  toolbar.addEventListener('click', (event) => {
    const commandButton = event.target.closest('[data-rich-command]');
    const blockButton = event.target.closest('[data-rich-block]');
    const linkButton = event.target.closest('[data-rich-link]');
    if (!commandButton && !blockButton && !linkButton) return;
    event.preventDefault();
    editor.focus();
    if (commandButton) {
      richEditorCommand(commandButton.getAttribute('data-rich-command'));
    }
    if (blockButton) {
      richEditorCommand('formatBlock', blockButton.getAttribute('data-rich-block'));
    }
    if (linkButton) {
      const url = window.prompt('Enter link URL');
      if (url) richEditorCommand('createLink', url);
    }
    syncRichEditor(textarea, editor);
  });

  editor.addEventListener('input', () => syncRichEditor(textarea, editor));
  editor.addEventListener('blur', () => syncRichEditor(textarea, editor));

  const form = textarea.closest('form');
  if (form) {
    form.addEventListener('submit', () => syncRichEditor(textarea, editor));
  }
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('textarea[data-rich-editor]').forEach(buildRichEditor);
});