import './bootstrap';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

function replaceTokens(template, replacements = {}) {
    return Object.entries(replacements).reduce(
        (message, [token, value]) => message.replaceAll(`:${token}`, String(value)),
        template,
    );
}

function showAlert(message) {
    if (message) {
        window.alert(message);
    }
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

async function copyTextFromInput(input) {
    if (!input) {
        throw new Error('Missing copy target.');
    }

    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(input.value);
        return;
    }

    const wasHidden = input.classList.contains('d-none');

    if (wasHidden) {
        input.classList.remove('d-none');
    }

    input.focus();
    input.select();
    input.setSelectionRange(0, input.value.length);

    const copied = document.execCommand('copy');

    if (wasHidden) {
        input.classList.add('d-none');
    }

    if (!copied) {
        throw new Error('Clipboard copy failed.');
    }
}

function showImagePreview(preview, source) {
    if (!preview) {
        return;
    }

    if (source) {
        preview.src = source;
        preview.classList.remove('d-none');
        return;
    }

    preview.src = '#';
    preview.classList.add('d-none');
}

function parseCsvRows(content) {
    const rows = [];
    let row = [];
    let cell = '';
    let inQuotes = false;

    for (let index = 0; index < content.length; index += 1) {
        const character = content[index];
        const nextCharacter = content[index + 1];

        if (character === '"') {
            if (inQuotes && nextCharacter === '"') {
                cell += '"';
                index += 1;
                continue;
            }

            inQuotes = !inQuotes;
            continue;
        }

        if (character === ',' && !inQuotes) {
            row.push(cell);
            cell = '';
            continue;
        }

        if ((character === '\n' || character === '\r') && !inQuotes) {
            if (character === '\r' && nextCharacter === '\n') {
                index += 1;
            }

            row.push(cell);
            rows.push(row);
            row = [];
            cell = '';
            continue;
        }

        cell += character;
    }

    if (cell.length > 0 || row.length > 0) {
        row.push(cell);
        rows.push(row);
    }

    return rows.filter((parsedRow) => parsedRow.some((value) => String(value).trim() !== ''));
}

function formatMinutesSeconds(totalSeconds) {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function buildCountdownLabel(template, seconds) {
    return template.replaceAll(':seconds', String(seconds));
}

function bindQuizQuestionHistoryGuard() {
    if (window.quizQuestionHistoryGuardBound === true) {
        return;
    }

    window.quizQuestionHistoryGuardBound = true;

    window.addEventListener('pagehide', () => {
        if (document.querySelector('[data-quiz-question-runtime]')) {
            document.documentElement.style.visibility = 'hidden';
        }
    });

    window.addEventListener('pageshow', (event) => {
        const isQuestionRuntime = document.querySelector('[data-quiz-question-runtime]') !== null;
        const navigationEntry = performance.getEntriesByType?.('navigation')?.[0];
        const restoredFromHistory = event.persisted || navigationEntry?.type === 'back_forward';

        if (isQuestionRuntime && restoredFromHistory) {
            window.location.reload();
            return;
        }

        document.documentElement.style.visibility = '';
    });
}

function getFocusableElements(container) {
    return Array.from(container.querySelectorAll('a, button, input:not([type="hidden"]), textarea, select, details, [tabindex]:not([tabindex="-1"])'))
        .filter((element) => element instanceof HTMLElement && !element.hasAttribute('disabled') && !element.hidden);
}

function syncModalBodyLock() {
    const hasOpenModal = document.querySelector('[data-app-modal][data-modal-open="true"]') !== null;
    document.body.style.overflow = hasOpenModal ? 'hidden' : '';
}

function initToasts() {
    document.querySelectorAll('.toast').forEach((toastElement) => {
        const toast = bootstrap.Toast.getOrCreateInstance(toastElement, { delay: 5000 });
        toast.show();
    });
}

function initConfirmSubmit() {
    document.querySelectorAll('form[data-confirm-submit]').forEach((form) => {
        if (form.dataset.confirmBound === 'true') {
            return;
        }

        form.dataset.confirmBound = 'true';
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirmSubmit;

            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

function initSubmitFormButtons() {
    document.querySelectorAll('[data-submit-form]').forEach((button) => {
        if (button.dataset.submitBound === 'true') {
            return;
        }

        button.dataset.submitBound = 'true';
        button.addEventListener('click', (event) => {
            const form = document.getElementById(button.dataset.submitForm);

            if (!form) {
                return;
            }

            const message = button.dataset.confirmMessage;
            if (message && !window.confirm(message)) {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.submit();
        });
    });
}

function initAutoSubmitControls() {
    document.querySelectorAll('[data-auto-submit]').forEach((control) => {
        if (control.dataset.autoSubmitBound === 'true') {
            return;
        }

        control.dataset.autoSubmitBound = 'true';
        control.addEventListener('change', () => {
            const form = control.form;

            if (!form) {
                return;
            }

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.submit();
        });
    });
}

function initClipboardButtons() {
    document.querySelectorAll('[data-copy-target]').forEach((button) => {
        if (button.dataset.copyBound === 'true') {
            return;
        }

        button.dataset.copyBound = 'true';
        button.addEventListener('click', async (event) => {
            event.preventDefault();

            const target = document.getElementById(button.dataset.copyTarget);

            try {
                await copyTextFromInput(target);
                showAlert(button.dataset.copySuccess);
            } catch (error) {
                showAlert(button.dataset.copyError);
            }
        });
    });
}

function initImagePreviewInputs() {
    document.querySelectorAll('input[type="file"][data-image-preview-target]').forEach((input) => {
        if (input.dataset.previewBound === 'true') {
            return;
        }

        const preview = document.getElementById(input.dataset.imagePreviewTarget);
        const fallbackSource = preview?.dataset.previewFallbackSrc ?? '';

        input.dataset.previewBound = 'true';
        input.addEventListener('change', () => {
            const file = input.files?.[0];

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    showImagePreview(preview, String(event.target?.result ?? ''));
                };
                reader.readAsDataURL(file);
                return;
            }

            showImagePreview(preview, fallbackSource);
        });
    });
}

function initRandomLimitToggles() {
    document.querySelectorAll('[data-random-limit-toggle]').forEach((checkbox) => {
        if (checkbox.dataset.randomLimitBound === 'true') {
            return;
        }

        const container = document.getElementById(checkbox.dataset.randomLimitTarget);
        const input = document.getElementById(checkbox.dataset.randomLimitInput);

        const syncState = () => {
            const isChecked = checkbox.checked;

            container?.classList.toggle('d-none', !isChecked);

            if (input) {
                input.disabled = !isChecked;

                if (!isChecked) {
                    input.value = '';
                }
            }
        };

        checkbox.dataset.randomLimitBound = 'true';
        checkbox.addEventListener('change', syncState);
        syncState();
    });
}

function initQuestionTemplateEditors() {
    document.querySelectorAll('[data-answer-editor]').forEach((editor) => {
        if (editor.dataset.answerEditorBound === 'true') {
            return;
        }

        const template = document.getElementById(editor.dataset.answerTemplate);
        const addButton = document.getElementById(editor.dataset.answerAddButton);
        const maxAnswers = editor.dataset.maxAnswers ? Number(editor.dataset.maxAnswers) : null;

        const updateAnswerIndexes = () => {
            const rows = editor.querySelectorAll('[data-answer-row]');

            rows.forEach((row, index) => {
                const idInput = row.querySelector('input[type="hidden"][name*="[id]"]') ?? row.querySelector('[data-answer-id]');
                const textInput = row.querySelector('input[type="text"]');
                const hiddenCorrectInput = row.querySelector('input[type="hidden"][name*="[is_correct]"]') ?? row.querySelector('[data-answer-correct-hidden]');
                const checkboxInput = row.querySelector('input[type="checkbox"]');
                const checkboxLabel = row.querySelector('label.form-check-label');

                if (idInput) {
                    idInput.name = `answers[${index}][id]`;
                }

                if (textInput) {
                    textInput.name = `answers[${index}][text]`;
                }

                if (hiddenCorrectInput) {
                    hiddenCorrectInput.name = `answers[${index}][is_correct]`;
                }

                if (checkboxInput) {
                    checkboxInput.name = `answers[${index}][is_correct]`;
                    checkboxInput.id = `answer_correct_${index}`;
                }

                if (checkboxLabel) {
                    checkboxLabel.htmlFor = `answer_correct_${index}`;
                }
            });

            if (addButton && maxAnswers !== null) {
                addButton.disabled = rows.length >= maxAnswers;
            }
        };

        editor.dataset.answerEditorBound = 'true';

        addButton?.addEventListener('click', () => {
            if (!template) {
                return;
            }

            if (maxAnswers !== null && editor.querySelectorAll('[data-answer-row]').length >= maxAnswers) {
                return;
            }

            const clone = template.content.firstElementChild?.cloneNode(true);
            if (!clone) {
                return;
            }

            editor.appendChild(clone);
            updateAnswerIndexes();
        });

        editor.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-answer-row]');
            if (!removeButton) {
                return;
            }

            const rows = editor.querySelectorAll('[data-answer-row]');
            if (rows.length <= 2) {
                return;
            }

            removeButton.closest('[data-answer-row]')?.remove();
            updateAnswerIndexes();
        });

        updateAnswerIndexes();
    });
}

function initStudentCsvValidation() {
    document.querySelectorAll('form[data-student-import-form]').forEach((form) => {
        if (form.dataset.validationBound === 'true') {
            return;
        }

        form.dataset.validationBound = 'true';
        form.addEventListener('submit', (event) => {
            const input = form.querySelector('input[type="file"][data-student-import-input]');
            const file = input?.files?.[0];

            if (!file) {
                return;
            }

            event.preventDefault();

            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                const csvContent = String(loadEvent.target?.result ?? '');
                const rows = parseCsvRows(csvContent);
                const errors = [];
                const maxLines = Number(form.dataset.maxLines ?? '30');
                const maxAttempts = Number(form.dataset.maxAttempts ?? '5');
                const expectedHeader = String(form.dataset.expectedHeader ?? '').trim().toLowerCase();

                if (rows.length === 0) {
                    showAlert(form.dataset.emptyFileMessage);
                    return;
                }

                if (rows.length - 1 > maxLines) {
                    errors.push(replaceTokens(form.dataset.tooManyRowsMessage ?? '', { max: maxLines }));
                }

                const headerRow = rows[0].map((cell) => cell.trim()).join(',').toLowerCase();
                if (expectedHeader && headerRow !== expectedHeader) {
                    errors.push(form.dataset.invalidHeadersMessage ?? '');
                }

                for (let index = 1; index < rows.length; index += 1) {
                    const lineNumber = index + 1;
                    const [name, code, attempts] = rows[index].map((cell) => cell.trim());

                    if (!name || !code || !attempts) {
                        errors.push(replaceTokens(form.dataset.missingFieldsMessage ?? '', { line: lineNumber }));
                        continue;
                    }

                    if (!/^\d{4}$/.test(code)) {
                        errors.push(replaceTokens(form.dataset.invalidCodeMessage ?? '', { line: lineNumber }));
                    }

                    if (code === '0000') {
                        errors.push(replaceTokens(form.dataset.reservedCodeMessage ?? '', { line: lineNumber }));
                    }

                    const attemptsValue = Number(attempts);
                    if (Number.isNaN(attemptsValue) || attemptsValue < 1 || attemptsValue > maxAttempts) {
                        errors.push(replaceTokens(form.dataset.invalidAttemptsMessage ?? '', {
                            line: lineNumber,
                            max: maxAttempts,
                        }));
                    }
                }

                if (errors.length > 0) {
                    showAlert(errors.join('\n'));
                    return;
                }

                form.submit();
            };

            reader.onerror = () => {
                showAlert(form.dataset.readErrorMessage);
            };

            reader.readAsText(file);
        });
    });
}

function initQuestionCsvValidation() {
    document.querySelectorAll('form[data-question-import-form]').forEach((form) => {
        if (form.dataset.validationBound === 'true') {
            return;
        }

        form.dataset.validationBound = 'true';
        form.addEventListener('submit', (event) => {
            const input = form.querySelector('input[type="file"][data-question-import-input]');
            const file = input?.files?.[0];

            if (!file) {
                return;
            }

            event.preventDefault();

            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                const csvContent = String(loadEvent.target?.result ?? '');
                const rows = parseCsvRows(csvContent);
                const maxLines = Number(form.dataset.maxLines ?? '20');
                const expectedHeaderPrefix = String(form.dataset.expectedHeaderPrefix ?? '').trim().toLowerCase();
                const errors = [];

                if (rows.length === 0) {
                    showAlert(form.dataset.emptyFileMessage);
                    return;
                }

                if (rows.length - 1 > maxLines) {
                    errors.push(replaceTokens(form.dataset.tooManyRowsMessage ?? '', { max: maxLines }));
                }

                const headerRow = rows[0].map((cell) => cell.trim()).join(',').toLowerCase();
                if (expectedHeaderPrefix && !headerRow.startsWith(expectedHeaderPrefix)) {
                    errors.push(form.dataset.invalidHeadersMessage ?? '');
                }

                for (let index = 1; index < rows.length; index += 1) {
                    const lineNumber = index + 1;
                    const row = rows[index].map((cell) => cell.trim());
                    const hasQuestionText = row[0]?.length > 0;

                    if (!hasQuestionText) {
                        errors.push(replaceTokens(form.dataset.emptyQuestionMessage ?? '', { line: lineNumber }));
                    }
                }

                if (errors.length > 0) {
                    showAlert(errors.join('\n'));
                    return;
                }

                form.submit();
            };

            reader.onerror = () => {
                showAlert(form.dataset.readErrorMessage);
            };

            reader.readAsText(file);
        });
    });
}

function initTemplateOwnershipToggles() {
    document.querySelectorAll('[data-visibility-toggle]').forEach((checkbox) => {
        if (checkbox.dataset.visibilityToggleBound === 'true') {
            return;
        }

        const target = document.getElementById(checkbox.dataset.visibilityToggleTarget);
        const syncState = () => {
            target?.classList.toggle('d-none', checkbox.checked);
        };

        checkbox.dataset.visibilityToggleBound = 'true';
        checkbox.addEventListener('change', syncState);
        syncState();
    });
}

function initFocusTargets() {
    document.querySelectorAll('[data-focus-on-load]').forEach((element) => {
        if (element.dataset.focusBound === 'true') {
            return;
        }

        const targetId = element.dataset.focusOnLoad;
        element.dataset.focusBound = 'true';

        window.setTimeout(() => {
            const target = targetId ? document.getElementById(targetId) : null;

            if (target instanceof HTMLElement) {
                target.focus();
            }
        }, 0);
    });
}

function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        if (button.dataset.passwordToggleBound === 'true') {
            return;
        }

        const input = document.getElementById(button.dataset.passwordToggleTarget);
        const icon = button.querySelector('[data-password-toggle-icon]');

        button.dataset.passwordToggleBound = 'true';
        button.addEventListener('click', () => {
            if (!input) {
                return;
            }

            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');
            icon?.classList.toggle('fa-eye');
            icon?.classList.toggle('fa-eye-slash');
        });
    });
}

function initCountdownRedirects() {
    document.querySelectorAll('[data-countdown-redirect]').forEach((element) => {
        if (element.dataset.countdownBound === 'true') {
            return;
        }

        const initialCountdown = Number(element.dataset.countdownInitial ?? '10');
        let countdown = initialCountdown;
        const labelTemplate = element.dataset.countdownLabel ?? ':seconds';
        const redirectUrl = element.dataset.countdownRedirect;
        const labelTarget = element.querySelector('[data-countdown-text]') ?? element;
        const valueTarget = element.querySelector('[data-countdown-value]');
        const progressTarget = element.querySelector('[data-countdown-progress]');

        const renderCountdown = () => {
            const safeCountdown = Math.max(0, countdown);
            labelTarget.textContent = buildCountdownLabel(labelTemplate, safeCountdown);

            if (valueTarget) {
                valueTarget.textContent = String(safeCountdown);
            }

            if (progressTarget) {
                const radius = Number(progressTarget.dataset.countdownRadius ?? '0');

                if (radius > 0) {
                    const circumference = 2 * Math.PI * radius;
                    const ratio = initialCountdown > 0 ? safeCountdown / initialCountdown : 0;
                    progressTarget.style.strokeDasharray = `${circumference}`;
                    progressTarget.style.strokeDashoffset = `${circumference * (1 - ratio)}`;
                }
            }
        };

        element.dataset.countdownBound = 'true';
        renderCountdown();

        const timer = window.setInterval(() => {
            countdown -= 1;
            renderCountdown();

            if (countdown <= 0) {
                window.clearInterval(timer);
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }
        }, 1000);
    });
}

function initQuizParticipantDisclaimer() {
    const storageKey = 'quizParticipantDisclaimerHidden';

    document.querySelectorAll('[data-quiz-disclaimer]').forEach((panel) => {
        if (panel.dataset.disclaimerBound === 'true') {
            return;
        }

        panel.dataset.disclaimerBound = 'true';

        if (window.sessionStorage?.getItem(storageKey) === 'true') {
            panel.hidden = true;
        }

        panel.querySelector('[data-quiz-disclaimer-dismiss]')?.addEventListener('click', () => {
            panel.hidden = true;

            if (window.sessionStorage) {
                window.sessionStorage.setItem(storageKey, 'true');
            }
        });
    });
}

function initTypingEffects() {
    document.querySelectorAll('[data-typing-text]').forEach((element) => {
        if (element.dataset.typingBound === 'true') {
            return;
        }

        const fullText = element.dataset.typingText ?? '';
        const charDelay = Number(element.dataset.typingDelay ?? '50');
        const startDelay = Number(element.dataset.typingStartDelay ?? '1500');
        const revealTargetId = element.dataset.typingRevealTarget;
        const revealDelay = Number(element.dataset.typingRevealDelay ?? '0');
        let index = 0;

        element.dataset.typingBound = 'true';
        element.textContent = '';

        const typeNextChar = () => {
            if (index < fullText.length) {
                element.textContent += fullText[index];
                index += 1;
                window.setTimeout(typeNextChar, charDelay);
                return;
            }

            element.classList.add('typing-complete');
        };

        window.setTimeout(typeNextChar, startDelay);

        if (revealTargetId) {
            window.setTimeout(() => {
                document.getElementById(revealTargetId)?.classList.remove('d-none');
            }, revealDelay);
        }
    });
}

function initTurnstileWidgets() {
    const widgets = document.querySelectorAll('[data-turnstile-widget]');

    if (widgets.length === 0 || document.querySelector('script[data-turnstile-script]')) {
        return;
    }

    const script = document.createElement('script');
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    script.async = true;
    script.defer = true;
    script.dataset.turnstileScript = 'true';
    document.head.appendChild(script);
}

function initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
        if (dropdown.dataset.dropdownBound === 'true') {
            return;
        }

        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');

        if (!trigger || !menu) {
            dropdown.dataset.dropdownBound = 'true';
            return;
        }

        const closeMenu = () => {
            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            dropdown.dataset.dropdownOpen = 'false';
        };

        const openMenu = () => {
            menu.hidden = false;
            trigger.setAttribute('aria-expanded', 'true');
            dropdown.dataset.dropdownOpen = 'true';
        };

        const toggleMenu = () => {
            if (dropdown.dataset.dropdownOpen === 'true') {
                closeMenu();
                return;
            }

            document.querySelectorAll('[data-dropdown][data-dropdown-open="true"]').forEach((openDropdown) => {
                if (openDropdown === dropdown) {
                    return;
                }

                openDropdown.querySelector('[data-dropdown-menu]')?.setAttribute('hidden', 'hidden');
                openDropdown.querySelector('[data-dropdown-trigger]')?.setAttribute('aria-expanded', 'false');
                openDropdown.dataset.dropdownOpen = 'false';
            });

            openMenu();
        };

        dropdown.dataset.dropdownBound = 'true';
        closeMenu();

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            toggleMenu();
        });

        menu.addEventListener('click', () => {
            closeMenu();
        });

        document.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });
    });
}

function initAppModals() {
    document.querySelectorAll('[data-app-modal]').forEach((modal) => {
        if (modal.dataset.modalBound === 'true') {
            return;
        }

        const modalName = modal.dataset.modalName ?? '';
        const backdrop = modal.querySelector('[data-modal-backdrop]');
        const panel = modal.querySelector('[data-modal-panel]');
        const shouldFocus = modal.dataset.modalFocusable === 'true';
        let previouslyFocused = null;

        const closeModal = () => {
            modal.hidden = true;
            modal.dataset.modalOpen = 'false';
            modal.setAttribute('aria-hidden', 'true');
            syncModalBodyLock();

            if (previouslyFocused instanceof HTMLElement) {
                previouslyFocused.focus();
            }
        };

        const openModal = () => {
            previouslyFocused = document.activeElement instanceof HTMLElement ? document.activeElement : null;
            modal.hidden = false;
            modal.dataset.modalOpen = 'true';
            modal.setAttribute('aria-hidden', 'false');
            syncModalBodyLock();

            window.setTimeout(() => {
                const focusables = panel ? getFocusableElements(panel) : [];

                if (shouldFocus && focusables.length > 0) {
                    focusables[0].focus();
                    return;
                }

                panel?.focus();
            }, 50);
        };

        modal.dataset.modalBound = 'true';

        if (modal.dataset.modalOpen === 'true') {
            openModal();
        } else {
            closeModal();
        }

        backdrop?.addEventListener('click', closeModal);
        modal.addEventListener('close', closeModal);

        modal.addEventListener('keydown', (event) => {
            if (modal.dataset.modalOpen !== 'true') {
                return;
            }

            if (event.key === 'Escape') {
                closeModal();
                return;
            }

            if (event.key !== 'Tab' || !panel) {
                return;
            }

            const focusables = getFocusableElements(panel);
            if (focusables.length === 0) {
                event.preventDefault();
                panel.focus();
                return;
            }

            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
                return;
            }

            if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        window.addEventListener('open-modal', (event) => {
            if ((event.detail ?? null) === modalName) {
                openModal();
            }
        });

        window.addEventListener('close-modal', (event) => {
            if ((event.detail ?? null) === modalName) {
                closeModal();
            }
        });
    });
}

function initModalAutoOpen() {
    document.querySelectorAll('[data-open-modal-on-load]').forEach((element) => {
        if (element.dataset.modalAutoOpenBound === 'true') {
            return;
        }

        const modal = document.getElementById(element.dataset.openModalOnLoad);
        element.dataset.modalAutoOpenBound = 'true';

        if (modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    });
}

function initQuizQuestionRuntime() {
    document.querySelectorAll('[data-quiz-question-runtime]').forEach((container) => {
        if (container.dataset.runtimeBound === 'true') {
            return;
        }

        bindQuizQuestionHistoryGuard();

        const form = container.querySelector('[data-quiz-answer-form]');
        const submitButton = container.querySelector('[data-quiz-submit-button]');
        const selectionInfo = container.querySelector('[data-selection-info]');
        const selectedAnswer = container.querySelector('[data-selected-answer]');
        const answerInputs = container.querySelectorAll("input[name='answer_id[]']");
        const skipForm = container.querySelector('[data-quiz-skip-form]');

        if (!form) {
            container.dataset.runtimeBound = 'true';
            return;
        }
        const timeDisplay = container.querySelector('[data-time-remaining]');
        const progressBar = container.querySelector('[data-time-progress]');
        const correctCount = Number(container.dataset.correctCount ?? '1');
        const instructionText = container.dataset.instructionText ?? '';
        const selectedPrefix = container.dataset.selectedPrefix ?? '';
        const allowResume = container.dataset.allowResume === 'true';
        const attemptId = container.dataset.attemptId ?? '';
        const forceSubmitUrl = container.dataset.forceSubmitUrl ?? '';
        const csrfToken = container.dataset.csrfToken ?? '';
        const endQuizUrl = container.dataset.endQuizUrl ?? '';
        const hasTimer = container.dataset.hasTimer === 'true';
        const endTime = Number(container.dataset.endTime ?? '0');
        const serverNow = Number(container.dataset.serverNow ?? '0');
        const timeLimit = Number(container.dataset.timeLimit ?? '0');
        const lastQuestion = container.dataset.lastQuestion === 'true';
        const finalFallbackSelector = container.dataset.finalFallbackTarget ?? '';
        const fallbackTarget = finalFallbackSelector
            ? (container.matches(finalFallbackSelector) ? container : container.querySelector(finalFallbackSelector))
            : container;
        const fallbackTemplate = container.querySelector('template[data-final-submit-fallback-template]')
            ?? container.parentElement?.querySelector('template[data-final-submit-fallback-template]')
            ?? document.querySelector('template[data-final-submit-fallback-template]');
        const fallbackMarkup = fallbackTemplate?.innerHTML ?? '';

        let isBeingSubmitted = false;
        const isFirefox = typeof InstallTrigger !== 'undefined';

        const updateSelectionState = () => {
            const checkedInputs = container.querySelectorAll("input[name='answer_id[]']:checked");
            const selectedLabels = Array.from(checkedInputs).map((input) => {
                const rawLabel = input.parentNode?.textContent ?? '';

                return rawLabel.replace(/\s+/g, ' ').trim();
            });

            if (selectedAnswer) {
                selectedAnswer.innerText = checkedInputs.length > 0
                    ? `${selectedPrefix} ${selectedLabels.join(', ')}`
                    : '';
            }

            if (submitButton) {
                submitButton.disabled = checkedInputs.length !== correctCount;
            }

            if (selectionInfo) {
                selectionInfo.innerText = instructionText;
                selectionInfo.classList.toggle('text-success', checkedInputs.length === correctCount);
                selectionInfo.classList.toggle('text-danger', checkedInputs.length !== correctCount);
            }
        };

        const submitQuizForm = (event) => {
            if (event) {
                event.preventDefault();
            }

            if (!form) {
                return;
            }

            isBeingSubmitted = true;
            submitButton?.setAttribute('disabled', 'disabled');
            form.querySelectorAll('button').forEach((button) => {
                button.disabled = true;
            });

            const activeButton = document.activeElement;
            if (activeButton instanceof HTMLElement && activeButton.hasAttribute('formaction')) {
                form.setAttribute('action', activeButton.getAttribute('formaction') ?? form.action);
            }

            if (isFirefox && lastQuestion) {
                window.setTimeout(() => {
                    form.submit();
                }, 300);

                if (fallbackTarget && fallbackMarkup && !document.getElementById('final-submit-container')) {
                    window.setTimeout(() => {
                        if (!document.getElementById('final-submit-container')) {
                            fallbackTarget.insertAdjacentHTML('beforeend', fallbackMarkup);
                        }
                    }, 1000);
                }

                return;
            }

            window.setTimeout(() => {
                form.submit();
            }, 300);
        };

        container.dataset.runtimeBound = 'true';
        container.quizIsBeingSubmitted = false;

        form?.addEventListener('submit', submitQuizForm);
        skipForm?.addEventListener('submit', () => {
            isBeingSubmitted = true;
            skipForm.querySelector('button')?.setAttribute('disabled', 'disabled');
        });

        answerInputs.forEach((input) => {
            input.addEventListener('change', updateSelectionState);
        });
        updateSelectionState();

        if (!allowResume && attemptId && attemptId !== 'guest' && forceSubmitUrl) {
            window.addEventListener('beforeunload', () => {
                if (!isBeingSubmitted) {
                    navigator.sendBeacon(forceSubmitUrl, new URLSearchParams({
                        _token: csrfToken,
                        attempt_id: attemptId,
                    }));
                }
            });
        }

        if (hasTimer && timeDisplay && progressBar && endTime > 0 && timeLimit > 0) {
            const timerPrefix = timeDisplay.innerHTML.replace(/\d{2}:\d{2}\s*$/, '');
            const clientNowAtLoad = Math.floor(Date.now() / 1000);
            const offset = clientNowAtLoad - serverNow;

            const updateTimer = () => {
                const clientNow = Math.floor(Date.now() / 1000);
                const nowCorrected = clientNow - offset;
                const remaining = endTime - nowCorrected;

                if (remaining <= 0) {
                    if (endQuizUrl) {
                        window.location.href = endQuizUrl;
                    }
                    return;
                }

                timeDisplay.innerHTML = `${timerPrefix}${formatMinutesSeconds(remaining)}`;
                const remainingRatio = Math.min(100, Math.max(0, (remaining / timeLimit) * 100));
                progressBar.style.width = `${remainingRatio}%`;
                progressBar.setAttribute('aria-valuenow', String(Math.round(remainingRatio)));

                const isDangerZone = remaining <= 20;
                timeDisplay.classList.toggle('text-danger', isDangerZone);
                progressBar.classList.toggle('progress-bar--danger', isDangerZone);

                window.setTimeout(updateTimer, 1000);
            };

            updateTimer();
        }
    });
}

async function fetchJsonState(url, sinceVersion) {
    const requestUrl = new URL(url, window.location.origin);

    if (Number.isInteger(sinceVersion) && sinceVersion > 0) {
        requestUrl.searchParams.set('since', String(sinceVersion));
    }

    const response = await window.fetch(requestUrl.toString(), {
        headers: {
            Accept: 'application/json',
        },
        credentials: 'same-origin',
    });

    if (response.status === 204) {
        return null;
    }

    if (!response.ok) {
        const error = new Error(`State request failed with ${response.status}`);
        error.status = response.status;
        throw error;
    }

    return response.json();
}

async function postDisplayAction(url, csrfToken, payload = {}) {
    const response = await window.fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
    });

    const isJson = (response.headers.get('content-type') ?? '').includes('application/json');
    const data = isJson ? await response.json() : null;

    if (!response.ok) {
        throw new Error(data?.message ?? `Request failed with ${response.status}`);
    }

    return data;
}

function createAnswerOptionMarkup(answer, inputType, prefix, groupName) {
    const prefixMarkup = prefix ? `<span class="quiz-display-answer__prefix">${escapeHtml(prefix)}</span>` : '';
    const inputName = inputType === 'checkbox' ? `${groupName}[]` : groupName;

    return `
        <label class="quiz-display-answer ${answer.is_selected ? 'is-selected' : ''}">
            <input
                type="${inputType}"
                name="${escapeHtml(inputName)}"
                value="${escapeHtml(answer.id)}"
                ${answer.is_selected ? 'checked' : ''}
            >
            <span class="quiz-display-answer__copy">
                ${prefixMarkup}
                <span>${escapeHtml(answer.text)}</span>
            </span>
        </label>
    `;
}

function renderDisplayStatusBadge(target, state) {
    if (!target) {
        return;
    }

    target.textContent = state.session.status_label;
    target.dataset.status = state.session.status;
}

function renderDisplayTimer(target, seconds) {
    if (!target) {
        return;
    }

    if (typeof seconds !== 'number') {
        target.textContent = '';
        return;
    }

    target.textContent = formatMinutesSeconds(Math.max(0, seconds));
}

function createTimerRenderer(target) {
    let timerSnapshot = null;

    const renderCurrent = () => {
        if (!timerSnapshot) {
            renderDisplayTimer(target, null);
            return;
        }

        const elapsedSeconds = Math.floor((Date.now() - timerSnapshot.capturedAt) / 1000);
        renderDisplayTimer(target, Math.max(0, timerSnapshot.seconds - elapsedSeconds));
    };

    window.setInterval(renderCurrent, 1000);

    return {
        sync(seconds) {
            timerSnapshot = typeof seconds === 'number'
                ? {
                    seconds: Math.max(0, Math.floor(seconds)),
                    capturedAt: Date.now(),
                }
                : null;

            renderCurrent();
        },
    };
}

function initQuizDisplayScreen() {
    document.querySelectorAll('[data-quiz-display-screen]').forEach((container) => {
        if (container.dataset.displayScreenBound === 'true') {
            return;
        }

        const stateUrl = container.dataset.stateUrl;
        const pollInterval = Number(container.dataset.pollInterval ?? '1500');
        const expiredStatusLabel = container.dataset.expiredStatusLabel ?? 'Expired';
        const expiredMessage = container.dataset.expiredMessage ?? 'This display session is no longer available.';
        const studentTarget = container.querySelector('[data-display-student]');
        const progressTarget = container.querySelector('[data-display-progress]');
        const timerTarget = container.querySelector('[data-display-timer]');
        const statusBadge = container.querySelector('[data-display-status-badge]');
        const grid = container.querySelector('[data-display-grid]');
        const sidebar = container.querySelector('[data-display-sidebar]');
        const placeholder = container.querySelector('[data-display-placeholder]');
        const questionCard = container.querySelector('[data-display-question-card]');
        const resultCard = container.querySelector('[data-display-result-card]');
        const questionLabel = container.querySelector('[data-display-question-label]');
        const questionText = container.querySelector('[data-display-question-text]');
        const imageShell = container.querySelector('[data-display-image-shell]');
        const image = container.querySelector('[data-display-image]');
        const selection = container.querySelector('[data-display-selection]');
        const answers = container.querySelector('[data-display-answers]');
        const resultText = container.querySelector('[data-display-result-text]');
        const resultNote = container.querySelector('[data-display-result-note]');
        const resultPdfLink = container.querySelector('[data-display-result-pdf]');

        let currentVersion = 0;
        let pollHandle = null;
        let previousMode = '';
        let connectionAnimationHandle = null;
        const timerRenderer = createTimerRenderer(timerTarget);

        const animateConnectionEstablished = () => {
            if (!questionCard) {
                return;
            }

            window.clearTimeout(connectionAnimationHandle);
            container.classList.remove('is-connection-animate');
            questionCard.classList.remove('is-entering');

            // Restart the transition classes when the controller connects.
            void container.offsetWidth;
            container.classList.add('is-connection-animate');
            questionCard.classList.add('is-entering');

            connectionAnimationHandle = window.setTimeout(() => {
                container.classList.remove('is-connection-animate');
                questionCard.classList.remove('is-entering');
            }, 1200);
        };

        const renderState = (state) => {
            const nextMode = (state.session.status === 'submitted' || state.session.status === 'expired' || state.session.status === 'revoked')
                ? 'result'
                : ((!state.session.controller_claimed || !state.question) ? 'waiting' : 'live');

            currentVersion = state.session.state_version;
            renderDisplayStatusBadge(statusBadge, state);

            if (studentTarget) {
                studentTarget.textContent = state.student.name ?? '';
            }

            if (progressTarget) {
                progressTarget.textContent = `${state.progress.current} / ${state.progress.total}`;
            }

            timerRenderer.sync(state.attempt.time_remaining_seconds);

            if (nextMode === 'result') {
                container.dataset.screenMode = 'result';
                grid?.classList.add('is-stage-only');
                placeholder?.classList.add('d-none');
                questionCard?.classList.add('d-none');
                sidebar?.classList.add('d-none');
                resultCard?.classList.remove('d-none');

                if (resultText) {
                    if (state.session.status === 'submitted') {
                        resultText.textContent = `${state.student.name}: ${state.attempt.score}%`;
                    } else {
                        resultText.textContent = state.messages.screen_expired;
                    }
                }

                if (resultNote) {
                    resultNote.textContent = state.result?.reason_label ?? '';
                    resultNote.classList.toggle('d-none', (state.result?.reason_label ?? '') === '');
                }

                if (resultPdfLink) {
                    const pdfUrl = state.result?.pdf_url ?? '';
                    resultPdfLink.classList.toggle('d-none', pdfUrl === '');
                    resultPdfLink.href = pdfUrl || '#';
                }

                previousMode = nextMode;
                return;
            }

            if (nextMode === 'waiting') {
                container.dataset.screenMode = 'waiting';
                grid?.classList.remove('is-stage-only');
                placeholder?.classList.remove('d-none');
                questionCard?.classList.add('d-none');
                resultCard?.classList.add('d-none');
                sidebar?.classList.remove('d-none');
                previousMode = nextMode;
                return;
            }

            container.dataset.screenMode = 'live';
            grid?.classList.add('is-stage-only');
            placeholder?.classList.add('d-none');
            questionCard?.classList.remove('d-none');
            resultCard?.classList.add('d-none');
            sidebar?.classList.add('d-none');

            if (questionLabel) {
                questionLabel.textContent = state.progress.label;
            }

            if (questionText) {
                questionText.textContent = state.question.text;
            }

            if (selection) {
                selection.textContent = state.question.selection_summary;
            }

            if (imageShell && image) {
                imageShell.classList.toggle('d-none', !state.question.image_url);
                if (state.question.image_url) {
                    image.src = state.question.image_url;
                }
            }

            if (answers) {
                answers.innerHTML = state.question.answers.map((answer) => `
                    <div class="quiz-display-answer ${answer.is_selected ? 'is-selected' : ''}">
                        <div class="quiz-display-answer__copy">
                            ${answer.prefix ? `<span class="quiz-display-answer__prefix">${escapeHtml(answer.prefix)}</span>` : ''}
                            <span>${escapeHtml(answer.text)}</span>
                        </div>
                    </div>
                `).join('');
            }

            if (previousMode === 'waiting') {
                animateConnectionEstablished();
            }

            previousMode = nextMode;
        };

        const poll = async () => {
            try {
                const state = await fetchJsonState(stateUrl);
                if (state) {
                    renderState(state);
                }
            } catch (error) {
                window.clearInterval(pollHandle);

                if (error.status === 403) {
                    renderState({
                        session: {
                            status: 'expired',
                            status_label: expiredStatusLabel,
                            state_version: currentVersion,
                            controller_claimed: false,
                        },
                        student: {
                            name: studentTarget?.textContent ?? '',
                        },
                        progress: {
                            current: 0,
                            total: 0,
                        },
                        attempt: {
                            time_remaining_seconds: null,
                        },
                        messages: {
                            screen_expired: expiredMessage,
                        },
                    });

                    return;
                }

                showAlert(error.message);
            }
        };

        container.dataset.displayScreenBound = 'true';
        poll();
        pollHandle = window.setInterval(poll, pollInterval);
    });
}

function initQuizDisplayController() {
    document.querySelectorAll('[data-quiz-display-controller]').forEach((container) => {
        if (container.dataset.displayControllerBound === 'true') {
            return;
        }

        const stateUrl = container.dataset.stateUrl;
        const answerUrl = container.dataset.answerUrl;
        const navigateUrl = container.dataset.navigateUrl;
        const submitUrl = container.dataset.submitUrl;
        const csrfToken = container.dataset.csrfToken;
        const pollInterval = Number(container.dataset.pollInterval ?? '1500');
        const expiredStatusLabel = container.dataset.expiredStatusLabel ?? 'Expired';

        const studentTarget = container.querySelector('[data-controller-student]');
        const progressTarget = container.querySelector('[data-controller-progress]');
        const timerTarget = container.querySelector('[data-controller-timer]');
        const statusTarget = container.querySelector('[data-controller-status]');
        const placeholder = container.querySelector('[data-controller-placeholder]');
        const questionCard = container.querySelector('[data-controller-question-card]');
        const resultCard = container.querySelector('[data-controller-result-card]');
        const questionLabel = container.querySelector('[data-controller-question-label]');
        const questionText = container.querySelector('[data-controller-question-text]');
        const instruction = container.querySelector('[data-controller-instruction]');
        const imageShell = container.querySelector('[data-controller-image-shell]');
        const image = container.querySelector('[data-controller-image]');
        const answers = container.querySelector('[data-controller-answers]');
        const selection = container.querySelector('[data-controller-selection]');
        const previousButton = container.querySelector('[data-controller-previous]');
        const nextButton = container.querySelector('[data-controller-next]');
        const submitButton = container.querySelector('[data-controller-submit]');
        const actionHelper = container.querySelector('[data-controller-action-helper]');
        const resultText = container.querySelector('[data-controller-result-text]');
        const resultNote = container.querySelector('[data-controller-result-note]');
        const resultPdfLink = container.querySelector('[data-controller-result-pdf]');

        let currentVersion = 0;
        let currentQuestion = null;
        let currentState = null;
        let pollHandle = null;
        let requestInFlight = false;
        const timerRenderer = createTimerRenderer(timerTarget);

        const renderState = (state) => {
            currentVersion = state.session.state_version;
            currentState = state;
            currentQuestion = state.question;

            if (studentTarget) {
                studentTarget.textContent = state.student.name ?? '';
            }

            if (progressTarget) {
                progressTarget.textContent = `${state.progress.current} / ${state.progress.total}`;
            }

            if (statusTarget) {
                statusTarget.textContent = state.session.status_label;
            }

            timerRenderer.sync(state.attempt.time_remaining_seconds);

            if (state.session.status === 'submitted' || state.session.status === 'expired' || state.session.status === 'revoked') {
                placeholder?.classList.add('d-none');
                questionCard?.classList.add('d-none');
                resultCard?.classList.remove('d-none');

                if (resultText) {
                    resultText.textContent = state.session.status === 'submitted'
                        ? `${state.student.name}: ${state.attempt.score}%`
                        : state.messages.screen_expired;
                }

                if (resultNote) {
                    resultNote.textContent = state.result?.reason_label ?? '';
                    resultNote.classList.toggle('d-none', (state.result?.reason_label ?? '') === '');
                }

                if (resultPdfLink) {
                    const pdfUrl = state.result?.pdf_url ?? '';
                    resultPdfLink.classList.toggle('d-none', pdfUrl === '');
                    resultPdfLink.href = pdfUrl || '#';
                }

                return;
            }

            placeholder?.classList.add('d-none');
            questionCard?.classList.remove('d-none');
            resultCard?.classList.add('d-none');

            if (!state.question) {
                return;
            }

            if (questionLabel) {
                questionLabel.textContent = state.progress.label;
            }

            if (questionText) {
                questionText.textContent = state.question.text;
            }

            if (instruction) {
                instruction.textContent = state.question.instruction;
            }

            if (selection) {
                selection.textContent = state.question.selection_summary;
            }

            if (imageShell && image) {
                imageShell.classList.toggle('d-none', !state.question.image_url);
                if (state.question.image_url) {
                    image.src = state.question.image_url;
                }
            }

            if (answers) {
                const inputType = state.question.required_answers > 1 ? 'checkbox' : 'radio';
                const groupName = `controller-answer-${state.question.id}`;

                answers.innerHTML = state.question.answers
                    .map((answer) => createAnswerOptionMarkup(answer, inputType, answer.prefix, groupName))
                    .join('');
            }

            previousButton.disabled = !state.actions.can_previous || requestInFlight;
            nextButton.disabled = !state.actions.can_next || requestInFlight;
            submitButton.disabled = !state.actions.can_submit || requestInFlight;

            if (actionHelper) {
                const helperText = state.actions.helper_text ?? '';
                actionHelper.textContent = helperText;
                actionHelper.classList.toggle('d-none', helperText === '');
            }
        };

        const poll = async () => {
            if (requestInFlight) {
                return;
            }

            try {
                const state = await fetchJsonState(stateUrl, currentVersion);
                if (state) {
                    renderState(state);
                }
            } catch (error) {
                window.clearInterval(pollHandle);

                if (error.status === 403 && currentState) {
                    renderState({
                        ...currentState,
                        session: {
                            ...currentState.session,
                            status: 'expired',
                            status_label: expiredStatusLabel,
                        },
                    });

                    return;
                }

                showAlert(error.message);
            }
        };

        const runAction = async (url, payload) => {
            requestInFlight = true;
            previousButton.disabled = true;
            nextButton.disabled = true;
            submitButton.disabled = true;

            try {
                const state = await postDisplayAction(url, csrfToken, payload);
                renderState(state);
            } catch (error) {
                showAlert(error.message);
            } finally {
                requestInFlight = false;
                if (currentState) {
                    previousButton.disabled = !currentState.actions.can_previous;
                    nextButton.disabled = !currentState.actions.can_next;
                    submitButton.disabled = !currentState.actions.can_submit;
                }
            }
        };

        container.dataset.displayControllerBound = 'true';

        answers?.addEventListener('change', (event) => {
            const input = event.target.closest('input');
            if (!input || !currentQuestion) {
                return;
            }

            const selectedIds = Array.from(answers.querySelectorAll('input:checked')).map((element) => Number(element.value));
            runAction(answerUrl, { answer_ids: selectedIds });
        });

        previousButton?.addEventListener('click', () => {
            runAction(navigateUrl, { direction: 'previous' });
        });

        nextButton?.addEventListener('click', () => {
            runAction(navigateUrl, { direction: 'next' });
        });

        submitButton?.addEventListener('click', () => {
            runAction(submitUrl, {});
        });

        poll();
        pollHandle = window.setInterval(poll, pollInterval);
    });
}

function initRetroScreenTransitions() {
    document.querySelectorAll('.retro-screen').forEach((screen) => {
        if (screen.dataset.retroTransitionsBound === 'true') {
            return;
        }

        const beginExit = () => {
            if (screen.classList.contains('retro-screen--exiting')) {
                return;
            }

            screen.classList.add('retro-screen--exiting');
        };

        screen.dataset.retroTransitionsBound = 'true';

        screen.querySelectorAll('a[href]').forEach((anchor) => {
            anchor.addEventListener('click', (event) => {
                if (
                    event.defaultPrevented ||
                    anchor.target === '_blank' ||
                    anchor.hasAttribute('download') ||
                    anchor.getAttribute('href')?.startsWith('#') ||
                    event.metaKey ||
                    event.ctrlKey ||
                    event.shiftKey ||
                    event.altKey ||
                    event.button !== 0
                ) {
                    return;
                }

                event.preventDefault();
                beginExit();
                window.setTimeout(() => {
                    window.location.href = anchor.href;
                }, 170);
            });
        });

        screen.querySelectorAll('form').forEach((form) => {
            if (
                form.matches('[data-quiz-answer-form], [data-quiz-skip-form]') ||
                form.closest('[data-quiz-question-runtime]')
            ) {
                return;
            }

            form.addEventListener('submit', (event) => {
                if (form.dataset.retroSubmitting === 'true') {
                    return;
                }

                event.preventDefault();
                form.dataset.retroSubmitting = 'true';
                beginExit();
                window.setTimeout(() => form.submit(), 170);
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initQuizParticipantDisclaimer();
    initToasts();
    initConfirmSubmit();
    initSubmitFormButtons();
    initAutoSubmitControls();
    initClipboardButtons();
    initImagePreviewInputs();
    initRandomLimitToggles();
    initQuestionTemplateEditors();
    initStudentCsvValidation();
    initQuestionCsvValidation();
    initTemplateOwnershipToggles();
    initFocusTargets();
    initPasswordToggles();
    initCountdownRedirects();
    initTypingEffects();
    initTurnstileWidgets();
    initDropdowns();
    initAppModals();
    initModalAutoOpen();
    initQuizQuestionRuntime();
    initQuizDisplayScreen();
    initQuizDisplayController();
    initRetroScreenTransitions();
});
