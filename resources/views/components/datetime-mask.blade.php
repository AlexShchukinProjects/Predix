<script>
// Универсальная маска для полей даты/времени "ЧЧ:ММ ДД.ММ.ГГ"
if (typeof window.initDateTimeMask !== 'function') {
    window.initDateTimeMask = function(input) {
        if (!input) return;

        if (input._maskInited) {
            return;
        }
        input._maskInited = true;

        const template = '--:-- ДД.ММ.ГГ';
        const digitPositions = [0,1,3,4,6,7,9,10,12,13];
        const placeholders = {
            0: '-', 1: '-', 3: '-', 4: '-',
            6: 'Д', 7: 'Д', 9: 'М', 10: 'М', 12: 'Г', 13: 'Г'
        };

        function setCaretToNext(pos) {
            for (let i = 0; i < digitPositions.length; i++) {
                if (digitPositions[i] > pos) {
                    const next = digitPositions[i];
                    input.setSelectionRange(next, next);
                    return;
                }
            }
            const last = digitPositions[digitPositions.length - 1] + 1;
            input.setSelectionRange(last, last);
        }

        function setCaretToPrev(pos) {
            for (let i = digitPositions.length - 1; i >= 0; i--) {
                if (digitPositions[i] < pos) {
                    const prev = digitPositions[i];
                    input.setSelectionRange(prev, prev);
                    return;
                }
            }
            input.setSelectionRange(0, 0);
        }

        input.addEventListener('focus', function() {
            if (!input.value) {
                input.value = template;
            } else if (input.value.length !== template.length) {
                input.value = template;
            }
            const firstIdx = input.value.indexOf('-');
            const pos = firstIdx !== -1 ? firstIdx : digitPositions[0];
            input.setSelectionRange(pos, pos);
        });

        input.addEventListener('blur', function() {
            const digitsOnly = (input.value || '').replace(/\D/g, '');
            if (!digitsOnly) {
                input.value = '';
            }
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });

        input.addEventListener('keydown', function(e) {
            const allowedNav = ['Tab','ArrowLeft','ArrowRight','Home','End'];
            if (allowedNav.includes(e.key)) {
                return;
            }

            const isBackspace = e.key === 'Backspace';
            const isDelete = e.key === 'Delete';
            const isDigit = /^[0-9]$/.test(e.key);

            if (!isBackspace && !isDelete && !isDigit) {
                e.preventDefault();
                return;
            }

            e.preventDefault();

            let value = input.value || template;
            let pos = input.selectionStart || 0;

            if (isBackspace) {
                setCaretToPrev(pos);
                pos = input.selectionStart || 0;
                if (!digitPositions.includes(pos)) return;
                const placeholder = placeholders[pos] || '-';
                value = value.substring(0, pos) + placeholder + value.substring(pos + 1);
                input.value = value;
                input.setSelectionRange(pos, pos);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                return;
            }

            if (isDelete) {
                if (!digitPositions.includes(pos)) return;
                const placeholder = placeholders[pos] || '-';
                value = value.substring(0, pos) + placeholder + value.substring(pos + 1);
                input.value = value;
                input.setSelectionRange(pos, pos);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                return;
            }

            if (isDigit) {
                if (!digitPositions.includes(pos)) {
                    setCaretToNext(pos);
                    pos = input.selectionStart || 0;
                    if (!digitPositions.includes(pos)) return;
                }
                value = value.substring(0, pos) + e.key + value.substring(pos + 1);
                input.value = value;
                setCaretToNext(pos);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                return;
            }
        });
    };
}

// Автоматическая инициализация для инпутов с классом .efds-datetime-input
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input.efds-datetime-input').forEach(function(input) {
        if (typeof window.initDateTimeMask === 'function') {
            window.initDateTimeMask(input);
        }
    });
});
</script>

