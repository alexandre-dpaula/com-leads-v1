(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const baseUrl = window.APP?.baseUrl || '';

    const modalBackdrop = document.getElementById('modal-backdrop');
    const leadModal = document.getElementById('lead-modal');
    const leadForm = leadModal?.querySelector('[data-lead-form]');
    const leadDeleteModal = document.getElementById('confirm-lead-delete');
    const stageModal = document.getElementById('stage-modal');
    const stageForm = stageModal?.querySelector('[data-stage-form]');
    const stageDeleteModal = document.getElementById('confirm-stage-delete');

    let currentLeadId = null;
    let currentStageId = null;

    const state = {
        dragCard: null,
        dragSourceStage: null,
        dragSourceIndex: null,
    };

    function buildUrl(path) {
        if (baseUrl) {
            return baseUrl.replace(/\/+$/, '') + path;
        }
        return path;
    }

    async function fetchJson(path, options = {}) {
        const response = await fetch(buildUrl(path), {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                ...options.headers,
            },
            ...options,
        });

        let payload = {};
        const text = await response.text();
        if (text) {
            try {
                payload = JSON.parse(text);
            } catch {
                payload = { message: text };
            }
        }

        if (!response.ok) {
            const error = new Error(payload.message || 'Erro ao processar a requisição.');
            error.payload = payload;
            error.status = response.status;
            throw error;
        }

        return payload;
    }

    function openModal(dialog) {
        if (!dialog) return;
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        }
        dialog.classList.remove('hidden');
        modalBackdrop?.classList.remove('hidden');
        const focusable = dialog.querySelector('input, button, select, textarea');
        focusable?.focus();
    }

    function closeModal(dialog) {
        if (!dialog) return;
        if (typeof dialog.close === 'function') {
            dialog.close();
        }
        dialog.classList.add('hidden');
        if (
            leadModal.classList.contains('hidden') &&
            leadDeleteModal.classList.contains('hidden') &&
            stageModal.classList.contains('hidden') &&
            stageDeleteModal.classList.contains('hidden')
        ) {
            modalBackdrop?.classList.add('hidden');
        }
    }

    function clearErrors(form) {
        form?.querySelectorAll('[data-error]').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });
    }

    function showErrors(form, errors = {}) {
        Object.entries(errors).forEach(([field, messages]) => {
            const errorEl = form.querySelector(`[data-error="${field}"]`);
            if (errorEl) {
                errorEl.textContent = messages[0] || 'Campo inválido.';
                errorEl.classList.remove('hidden');
            }
        });
    }

    function serializeForm(form) {
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = typeof value === 'string' ? value.trim() : value;
        });
        return data;
    }

    function resetLeadForm() {
        currentLeadId = null;
        if (!leadForm) return;
        leadForm.reset();
        clearErrors(leadForm);
        const deleteButton = leadForm.querySelector('[data-action="delete-lead"]');
        if (deleteButton) {
            deleteButton.hidden = true;
        }
        leadForm.querySelector('input[name="id"]').value = '';
    }

    function resetStageForm() {
        currentStageId = null;
        if (!stageForm) return;
        stageForm.reset();
        clearErrors(stageForm);
        stageForm.querySelector('input[name="id"]').value = '';
    }

    function updateStageCounters() {
        document.querySelectorAll('[data-stage]').forEach((stageCard) => {
            const counter = stageCard.querySelector('header p.text-xs');
            const leads = stageCard.querySelectorAll('[data-lead-id]');
            if (counter) {
                counter.textContent = `${leads.length} lead(s)`;
            }
        });
    }

    function renderLeadCard(lead) {
        const card = document.createElement('div');
        card.className = 'cursor-grab rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-indigo-200 hover:shadow';
        card.setAttribute('draggable', 'true');
        card.dataset.leadId = lead.id;
        card.dataset.stageId = lead.stage_id;
        card.dataset.position = lead.position;

        const tagsHtml = (lead.tags || '')
            .split(',')
            .map((tag) => tag.trim())
            .filter(Boolean)
            .map((tag) => `<span class="rounded bg-indigo-50 px-2 py-1 text-[10px] font-medium uppercase text-indigo-600">${escapeHtml(tag)}</span>`)
            .join('');

        const buttonsLead = {
            id: lead.id,
            name: lead.name,
            company: lead.company,
            email: lead.email,
            phone: lead.phone,
            value: lead.value,
            tags: lead.tags,
            notes: lead.notes,
            stage_id: lead.stage_id,
        };

        const updatedAt = lead.updated_at ? formatDateTime(lead.updated_at) : '';

        card.innerHTML = `
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-sm font-semibold text-slate-900">${escapeHtml(lead.name)}</p>
                    ${lead.company ? `<p class="text-xs text-slate-500">${escapeHtml(lead.company)}</p>` : ''}
                </div>
                <button
                    type="button"
                    class="text-xs text-indigo-600 hover:text-indigo-700"
                    data-action="edit-lead"
                    data-lead='${escapeHtml(JSON.stringify(buttonsLead))}'
                >
                    Detalhes
                </button>
            </div>
            <div class="mt-3 space-y-2 text-xs text-slate-600">
                ${lead.email ? `<p class="flex items-center gap-2"><span class="font-medium">E-mail:</span><a href="mailto:${escapeHtml(lead.email)}" class="text-indigo-600 hover:underline">${escapeHtml(lead.email)}</a></p>` : ''}
                ${lead.phone ? `<p class="flex items-center gap-2"><span class="font-medium">Telefone:</span><a href="tel:${escapeHtml(lead.phone)}" class="text-indigo-600 hover:underline">${escapeHtml(lead.phone)}</a></p>` : ''}
                ${lead.value ? `<p><span class="font-medium">Valor:</span> ${formatCurrency(lead.value)}</p>` : ''}
                ${tagsHtml ? `<p class="flex flex-wrap gap-1">${tagsHtml}</p>` : ''}
                ${updatedAt ? `<p class="text-[11px] text-slate-400">Atualizado em ${updatedAt}</p>` : ''}
            </div>
        `;

        enableDragEvents(card);
        return card;
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function formatCurrency(value) {
        const number = Number(value);
        if (Number.isNaN(number)) {
            return value;
        }
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(number);
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return dateString;
        }
        return date.toLocaleString('pt-BR');
    }

    function insertLeadCardInStage(card, stageId, position) {
        const stage = document.querySelector(`[data-stage="${stageId}"] [data-stage-items]`);
        if (!stage) return;

        const children = Array.from(stage.querySelectorAll('[data-lead-id]'));
        if (position <= 0 || position > children.length + 1) {
            stage.appendChild(card);
        } else {
            stage.insertBefore(card, children[position - 1] || null);
        }

        reindexStage(stage);
        updateStageCounters();
    }

    function reindexStage(stageContainer) {
        Array.from(stageContainer.querySelectorAll('[data-lead-id]')).forEach((child, index) => {
            child.dataset.position = String(index + 1);
            child.dataset.stageId = stageContainer.closest('[data-stage]')?.dataset.stage || '';
        });
    }

    function enableDragEvents(card) {
        card.addEventListener('dragstart', (event) => {
            state.dragCard = card;
            state.dragSourceStage = card.closest('[data-stage]');
            state.dragSourceIndex = Array.from(card.parentElement.children).indexOf(card);
            card.classList.add('opacity-50', 'ring-2', 'ring-indigo-300');
            event.dataTransfer.effectAllowed = 'move';
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('opacity-50', 'ring-2', 'ring-indigo-300');
            state.dragCard = null;
            state.dragSourceStage = null;
            state.dragSourceIndex = null;
        });
    }

    function setupStage(stageElement) {
        const container = stageElement.querySelector('[data-stage-items]');
        if (!container) return;

        container.addEventListener('dragover', (event) => {
            event.preventDefault();
            const afterElement = getDragAfterElement(container, event.clientY);
            const dragged = state.dragCard;
            if (!dragged) return;

            if (afterElement == null) {
                container.appendChild(dragged);
            } else if (afterElement !== dragged) {
                container.insertBefore(dragged, afterElement);
            }
        });

        container.addEventListener('drop', async (event) => {
            event.preventDefault();
            const dragged = state.dragCard;
            if (!dragged) return;
            const targetStage = container.closest('[data-stage]');
            const stageId = Number(targetStage.dataset.stage);
            const position = Array.from(container.querySelectorAll('[data-lead-id]')).indexOf(dragged) + 1;

            dragged.dataset.stageId = String(stageId);
            reindexStage(container);
            updateStageCounters();

            try {
                await fetchJson(`/api/leads/${dragged.dataset.leadId}/move`, {
                    method: 'PUT',
                    body: JSON.stringify({ stage_id: stageId, position }),
                });
            } catch (error) {
                console.error(error);
                alert(error.payload?.message || 'Não foi possível mover o lead. Recarregando o quadro.');
                window.location.reload();
            }
        });
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('[data-lead-id]:not(.opacity-50)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset, element: child };
            }
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
    }

    function bindLeadButtons() {
        document.querySelectorAll('[data-action="open-lead-modal"]').forEach((button) => {
            button.addEventListener('click', () => {
                resetLeadForm();
                const stageId = button.dataset.stageId;
                if (stageId && leadForm) {
                    leadForm.querySelector('#lead-stage').value = stageId;
                }
                openModal(leadModal);
            });
        });

        document.querySelectorAll('[data-action="edit-lead"]').forEach((button) => {
            button.addEventListener('click', () => {
                resetLeadForm();
                try {
                    const leadData = JSON.parse(button.dataset.lead || '{}');
                    currentLeadId = leadData.id;
                    leadForm.querySelector('#lead-name').value = leadData.name || '';
                    leadForm.querySelector('#lead-company').value = leadData.company || '';
                    leadForm.querySelector('#lead-email').value = leadData.email || '';
                    leadForm.querySelector('#lead-phone').value = leadData.phone || '';
                    leadForm.querySelector('#lead-value').value = leadData.value || '';
                    leadForm.querySelector('#lead-tags').value = leadData.tags || '';
                    leadForm.querySelector('#lead-notes').value = leadData.notes || '';
                    leadForm.querySelector('#lead-stage').value = leadData.stage_id || '';
                    leadForm.querySelector('input[name="id"]').value = leadData.id;

                    const deleteButton = leadForm.querySelector('[data-action="delete-lead"]');
                    if (deleteButton) {
                        deleteButton.hidden = false;
                        deleteButton.dataset.leadId = leadData.id;
                        deleteButton.dataset.leadName = leadData.name;
                    }

                    openModal(leadModal);
                } catch (error) {
                    console.error(error);
                }
            });
        });

        const deleteLeadButton = leadForm?.querySelector('[data-action="delete-lead"]');
        deleteLeadButton?.addEventListener('click', () => {
            closeModal(leadModal);
            const namePlaceholder = leadDeleteModal.querySelector('[data-lead-name]');
            namePlaceholder.textContent = deleteLeadButton.dataset.leadName || '';
            leadDeleteModal.dataset.leadId = deleteLeadButton.dataset.leadId || '';
            openModal(leadDeleteModal);
        });

        stageModal?.querySelector('[data-action="close-modal"]')?.addEventListener('click', () => closeModal(stageModal));
        leadModal?.querySelector('[data-action="close-modal"]')?.addEventListener('click', () => closeModal(leadModal));
        leadDeleteModal?.querySelector('[data-action="close-modal"]')?.addEventListener('click', () => closeModal(leadDeleteModal));
        stageDeleteModal?.querySelector('[data-action="close-modal"]')?.addEventListener('click', () => closeModal(stageDeleteModal));
    }

    function bindStageButtons() {
        document.querySelectorAll('[data-action="open-new-stage-modal"]').forEach((button) => {
            button.addEventListener('click', () => {
                resetStageForm();
                openModal(stageModal);
            });
        });

        document.querySelectorAll('[data-action="edit-stage"]').forEach((button) => {
            button.addEventListener('click', () => {
                resetStageForm();
                currentStageId = button.dataset.stageId;
                stageForm.querySelector('#stage-name').value = button.dataset.stageName || '';
                stageForm.querySelector('input[name="id"]').value = button.dataset.stageId || '';
                openModal(stageModal);
            });
        });

        document.querySelectorAll('[data-action="delete-stage"]').forEach((button) => {
            button.addEventListener('click', () => {
                stageDeleteModal.dataset.stageId = button.dataset.stageId || '';
                const label = stageDeleteModal.querySelector('[data-stage-name]');
                if (label) {
                    label.textContent = button.dataset.stageName || '';
                }
                const transferWrapper = stageDeleteModal.querySelector('[data-stage-transfer]');
                const errorEl = stageDeleteModal.querySelector('[data-error="target_stage_id"]');
                transferWrapper.hidden = true;
                errorEl.hidden = true;
                const select = stageDeleteModal.querySelector('#stage-transfer-select');
                if (select) {
                    Array.from(select.options).forEach((option) => {
                        option.hidden = option.value === button.dataset.stageId;
                        if (option.hidden && option.selected) {
                            option.selected = false;
                        }
                    });
                    if (!select.value) {
                        const firstVisible = Array.from(select.options).find((option) => !option.hidden);
                        if (firstVisible) {
                            firstVisible.selected = true;
                        }
                    }
                }
                openModal(stageDeleteModal);
            });
        });
    }

    function handleLeadFormSubmit() {
        if (!leadForm) return;
        leadForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearErrors(leadForm);
            const data = serializeForm(leadForm);
            const isUpdate = Boolean(data.id);
            const url = isUpdate ? `/api/leads/${data.id}` : '/api/leads';
            const method = isUpdate ? 'PUT' : 'POST';

            try {
                await fetchJson(url, {
                    method,
                    body: JSON.stringify(data),
                });
                window.location.reload();
            } catch (error) {
                if (error.status === 422) {
                    showErrors(leadForm, error.payload?.errors || {});
                    return;
                }
                alert(error.payload?.message || 'Erro ao salvar o lead.');
            }
        });
    }

    function handleLeadDelete() {
        const confirmButton = leadDeleteModal?.querySelector('[data-confirm-delete-lead]');
        confirmButton?.addEventListener('click', async () => {
            const id = leadDeleteModal.dataset.leadId;
            if (!id) return;
            try {
                await fetchJson(`/api/leads/${id}`, {
                    method: 'DELETE',
                });
                window.location.reload();
            } catch (error) {
                alert(error.payload?.message || 'Não foi possível excluir o lead.');
            }
        });
    }

    function handleStageFormSubmit() {
        if (!stageForm) return;
        stageForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearErrors(stageForm);
            const data = serializeForm(stageForm);
            const isUpdate = Boolean(data.id);
            const url = isUpdate ? `/api/stages/${data.id}` : '/api/stages';
            const method = isUpdate ? 'PUT' : 'POST';

            try {
                await fetchJson(url, {
                    method,
                    body: JSON.stringify(data),
                });
                window.location.reload();
            } catch (error) {
                if (error.status === 422) {
                    showErrors(stageForm, error.payload?.errors || {});
                    return;
                }
                alert(error.payload?.message || 'Erro ao salvar a etapa.');
            }
        });
    }

    function handleStageDelete() {
        const confirmButton = stageDeleteModal?.querySelector('[data-confirm-delete-stage]');
        confirmButton?.addEventListener('click', async () => {
            const stageId = stageDeleteModal.dataset.stageId;
            if (!stageId) return;
            const transferWrapper = stageDeleteModal.querySelector('[data-stage-transfer]');
            const select = stageDeleteModal.querySelector('#stage-transfer-select');
            const errorEl = stageDeleteModal.querySelector('[data-error="target_stage_id"]');

            const payload = {};
            if (!transferWrapper.hidden) {
                payload.target_stage_id = select.value;
            }

            try {
                await fetchJson(`/api/stages/${stageId}`, {
                    method: 'DELETE',
                    body: JSON.stringify(payload),
                });
                window.location.reload();
            } catch (error) {
                if (error.status === 409 && error.payload?.requires_target_stage) {
                    transferWrapper.hidden = false;
                    errorEl.hidden = false;
                    errorEl.textContent = error.payload.message;
                    return;
                }
                if (error.status === 422) {
                    errorEl.hidden = false;
                    errorEl.textContent = error.payload?.message || 'Dados inválidos.';
                    return;
                }
                alert(error.payload?.message || 'Não foi possível excluir a etapa.');
            }
        });
    }

    function initStageReorder() {
        const board = document.querySelector('[data-kanban-board] > div');
        if (!board) return;

        const stageState = {
            dragged: null,
        };

        function enableStageDrag(stageCard) {
            stageCard.addEventListener('dragstart', (event) => {
                const header = stageCard.querySelector('header');
                if (!header || !event.target.closest('header')) {
                    event.preventDefault();
                    return;
                }
                if (state.dragCard) {
                    event.preventDefault();
                    return;
                }
                stageState.dragged = stageCard;
                stageCard.classList.add('opacity-60', 'ring-2', 'ring-indigo-300');
                event.dataTransfer.effectAllowed = 'move';
            });

            stageCard.addEventListener('dragend', async () => {
                if (!stageState.dragged) return;
                stageCard.classList.remove('opacity-60', 'ring-2', 'ring-indigo-300');
                stageState.dragged = null;

                const order = [...board.querySelectorAll('[data-stage]')].map((item) => item.dataset.stage);
                try {
                    await fetchJson('/api/stages/reorder', {
                        method: 'PUT',
                        body: JSON.stringify({ order }),
                    });
                } catch (error) {
                    console.error(error);
                }
            });
        }

        board.addEventListener('dragover', (event) => {
            if (!stageState.dragged || state.dragCard) return;
            event.preventDefault();
            const afterElement = getStageAfterElement(board, event.clientX);
            if (afterElement == null) {
                board.appendChild(stageState.dragged);
            } else if (afterElement !== stageState.dragged) {
                board.insertBefore(stageState.dragged, afterElement);
            }
        });

        board.addEventListener('drop', (event) => {
            if (!stageState.dragged || state.dragCard) return;
            event.preventDefault();
        });

        board.querySelectorAll('[data-stage]').forEach((stageCard) => enableStageDrag(stageCard));
    }

    function getStageAfterElement(container, x) {
        const draggableElements = [...container.querySelectorAll('[data-stage]:not(.opacity-60)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = x - box.left - box.width / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset, element: child };
            }
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
    }

    function bindGlobalListeners() {
        document.querySelectorAll('[data-action="close-modal"]').forEach((button) => {
            button.addEventListener('click', () => {
                const dialog = button.closest('dialog');
                closeModal(dialog);
            });
        });

        modalBackdrop?.addEventListener('click', () => {
            [leadModal, leadDeleteModal, stageModal, stageDeleteModal].forEach((dialog) => closeModal(dialog));
        });
    }

    function initDragAndDrop() {
        document.querySelectorAll('[data-lead-id]').forEach((card) => enableDragEvents(card));
        document.querySelectorAll('[data-stage]').forEach((stage) => setupStage(stage));
    }

    function init() {
        if (!csrfToken) return;
        bindGlobalListeners();
        bindLeadButtons();
        bindStageButtons();
        handleLeadFormSubmit();
        handleLeadDelete();
        handleStageFormSubmit();
        handleStageDelete();
        initStageReorder();
        initDragAndDrop();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
