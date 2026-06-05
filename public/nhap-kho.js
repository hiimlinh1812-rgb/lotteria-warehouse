/**
 * LOTTERIA – Module Nhập Kho
 * JS utilities: auto-dismiss alerts, form confirm, date validation
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Auto-dismiss flash alerts sau 5 giây ─────────────────────────────
    document.querySelectorAll('.alert').forEach(function (el) {
        // Không tự dismiss alert-danger (lỗi form cần giữ lại)
        if (el.classList.contains('alert-danger')) return;
        setTimeout(function () {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 500);
        }, 5000);
    });

    // ── Validate HSD > NSX trong form nhập số lượng ───────────────────────
    document.querySelectorAll('input[name*="[hsd]"]').forEach(function (hsdInput) {
        var maMatch = hsdInput.name.match(/\[([^\]]+)\]\[hsd\]/);
        if (!maMatch) return;
        var ma      = maMatch[1];
        var nsxInput = document.querySelector('input[name="nguyen_lieu[' + ma + '][nsx]"]');
        if (!nsxInput) return;

        function validateDates() {
            if (nsxInput.value && hsdInput.value) {
                if (hsdInput.value <= nsxInput.value) {
                    hsdInput.setCustomValidity('Hạn sử dụng phải sau ngày sản xuất.');
                    hsdInput.classList.add('is-invalid');
                } else {
                    hsdInput.setCustomValidity('');
                    hsdInput.classList.remove('is-invalid');
                }
            }
        }

        hsdInput.addEventListener('change', validateDates);
        nsxInput.addEventListener('change', validateDates);
    });

    // ── Set max ngày NSX = hôm nay ───────────────────────────────────────
    var today = new Date().toISOString().split('T')[0];
    document.querySelectorAll('input[name*="[nsx]"]').forEach(function (el) {
        el.setAttribute('max', today);
    });

    // ── Confirm submit cho các form nguy hiểm ─────────────────────────────
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var msg = form.dataset.confirm;
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // ── Highlight row khi hover trên bảng ─────────────────────────────────
    document.querySelectorAll('tbody tr').forEach(function (row) {
        row.style.cursor = 'pointer';
        row.addEventListener('mouseenter', function () {
            if (!row.classList.contains('row-mismatch')) {
                row.style.background = '#fef9f9';
            }
        });
        row.addEventListener('mouseleave', function () {
            if (!row.classList.contains('row-mismatch')) {
                row.style.background = '';
            }
        });
    });

    // ── Số lượng input: chỉ cho số nguyên dương ───────────────────────────
    document.querySelectorAll('.qty-input').forEach(function (input) {
        input.addEventListener('keypress', function (e) {
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });
        input.addEventListener('blur', function () {
            var val = parseInt(this.value);
            if (isNaN(val) || val < 0) this.value = 0;
        });
    });

    // ── Mặc định ngày nhận = hôm nay nếu input date trống ────────────────
    document.querySelectorAll('input[type="date"][data-default-today]').forEach(function (el) {
        if (!el.value) el.value = today;
    });
});
