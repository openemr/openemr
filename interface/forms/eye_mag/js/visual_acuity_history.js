/* Dynamic Visual Acuity History refresh and controls. */
(function () {
    /** Reload saved visual-acuity history and replace the previous chart instance. */
    function refreshVisualAcuityHistory() {
        var formData = {
            action: 'refresh',
            id: $('#form_id').val(),
            encounter: $('#encounter').val(),
            pid: $('#pid').val(),
            refresh: 'VAHx'
        };
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        return $.ajax({
            type: 'POST',
            url: '../../forms/eye_mag/view.php?display=VAHx',
            data: formData
        }).done(function (result) {
            if (window.eyeMagVaChart) {
                window.eyeMagVaChart.destroy();
                window.eyeMagVaChart = null;
            }
            $('#LayerVision_VAHx').html(result);
            $('#Close_VAHx').on('click.eyeMagVaHistory', function () {
                $('#LayerVision_VAHx_lightswitch').trigger('click');
            });
        });
    }

    /** Update this visit's pressure values and refresh time-based history when available. */
    function refreshGlaucomaFlowSheetOnLoad() {
        if (typeof window.config_byday === 'undefined' || typeof window.myLine === 'undefined') return;
        var visitIndex = window.config_byday.data.labels.indexOf(window.visit_date);
        if (visitIndex < 0) return;

        var odIop = $('#ODIOPAP').val() || $('#ODIOPTPN').val() || 0;
        var osIop = $('#OSIOPAP').val() || $('#OSIOPTPN').val() || 0;
        window.config_byday.data.datasets[0].data[visitIndex] = $('#ODIOPTARGET').val() || 0;
        window.config_byday.data.datasets[1].data[visitIndex] = $('#OSIOPTARGET').val() || 0;
        window.config_byday.data.datasets[2].data[visitIndex] = odIop;
        window.config_byday.data.datasets[3].data[visitIndex] = osIop;
        window.myLine.update();

        if (/^\d{1,2}:\d{2}/.test($('#IOPTIME').val() || '') && typeof window.refresh_GFS === 'function') {
            window.refresh_GFS();
        }
    }

    $(function () {
        var vaFieldPattern = /^(?:SC(?:OD|OS)VA|(?:OD|OS)VA_1|(?:PH|AR|MR|CR|CTL)(?:OD|OS)VA)$/;
        var vaRefreshPending = false;
        var vaRefreshTimer = null;

        $('#eye_mag').on('change.eyeMagVaHistory', 'input', function () {
            var fieldName = (this.name || '').replace(/_copy(?:_brd)?$/, '');
            if (!vaFieldPattern.test(fieldName)) return;
            vaRefreshPending = true;
        });

        // ajaxSuccess excludes failed HTTP requests and response conversion errors.
        $(document).on('ajaxSuccess.eyeMagVaHistorySave', function (event, xhr, settings) {
            if (!vaRefreshPending || !settings.url || settings.url.indexOf('/eye_mag/save.php') === -1) return;
            // The form uses HTTP 200 with this sentinel when another user owns it.
            if (String(xhr.responseText || '').trim() === 'Code 400') return;
            window.clearTimeout(vaRefreshTimer);
            vaRefreshTimer = window.setTimeout(function () {
                vaRefreshPending = false;
                refreshVisualAcuityHistory();
            }, 150);
        });

        refreshGlaucomaFlowSheetOnLoad();
    });
})();
