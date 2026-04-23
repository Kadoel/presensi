const KadoelAjax = {
    showNotif(type = 'info', posisi = 'right', pesan = '') {
        if (!pesan) return;

        if (typeof window.notifikasi === 'function') {
            window.notifikasi(type, posisi, pesan);
            return;
        }

        if (typeof Codebase !== 'undefined') {
            Codebase.helpers('jq-notify', {
                align: posisi,
                from: 'top',
                type: type,
                icon: type === 'info'
                    ? 'fa fa-circle-info me-2'
                    : (type === 'success'
                        ? 'fa fa-circle-check me-2'
                        : 'fa fa-bug me-2'),
                message: pesan
            });
            return;
        }

        console.warn('Notifikasi:', pesan);
    },

    clearErrors(fields = []) {
        if (Array.isArray(fields) && fields.length > 0) {
            fields.forEach(function (field) {
                const $input = $('#' + field);
                const $error = $('#error-' + field);

                if ($input.length) {
                    $input.removeClass('is-invalid');
                }

                if ($error.length) {
                    $error.html('').hide();
                }
            });

            return;
        }

        $('.is-invalid').removeClass('is-invalid');
        $('[id^="error-"]').html('').hide();
    },

    applyErrors(errors = {}) {
        if (!errors || typeof errors !== 'object') {
            return;
        }

        Object.entries(errors).forEach(function ([key, value]) {
            const $input = $('#' + key);
            const $error = $('#error-' + key);

            if ($input.length) {
                $input.addClass('is-invalid');
            }

            if ($error.length) {
                $error.html(value).show();
            }
        });
    },

    handleError(result, options = {}) {
        const type = options.type || 'danger';
        const position = options.position || 'right';
        const clearFields = options.clearFields || [];

        this.clearErrors(clearFields);

        if (!result || typeof result !== 'object') {
            return;
        }

        if (result.errors && typeof result.errors === 'object') {
            this.applyErrors(result.errors);
        }

        if (result.pesan) {
            this.showNotif(type, position, result.pesan);
            return;
        }

        if (result.errors && result.errors.general) {
            this.showNotif(type, position, result.errors.general);
        }
    }
};