// Single IIFE version of table-edit.js: clean checkbox behavior
(function () {
    // Inline table editor. Requires each table row's first data column to be the record id.
    function ajaxPost(url, data) {
        return fetch(url, { method: 'POST', body: data }).then(async r => {
            const text = await r.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('ajaxPost: response was not JSON', text);
                return { success: false, error: 'Non-JSON response from server', _raw: text };
            }
        });
    }

    function makeEditableRow(tr) {
        const cells = Array.from(tr.querySelectorAll('td'));
        // allow the first cell (id) to become editable as well
        for (let i = 0; i < cells.length; i++) {
            const cell = cells[i];
            // do not convert action/icon/button cells
            if (cell.querySelector('button') || cell.querySelector('i')) continue;
            // handle anchor (mailto) by using its text as the editable value
            let text = '';
            const a = cell.querySelector('a');
            if (a) text = a.textContent.trim(); else text = cell.textContent.trim();
            const input = document.createElement('input');
            input.type = 'text';
            input.value = text;
            input.style.width = '100%';
            input.dataset.original = text;
            cell.innerHTML = '';
            cell.appendChild(input);
        }
    }

    function readEditableRow(tr) {
        const cells = Array.from(tr.querySelectorAll('td'));
        const values = [];
        for (let i = 0; i < cells.length; i++) {
            const cell = cells[i];
            const input = cell.querySelector('input[type="text"]');
            if (input) values.push(input.value.trim()); else {
                const cb = cell.querySelector('.row-select-cb');
                if (cb && cb.value) values.push(cb.value.trim()); else values.push(cell.textContent.trim());
            }
        }
        return values;
    }

    document.addEventListener('DOMContentLoaded', function () {
        // mapping from API table name -> ordered list of DB column names matching table header order
        const tableColumnMap = {
            'customers': ['id', 'firstName', 'lastName', 'email', 'phone', 'total_spent'],
            'items': ['item_id', 'name', 'category', 'wholesale_price', 'retail_price', 'quantity'],
            'suppliers': ['id', 'company_name', 'first_name', 'last_name', 'email', 'phone'],
            'sales': ['item_id', 'item_name', 'price', 'quantity', 'discount']
        };

        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tableId = btn.getAttribute('data-table');
                const table = document.getElementById(tableId);
                if (!table) return;
                const tbody = table.tBodies[0];

                // If already in edit mode, treat this click as Cancel (toggle)
                if (tbody.dataset.editMode) {
                    exitEditMode(table);
                    return;
                }

                enterEditMode(table);
            });
        });

        function enterEditMode(table) {
            const tbody = table.tBodies[0];
            tbody.dataset.editMode = '1';
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // Save original HTML for each row so we can restore it later
            rows.forEach(r => r.dataset.originalHtml = r.innerHTML);

            // toolbar
            const toolbar = document.createElement('div');
            toolbar.className = 'table-edit-toolbar';
            toolbar.innerHTML = '<button class="te-btn save-selected" title="Save Selected" aria-label="Save Selected">\n<img src="assets/save.png" alt="save" />\n</button>\n<button class="te-btn delete-selected" title="Delete Selected" aria-label="Delete Selected">\n<img src="assets/delete.png" alt="delete" />\n</button>\n<button class="te-btn cancel-edit" title="Cancel" aria-label="Cancel">\n<img src="assets/cancel.png" alt="cancel" />\n</button>\n';
            table.parentNode.insertBefore(toolbar, table.nextSibling);

            // create checkbox for each row
            rows.forEach(r => createRowCheckbox(r));

            // actions
            toolbar.querySelector('.save-selected').addEventListener('click', async () => await saveSelected(table));
            toolbar.querySelector('.delete-selected').addEventListener('click', async () => await deleteSelected(table));
            toolbar.querySelector('.cancel-edit').addEventListener('click', () => exitEditMode(table));
        }

        function exitEditMode(table) {
            const tbody = table.tBodies[0];
            // remove toolbar
            const tb = table.parentNode.querySelector('.table-edit-toolbar');
            if (tb) tb.remove();
            // restore original HTML for rows that still have originalHtml saved
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.forEach(r => {
                if (r.dataset.originalHtml) {
                    r.innerHTML = r.dataset.originalHtml;
                    delete r.dataset.originalHtml;
                }
            });
            // remove any remaining row-select checkboxes to clean up UI
            const leftoverCbs = table.querySelectorAll('.row-select-cb');
            leftoverCbs.forEach(cb => cb.remove());
            // clear edit flag
            delete tbody.dataset.editMode;
        }

        function createRowCheckbox(r) {
            const idCell = r.cells[0];
            // ensure we remove old checkbox if exists
            const existing = idCell.querySelector('.row-select-cb');
            if (existing) existing.remove();
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.className = 'row-select-cb';
            // set checkbox value to the row id text so we can read it reliably
            const idText = idCell.textContent.trim();
            cb.value = idText;
            idCell.prepend(cb);
            cb.addEventListener('change', () => {
                if (cb.checked) makeEditableRow(r); else restoreRow(r);
            });
            return cb;
        }

        function restoreRow(r) {
            // restore original HTML (do not re-create checkbox here)
            const hasOriginal = !!r.dataset.originalHtml;
            if (hasOriginal) {
                r.innerHTML = r.dataset.originalHtml;
            } else {
                // If no original saved, try to replace inputs with their original values
                r.querySelectorAll('input').forEach(inp => {
                    const td = inp.parentNode;
                    td.textContent = inp.dataset.original || inp.value;
                });
            }
            // re-add checkbox so it remains visible while edit mode is still active
            const cb = createRowCheckbox(r);
            if (cb) {
                // make sure checkbox value matches the current id cell text
                const idCell = r.cells[0];
                const idText = idCell ? idCell.textContent.trim() : '';
                cb.value = idText;
                cb.checked = false; // ensure it's unchecked after restore
            }
        }

        async function saveSelected(table) {
            const tbody = table.tBodies[0];
            const selected = tbody.querySelectorAll('.row-select-cb:checked');
        for (const cb of selected) {
                const row = cb.closest('tr');
            const values = readEditableRow(row);
            const id = values[0];
            const origId = cb.value; // original id stored on checkbox
                const headers = Array.from(table.querySelectorAll('thead th')).map(h => h.textContent.trim());
                const fd = new FormData();
                // normalize table id to API table names (server expects plural 'customers')
                const rawTable = table.id.replace('-table', '');
                const apiTable = (function (t) {
                    if (t === 'customer') return 'customers';
                    if (t === 'item' || t === 'items') return 'items';
                    if (t === 'supplier' || t === 'suppliers') return 'suppliers';
                    if (t === 'sale' || t === 'sales') return 'sales';
                    return t;
                })(rawTable);
                fd.append('table', apiTable);
                fd.append('id', origId);
                // if the first column (id) was edited, send the new_id separately
                if (id !== origId) fd.append('new_id', id);
                // prefer explicit mapping from tableColumnMap so keys match API whitelist
                const colMap = tableColumnMap[apiTable] || null;
                if (colMap) {
                    // colMap expected to be ordered similarly to headers; skip id at index 0
                    for (let i = 1; i < colMap.length && i < values.length; i++) {
                        const dbCol = colMap[i];
                        fd.append(dbCol, values[i]);
                    }
                } else {
                    for (let i = 1; i < headers.length && i < values.length; i++) {
                        const col = headers[i].replace(/\s+/g, '_').toLowerCase();
                        fd.append(col, values[i]);
                    }
                }
                const res = await ajaxPost('api/update_record.php', fd);
                if (res && res.success) {
                    // replace only text inputs with their values
                    row.querySelectorAll('input[type="text"]').forEach(inp => {
                        const td = inp.parentNode;
                        td.textContent = inp.value;
                    });
                    // keep the checkbox visible while edit mode is active; uncheck it
                    const checkbox = row.querySelector('.row-select-cb');
                    if (checkbox) checkbox.checked = false;
                    // update saved originalHtml to reflect current row state (checkbox removed)
                    row.dataset.originalHtml = row.innerHTML;
                } else {
                    alert('Failed to save: ' + (res.error || 'unknown'));
                }
            }
            // if no more checkboxes remain, exit edit mode to remove toolbar and clean UI
            const remaining = table.querySelectorAll('.row-select-cb');
            if (!remaining || remaining.length === 0) exitEditMode(table);
        }

        async function deleteSelected(table) {
            if (!confirm('Delete selected rows? This will remove them from the database.')) return;
            const tbody = table.tBodies[0];
            const selected = tbody.querySelectorAll('.row-select-cb:checked');
            for (const cb of selected) {
                const row = cb.closest('tr');
                const cbInRow = row.querySelector('.row-select-cb');
                const id = (cbInRow && cbInRow.value) ? cbInRow.value.trim() : row.cells[0].textContent.trim();
                const fd = new FormData();
                const rawTable = table.id.replace('-table', '');
                const apiTable = (function (t) {
                    if (t === 'customer') return 'customers';
                    if (t === 'item' || t === 'items') return 'items';
                    if (t === 'supplier' || t === 'suppliers') return 'suppliers';
                    if (t === 'sale' || t === 'sales') return 'sales';
                    return t;
                })(rawTable);
                fd.append('table', apiTable);
                fd.append('id', id);
                const res = await ajaxPost('api/delete_record.php', fd);
                if (res && res.success) {
                    row.remove();
                } else {
                    alert('Failed to delete: ' + (res.error || 'unknown'));
                }
            }
            // if no more checkboxes remain, exit edit mode to remove toolbar and clean UI
            const remaining = table.querySelectorAll('.row-select-cb');
            if (!remaining || remaining.length === 0) exitEditMode(table);
        }

        function makeEditableRow(tr) {
            const cells = Array.from(tr.querySelectorAll('td'));
            for (let i = 1; i < cells.length; i++) {
                const cell = cells[i];
                if (cell.querySelector('button') || cell.querySelector('i')) continue;
                let text = '';
                const a = cell.querySelector('a');
                if (a) text = a.textContent.trim(); else text = cell.textContent.trim();
                const input = document.createElement('input');
                input.type = 'text';
                input.value = text;
                input.style.width = '100%';
                input.dataset.original = text;
                cell.innerHTML = '';
                cell.appendChild(input);
            }
        }

        function readEditableRow(tr) {
            const cells = Array.from(tr.querySelectorAll('td'));
            const values = [];
            for (let i = 0; i < cells.length; i++) {
                const cell = cells[i];
                const input = cell.querySelector('input[type="text"]');
                if (input) values.push(input.value.trim()); else {
                    const cb = cell.querySelector('.row-select-cb');
                    if (cb && cb.value) values.push(cb.value.trim()); else values.push(cell.textContent.trim());
                }
            }
            return values;
        }
    });
})();
