(function($) {
    'use strict';

    $(document).ready(function() {
        initDailyChart();
        initCategoryChart();
        initComparisonForm();
        initPurgeForm();
        initExportForm();
        initQuickExport();
    });

    function initDailyChart() {
        var canvas = document.getElementById('geo-bot-chart-daily');
        if (!canvas || typeof geoBotChartData === 'undefined') return;

        new Chart(canvas, {
            type: 'line',
            data: geoBotChartData.daily,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    function initCategoryChart() {
        var canvas = document.getElementById('geo-bot-chart-categories');
        if (!canvas || typeof geoBotChartData === 'undefined') return;

        new Chart(canvas, {
            type: 'doughnut',
            data: geoBotChartData.categories,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function initComparisonForm() {
        $('#geo-bot-compare-btn').on('click', function() {
            var btn = $(this);
            var originalText = btn.text();
            
            btn.prop('disabled', true).text('Chargement...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'geo_bot_get_comparison',
                    nonce: $('#geo_bot_compare_nonce').val(),
                    period1_start: $('#period1_start').val(),
                    period1_end: $('#period1_end').val(),
                    period2_start: $('#period2_start').val(),
                    period2_end: $('#period2_end').val()
                },
                success: function(response) {
                    if (response.success) {
                        displayComparisonResults(response.data);
                    } else {
                        alert('Erreur lors de la comparaison');
                    }
                },
                error: function() {
                    alert('Erreur de communication avec le serveur');
                },
                complete: function() {
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });
    }

    function displayComparisonResults(data) {
        $('#geo-bot-comparison-results').show();

        $('#period1-dates').text(data.period1.start + ' - ' + data.period1.end);
        $('#period1-total').text(formatNumber(data.period1.total));

        $('#period2-dates').text(data.period2.start + ' - ' + data.period2.end);
        $('#period2-total').text(formatNumber(data.period2.total));

        var diffClass = data.diff > 0 ? 'diff-positive' : (data.diff < 0 ? 'diff-negative' : 'diff-neutral');
        var diffSign = data.diff > 0 ? '+' : '';

        $('#diff-value')
            .text(diffSign + formatNumber(data.diff))
            .removeClass('diff-positive diff-negative diff-neutral')
            .addClass(diffClass);

        $('#diff-percent')
            .text(diffSign + data.diff_percent + '%')
            .removeClass('diff-positive diff-negative diff-neutral')
            .addClass(diffClass);

        var categoryTbody = $('#comparison-by-category tbody');
        categoryTbody.empty();

        data.by_category.forEach(function(cat) {
            var varClass = cat.diff > 0 ? 'variation-positive' : (cat.diff < 0 ? 'variation-negative' : 'variation-neutral');
            var varSign = cat.diff > 0 ? '+' : '';

            categoryTbody.append(
                '<tr>' +
                '<td><strong>' + cat.label + '</strong></td>' +
                '<td>' + formatNumber(cat.period1) + '</td>' +
                '<td>' + formatNumber(cat.period2) + '</td>' +
                '<td class="' + varClass + '">' + varSign + formatNumber(cat.diff) + ' (' + varSign + cat.diff_percent + '%)</td>' +
                '</tr>'
            );
        });

        var botTbody = $('#comparison-by-bot tbody');
        botTbody.empty();

        data.by_bot.forEach(function(bot) {
            var varClass = bot.diff > 0 ? 'variation-positive' : (bot.diff < 0 ? 'variation-negative' : 'variation-neutral');
            var varSign = bot.diff > 0 ? '+' : '';

            botTbody.append(
                '<tr>' +
                '<td><strong>' + bot.bot_name + '</strong></td>' +
                '<td>' + formatNumber(bot.period1) + '</td>' +
                '<td>' + formatNumber(bot.period2) + '</td>' +
                '<td class="' + varClass + '">' + varSign + formatNumber(bot.diff) + ' (' + varSign + bot.diff_percent + '%)</td>' +
                '</tr>'
            );
        });

        updateComparisonChart(data);
    }

    function updateComparisonChart(data) {
        var canvas = document.getElementById('geo-bot-chart-comparison');
        if (!canvas) return;

        if (window.comparisonChart) {
            window.comparisonChart.destroy();
        }

        var labels = data.by_category.map(function(c) { return c.label; });
        var period1Data = data.by_category.map(function(c) { return c.period1; });
        var period2Data = data.by_category.map(function(c) { return c.period2; });

        window.comparisonChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Période 1',
                        data: period1Data,
                        backgroundColor: 'rgba(102, 126, 234, 0.7)',
                        borderColor: 'rgba(102, 126, 234, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Période 2',
                        data: period2Data,
                        backgroundColor: 'rgba(237, 100, 166, 0.7)',
                        borderColor: 'rgba(237, 100, 166, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    function initPurgeForm() {
        $('#select-all-months').on('change', function() {
            $('input[name="months[]"]').prop('checked', $(this).prop('checked'));
        });

        $('#geo-bot-purge-form').on('submit', function(e) {
            e.preventDefault();

            var months = $('input[name="months[]"]:checked');
            if (months.length === 0) {
                alert('Veuillez sélectionner au moins un mois');
                return;
            }

            if (!confirm('Êtes-vous sûr de vouloir supprimer les données de ' + months.length + ' mois ? Cette action est irréversible.')) {
                return;
            }

            var btn = $('#purge-btn');
            btn.prop('disabled', true).text('Suppression...');

            var monthValues = [];
            months.each(function() {
                monthValues.push($(this).val());
            });

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'geo_bot_purge',
                    nonce: $('#geo_bot_purge_nonce').val(),
                    months: monthValues
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Erreur: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Erreur de communication avec le serveur');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Supprimer les mois sélectionnés');
                }
            });
        });
    }

    function initExportForm() {
        $('input[name="export_type"]').on('change', function() {
            if ($(this).val() === 'month') {
                $('#export-month-selector').show();
                $('#export-range-selector').hide();
            } else {
                $('#export-month-selector').hide();
                $('#export-range-selector').show();
            }
        });

        $('#geo-bot-export-form').on('submit', function(e) {
            e.preventDefault();
            performExport();
        });
    }

    function performExport(customParams) {
        var params = customParams || {};

        if (!params.format) {
            params.format = $('input[name="format"]:checked').val();
        }

        var exportType = $('input[name="export_type"]:checked').val();

        if (!params.month && !params.start_date) {
            if (exportType === 'month') {
                params.month = $('#export_month').val();
                params.year = $('#export_year').val();
            } else {
                params.start_date = $('#export_start').val();
                params.end_date = $('#export_end').val();
            }
        }

        var form = $('<form>', {
            method: 'POST',
            action: ajaxurl
        });

        form.append($('<input>', { type: 'hidden', name: 'action', value: 'geo_bot_export' }));
        form.append($('<input>', { type: 'hidden', name: 'nonce', value: $('#geo_bot_export_nonce').val() }));

        for (var key in params) {
            if (params[key]) {
                form.append($('<input>', { type: 'hidden', name: key, value: params[key] }));
            }
        }

        $('body').append(form);
        form.submit();
        form.remove();
    }

    function initQuickExport() {
        $('.quick-export').on('click', function() {
            var monthYear = $(this).data('month');
            var format = $(this).data('format');
            var parts = monthYear.split('-');

            performExport({
                format: format,
                month: parts[1],
                year: parts[0]
            });
        });
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

})(jQuery);
