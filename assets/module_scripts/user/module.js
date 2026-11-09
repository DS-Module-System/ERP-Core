'use strict';

function syncRealCheckbox(cb) {
    const roleKey = cb.dataset.roleKey;
    const real = document.querySelector(
        `input.real-role-field[value="${roleKey}"]`
    );

    if (!real) return;

    real.checked = cb.checked;

    if (cb.checked) {
        real.setAttribute('checked', 'checked');
    } else {
        real.removeAttribute('checked');
    }
}

function updateGroupAndGlobal() {
    // Update each group's "check all"
    document.querySelectorAll('.card').forEach(card => {
        const groupCheckAll = card.querySelector('.check_all_group');
        const boxes = card.querySelectorAll('.role-checkbox');

        if (groupCheckAll) {
            groupCheckAll.checked = Array.from(boxes).every(cb => cb.checked);
        }
    });

    // Update global "check all roles"
    const checkAllRoles = document.getElementById('check_all_roles');
    if (checkAllRoles) {
        checkAllRoles.checked = Array.from(
            document.querySelectorAll('.check_all_group')
        ).every(cb => cb.checked);
    }
}

function initializeRoleCheckboxes() {
    document.querySelectorAll('.role-checkbox').forEach(cb => {
        const roleKey = cb.dataset.roleKey;
        const real = document.querySelector(`input.real-role-field[value="${roleKey}"]`);
        cb.checked = real ? real.checked : false;
    });

    updateGroupAndGlobal();
}

function checkBoxUserGroups() {
    const checkAllRoles = document.getElementById('check_all_roles');

    // Individual role checkboxes
    document.querySelectorAll('.role-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            syncRealCheckbox(this);
            updateGroupAndGlobal();
        });
    });

    // GROUP checkboxes
    document.querySelectorAll('.check_all_group').forEach(groupCb => {
        groupCb.addEventListener('change', function () {
            const checkboxes = this.closest('.card').querySelectorAll('.role-checkbox');

            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                syncRealCheckbox(cb);
            });

            updateGroupAndGlobal();
        });
    });

    // GLOBAL "check all roles"
    if (checkAllRoles) {
        checkAllRoles.addEventListener('change', function () {
            document.querySelectorAll('.role-checkbox').forEach(cb => {
                cb.checked = this.checked;
                syncRealCheckbox(cb);
            });

            document.querySelectorAll('.check_all_group').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initializeRoleCheckboxes();
    checkBoxUserGroups();
});


$(document).ready( () => {
    checkBoxUserGroups();
    initializeRoleCheckboxes();
});