function initializeAdmin() {
    window.lucide?.createIcons();
    const body = document.body;
    const mobile = window.matchMedia('(max-width: 768px)');
    const tablet = window.matchMedia('(min-width: 769px) and (max-width: 1024px)');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const readPreference = (key) => {
        try { return localStorage.getItem(key) === 'true'; } catch { return false; }
    };
    const savePreference = (key, value) => {
        const status = document.querySelector('[data-preferences-status]');
        try {
            localStorage.setItem(key, String(value));
            status?.classList.remove('is-error');
            if (status) status.querySelector('span').textContent = 'Préférences enregistrées sur cet appareil';
            return true;
        } catch {
            status?.classList.add('is-error');
            if (status) status.querySelector('span').textContent = 'Stockage indisponible. Modifications valables pour cette page.';
            return false;
        }
    };
    let sidebarReturnFocus;
    function setSidebar(open) {
        body.classList.toggle('sidebar-open', open);
        overlay.hidden = !open;
        document.querySelector('.mobile-menu')?.setAttribute('aria-expanded', String(open));
        sidebar.inert = mobile.matches && !open;
        if (mobile.matches) {
            document.querySelector('.main').inert = open;
        }
        if (open) {
            sidebarReturnFocus = document.activeElement;
            sidebar.querySelector('a')?.focus();
        } else if (sidebarReturnFocus) {
            sidebarReturnFocus.focus();
            sidebarReturnFocus = null;
        }
    }
    function syncLayout() {
        body.classList.toggle('sidebar-compact', !mobile.matches && readPreference('ga-compact'));
        document.querySelector('.main').inert = false;
        setSidebar(false);
    }
    syncLayout();
    mobile.addEventListener('change', syncLayout);
    document.querySelectorAll('[data-toggle-sidebar]').forEach((button) => {
        button.addEventListener('click', () => {
            if (mobile.matches) {
                setSidebar(!body.classList.contains('sidebar-open'));
            } else if (tablet.matches) {
                body.classList.remove('sidebar-compact');
                body.classList.toggle('sidebar-expanded');
            } else {
                const compact = body.classList.toggle('sidebar-compact');
                savePreference('ga-compact', compact);
                document.querySelector('#compact-navigation').checked = compact;
            }
        });
    });
    overlay.addEventListener('click', () => setSidebar(false));
    document.querySelector('#compact-navigation').checked = readPreference('ga-compact');
    document.querySelector('#compact-navigation').addEventListener('change', (event) => {
        savePreference('ga-compact', event.target.checked);
        body.classList.remove('sidebar-expanded');
        body.classList.toggle('sidebar-compact', event.target.checked && !mobile.matches);
    });
    body.classList.toggle('reduce-motion', readPreference('ga-reduce-motion'));
    document.querySelector('#reduce-motion').checked = readPreference('ga-reduce-motion');
    document.querySelector('#reduce-motion').addEventListener('change', (event) => {
        savePreference('ga-reduce-motion', event.target.checked);
        body.classList.toggle('reduce-motion', event.target.checked);
    });
    let density = 'comfortable';
    try { if (localStorage.getItem('ga-table-density') === 'compact') density = 'compact'; } catch { /* Use the default density. */ }
    function applyDensity(value) {
        body.classList.toggle('table-density-compact', value === 'compact');
        document.querySelectorAll('[name="table-density"]').forEach((input) => { input.checked = input.value === value; });
    }
    applyDensity(density);
    document.querySelectorAll('[name="table-density"]').forEach((input) => input.addEventListener('change', () => {
        applyDensity(input.value);
        savePreference('ga-table-density', input.value);
    }));
    document.querySelector('[data-reset-preferences]')?.addEventListener('click', () => {
        body.classList.remove('sidebar-compact', 'sidebar-expanded', 'reduce-motion');
        document.querySelector('#compact-navigation').checked = false;
        document.querySelector('#reduce-motion').checked = false;
        applyDensity('comfortable');
        const results = [
            savePreference('ga-compact', false),
            savePreference('ga-reduce-motion', false),
            savePreference('ga-table-density', 'comfortable'),
        ];
        if (results.every(Boolean)) document.querySelector('[data-preferences-status] span').textContent = 'Préférences réinitialisées';
    });

    let dialogReturnFocus;
    function openDialog(dialog, trigger) {
        if (!dialog) return;
        if (dialog.open) return;
        const previousDialog = document.querySelector('dialog[open]');
        if (!previousDialog) dialogReturnFocus = trigger ?? document.activeElement;
        if (body.classList.contains('sidebar-open')) {
            setSidebar(false);
            dialogReturnFocus = document.querySelector('.mobile-menu');
        }
        previousDialog?.close();
        document.querySelectorAll('details[open]').forEach((details) => details.removeAttribute('open'));
        dialog.showModal();
    }
    document.querySelectorAll('[data-open-dialog]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const dialog = document.getElementById(trigger.dataset.openDialog);
            if (trigger.dataset.openDialog === 'absence-dialog') {
                const employeeInput = dialog.querySelector('[name="user_id"]');
                employeeInput.value = trigger.dataset.employee ?? '';
            }
            if (trigger.dataset.profile) {
                const profile = JSON.parse(trigger.dataset.profile);
                dialog.querySelectorAll('[data-profile-field]').forEach((field) => {
                    const key = field.dataset.profileField;
                    field.textContent = key === 'status' ? (profile[key] === 'active' ? 'Actif' : 'Inactif') : (profile[key] || '—');
                });
            }
            if (trigger.dataset.attendance) {
                const attendance = JSON.parse(trigger.dataset.attendance);
                ['user_id', 'check_in_time', 'check_out_time'].forEach((key) => {
                    dialog.querySelector('[name="' + key + '"]').value = attendance[key] ?? '';
                });
                dialog.querySelector('[data-attendance-name]').textContent = attendance.name;
            }
            openDialog(dialog, trigger);
        });
    });
    document.querySelectorAll('[data-close-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog').close()));
    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target !== dialog) return;
            const rect = dialog.getBoundingClientRect();
            if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
        });
        dialog.addEventListener('close', () => {
            if (document.querySelector('dialog[open]')) return;
            const details = dialogReturnFocus?.closest('details');
            if (details && !details.open) details.querySelector('summary')?.focus();
            else dialogReturnFocus?.focus();
        });
        if (dialog.hasAttribute('data-reopen')) openDialog(dialog);
    });
    document.addEventListener('keydown', (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            if (!document.querySelector('dialog[open]')) openDialog(document.querySelector('#search-dialog'));
        }
        if (event.key === 'Escape') {
            if (body.classList.contains('sidebar-open')) setSidebar(false);
            document.querySelectorAll('details[open]').forEach((details) => {
                details.removeAttribute('open');
                details.querySelector('summary')?.focus();
            });
        }
        if (event.key === 'Tab' && body.classList.contains('sidebar-open')) {
            const focusables = [...sidebar.querySelectorAll('a, button, summary')].filter((item) => item.getClientRects().length);
            const first = focusables[0], last = focusables.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    function placeMenu(details) {
        const panel = details.querySelector('.dropdown-panel');
        const rect = details.querySelector('summary').getBoundingClientRect();
        panel.style.left = Math.max(8, Math.min(window.innerWidth - panel.offsetWidth - 8, rect.right - panel.offsetWidth)) + 'px';
        panel.style.right = 'auto';
        panel.style.top = (rect.bottom + panel.offsetHeight + 8 > window.innerHeight ? Math.max(8, rect.top - panel.offsetHeight - 5) : rect.bottom + 5) + 'px';
    }
    document.querySelectorAll('details.dropdown').forEach((details) => {
        details.addEventListener('toggle', () => {
            if (!details.open) return;
            document.querySelectorAll('details.dropdown[open]').forEach((other) => {
                if (other !== details) other.removeAttribute('open');
            });
            if (details.hasAttribute('data-floating-menu')) placeMenu(details);
        });
    });
    document.addEventListener('click', (event) => {
        document.querySelectorAll('details.dropdown[open]').forEach((details) => {
            if (!details.contains(event.target)) details.removeAttribute('open');
        });
    });
    window.addEventListener('resize', () => document.querySelectorAll('[data-floating-menu][open]').forEach(placeMenu));
    document.addEventListener('scroll', (event) => {
        if (event.target instanceof Element && event.target.closest('.dropdown-panel')) return;
        document.querySelectorAll('[data-floating-menu][open]').forEach((details) => details.removeAttribute('open'));
    }, true);

    document.querySelectorAll('[data-dismiss-toast]').forEach((button) => button.addEventListener('click', () => button.closest('[data-toast]').remove()));
    document.querySelectorAll('form[data-submit-state]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.classList.contains('is-submitting')) { event.preventDefault(); return; }
            form.classList.add('is-submitting');
            form.setAttribute('aria-busy', 'true');
        });
    });
    document.querySelectorAll('[data-filter-form]').forEach((form) => {
        form.querySelectorAll('input').forEach((input) => input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                form.requestSubmit(form.querySelector('.filter-apply'));
            }
        }));
        // Preserve the selected status when submitting a search with Enter.
        form.addEventListener('submit', (event) => {
            if (!event.submitter?.name) {
                const selected = form.querySelector('[aria-pressed="true"]');
                const hidden = document.createElement('input');
                hidden.type = 'hidden'; hidden.name = 'status'; hidden.value = selected?.value ?? '';
                form.append(hidden);
            }
            form.classList.add('is-loading');
            form.setAttribute('aria-busy', 'true');
        });
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('.is-submitting, .is-loading').forEach((element) => {
            element.classList.remove('is-submitting', 'is-loading');
            element.removeAttribute('aria-busy');
        });
    });

    const canvas = document.querySelector('#weekly-chart');
    if (canvas && window.Chart) {
        const values = JSON.parse(canvas.dataset.values);
        const maximum = Math.max(4, ...values.flatMap((day) => [day.present ?? 0, day.absent ?? 0]));
        new window.Chart(canvas, {
            type: 'bar',
            data: {
                labels: values.map((day) => day.label),
                datasets: [
                    { label: 'Présents', data: values.map((day) => day.present), backgroundColor: '#36866a', borderRadius: 3, maxBarThickness: 20 },
                    { label: 'Absents', data: values.map((day) => day.absent), backgroundColor: '#e59898', borderRadius: 3, maxBarThickness: 20 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: { duration: matchMedia('(prefers-reduced-motion: reduce)').matches || readPreference('ga-reduce-motion') ? 0 : 220 },
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#fff', titleColor: '#101828', bodyColor: '#667085', borderColor: '#e4e7ec', borderWidth: 1, padding: 12, cornerRadius: 6 } },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { color: '#667085', font: { family: 'Inter', size: 10 } } },
                    y: { beginAtZero: true, suggestedMax: maximum, border: { display: false }, grid: { color: '#f0f2f5' }, ticks: { precision: 0, maxTicksLimit: 5, color: '#98a2b3', font: { family: 'Inter', size: 10 } } },
                },
            },
        });
    }
    document.querySelector('[data-chart-loading]')?.remove();
    if (canvas && !window.Chart) {
        const fallback = document.createElement('p');
        fallback.className = 'chart-empty';
        fallback.textContent = 'Le graphique n’a pas pu être chargé. Actualisez la page.';
        canvas.replaceWith(fallback);
        document.querySelector('#weekly-data')?.classList.remove('visually-hidden');
    }
}

if (document.readyState !== 'complete') document.addEventListener('DOMContentLoaded', initializeAdmin, { once: true });
else initializeAdmin();
