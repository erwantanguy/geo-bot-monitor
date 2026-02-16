(function($) {
    'use strict';

    $(document).ready(function() {
        initDailyChart();
        initCategoryChart();
        initComparisonForm();
        initPurgeForm();
        initExportForm();
        initQuickExport();
        initBotBlocker();
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

    function initBotBlocker() {
        var $blockTable = $('#geo-bot-block-table');
        if (!$blockTable.length) return;

        $('#select-all-bots').on('change', function() {
            $blockTable.find('input[name="bots[]"]').prop('checked', $(this).prop('checked'));
        });

        $('#geo-bot-generate-btn').on('click', function() {
            var selectedBots = [];
            $blockTable.find('input[name="bots[]"]:checked').each(function() {
                selectedBots.push($(this).val());
            });

            if (selectedBots.length === 0) {
                alert('Veuillez sélectionner au moins un bot');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).text('Génération...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'geo_bot_generate_block_code',
                    nonce: $('#geo_bot_block_nonce').val(),
                    bots: selectedBots,
                    robots: $('#gen-robots').prop('checked'),
                    llms: $('#gen-llms').prop('checked'),
                    htaccess: $('#gen-htaccess').prop('checked')
                },
                success: function(response) {
                    if (response.success) {
                        displayGeneratedCode(response.data);
                    } else {
                        alert('Erreur: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Erreur de communication avec le serveur');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Générer le code');
                }
            });
        });

        $('.geo-bot-quick-block').on('click', function() {
            var botName = $(this).data('bot');
            var category = $(this).data('category');
            openBotBlockModal(botName, category);
        });

        $('.geo-bot-modal-close').on('click', function() {
            $(this).closest('.geo-bot-modal').hide();
        });

        $('.geo-bot-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });

        $(document).on('click', '.geo-bot-copy-btn', function() {
            var targetId = $(this).data('target');
            var $pre = $('#' + targetId);
            var text = $pre.text();
            
            copyToClipboard(text, $(this));
        });

        $('#download-robots-btn').on('click', function() {
            downloadFile('robots.txt', $('#robots-code').text());
        });

        $('#download-llms-btn').on('click', function() {
            downloadFile('llms.txt', $('#llms-code').text());
        });

        $('#gen-llms').on('change', function() {
            $('#llms-section').toggle($(this).prop('checked'));
            $('#download-llms-btn').toggle($(this).prop('checked'));
        });

        $('#gen-htaccess').on('change', function() {
            $('#htaccess-section').toggle($(this).prop('checked'));
        });

        $('#gen-robots').on('change', function() {
            $('#robots-section').toggle($(this).prop('checked'));
            $('#download-robots-btn').toggle($(this).prop('checked'));
        });

        $('#geo-bot-sync-robots').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).find('.dashicons').addClass('dashicons-update-spin');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'geo_bot_sync_robots',
                    nonce: geoBotAdmin.nonces.block
                },
                success: function(response) {
                    if (response.success) {
                        showNotice(response.data.message, 'success');
                        if (response.data.synced > 0) {
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        showNotice(response.data.message || 'Erreur lors de la synchronisation', 'error');
                    }
                },
                error: function() {
                    showNotice('Erreur de communication avec le serveur', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).find('.dashicons').removeClass('dashicons-update-spin');
                }
            });
        });
    }

    function displayGeneratedCode(data) {
        $('#geo-bot-generated-code').show();

        if (data.robots) {
            $('#robots-code').text(data.robots);
            $('#robots-section').show();
            $('#download-robots-btn').show();
        } else {
            $('#robots-section').hide();
            $('#download-robots-btn').hide();
        }

        if (data.llms) {
            $('#llms-code').text(data.llms);
            $('#llms-section').show();
            $('#download-llms-btn').show();
        } else {
            $('#llms-section').hide();
            $('#download-llms-btn').hide();
        }

        if (data.htaccess) {
            $('#htaccess-code').text(data.htaccess);
            $('#htaccess-section').show();
        } else {
            $('#htaccess-section').hide();
        }

        $('html, body').animate({
            scrollTop: $('#geo-bot-generated-code').offset().top - 50
        }, 500);
    }

    function openBotBlockModal(botName, category) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'geo_bot_get_bot_code',
                nonce: $('#geo_bot_block_nonce').val(),
                bot: botName,
                category: category
            },
            success: function(response) {
                if (response.success) {
                    showBotBlockModal(response.data);
                } else {
                    alert('Erreur: ' + response.data.message);
                }
            }
        });
    }

    function showBotBlockModal(data) {
        var $modal = $('#geo-bot-block-modal');
        var isBlocked = $('tr[data-bot="' + data.bot_name + '"]').find('.geo-bot-status-blocked').length > 0;
        
        $('#modal-bot-name').text('Bloquer ' + data.bot_name);

        var infoHtml = '<div class="geo-bot-modal-info">';
        infoHtml += '<p><strong>User-Agent:</strong> <code>' + data.user_agent + '</code></p>';
        
        if (data.is_ai) {
            infoHtml += '<span class="geo-bot-tag geo-bot-tag-ai">Bot IA</span> ';
        }
        if (data.is_seo) {
            infoHtml += '<span class="geo-bot-tag geo-bot-tag-seo">Bot SEO</span> ';
        }
        if (data.warning) {
            infoHtml += '<div class="geo-bot-modal-warning"><span class="dashicons dashicons-warning"></span> ' + data.warning + '</div>';
        }
        infoHtml += '</div>';
        
        $('#modal-bot-info').html(infoHtml);

        var sectionsHtml = '';

        sectionsHtml += '<div class="geo-bot-code-section">';
        sectionsHtml += '<h4>robots.txt</h4>';
        sectionsHtml += '<p class="description">Ajoutez ces lignes à votre fichier robots.txt</p>';
        sectionsHtml += '<div class="geo-bot-code-block"><pre id="modal-robots-code">' + escapeHtml(data.robots) + '</pre>';
        sectionsHtml += '<button type="button" class="button geo-bot-copy-btn" data-target="modal-robots-code"><span class="dashicons dashicons-clipboard"></span> Copier</button>';
        sectionsHtml += '</div></div>';

        if (data.llms) {
            sectionsHtml += '<div class="geo-bot-code-section">';
            sectionsHtml += '<h4>llms.txt (recommandé pour les IA)</h4>';
            sectionsHtml += '<p class="description">Créez ce fichier à la racine de votre site</p>';
            sectionsHtml += '<div class="geo-bot-code-block"><pre id="modal-llms-code">' + escapeHtml(data.llms) + '</pre>';
            sectionsHtml += '<button type="button" class="button geo-bot-copy-btn" data-target="modal-llms-code"><span class="dashicons dashicons-clipboard"></span> Copier</button>';
            sectionsHtml += '</div></div>';
        }

        sectionsHtml += '<div class="geo-bot-code-section">';
        sectionsHtml += '<h4>.htaccess (blocage serveur)</h4>';
        sectionsHtml += '<p class="description">Pour un blocage strict au niveau Apache</p>';
        sectionsHtml += '<div class="geo-bot-code-block"><pre id="modal-htaccess-code">' + escapeHtml(data.htaccess) + '</pre>';
        sectionsHtml += '<button type="button" class="button geo-bot-copy-btn" data-target="modal-htaccess-code"><span class="dashicons dashicons-clipboard"></span> Copier</button>';
        sectionsHtml += '</div></div>';

        sectionsHtml += '<div class="geo-bot-modal-actions">';
        if (isBlocked) {
            sectionsHtml += '<button type="button" class="button" id="modal-unblock-btn" data-bot="' + escapeHtml(data.bot_name) + '"><span class="dashicons dashicons-unlock"></span> Retirer du blocage</button>';
        } else {
            sectionsHtml += '<button type="button" class="button button-primary" id="modal-block-btn" data-bot="' + escapeHtml(data.bot_name) + '" data-is-ai="' + (data.is_ai ? '1' : '0') + '"><span class="dashicons dashicons-lock"></span> Ajouter au blocage</button>';
        }
        if (data.is_ai) {
            sectionsHtml += '<p class="description geo-bot-authority-note"><span class="dashicons dashicons-info"></span> Si GEO Authority Suite est installé, ce bot sera automatiquement ajouté au fichier llms.txt généré.</p>';
        }
        sectionsHtml += '</div>';

        $('#modal-code-sections').html(sectionsHtml);

        $('#modal-block-btn').on('click', function() {
            saveBotBlock($(this).data('bot'), 'add', $(this).data('is-ai') === 1);
        });

        $('#modal-unblock-btn').on('click', function() {
            saveBotBlock($(this).data('bot'), 'remove', false);
        });

        $modal.show();
    }

    function saveBotBlock(botName, action, isAi) {
        var methods = ['robots'];
        if (isAi) {
            methods.push('llms');
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'geo_bot_save_blocked',
                nonce: $('#geo_bot_block_nonce').val(),
                bot: botName,
                block_action: action,
                methods: methods
            },
            success: function(response) {
                if (response.success) {
                    var $row = $('tr[data-bot="' + botName + '"]');
                    var $statusCell = $row.find('td:eq(4)');
                    var $checkbox = $row.find('input[name="bots[]"]');
                    
                    if (action === 'add') {
                        $statusCell.html('<span class="geo-bot-status geo-bot-status-blocked">Bloqué</span>');
                        $checkbox.prop('checked', true);
                    } else {
                        $statusCell.html('<span class="geo-bot-status geo-bot-status-allowed">Autorisé</span>');
                        $checkbox.prop('checked', false);
                    }
                    
                    $('#geo-bot-block-modal').hide();
                    
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('Erreur de communication avec le serveur', 'error');
            }
        });
    }

    function showNotice(message, type) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var $notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('.wrap.geo-bot-dashboard h1').after($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    function copyToClipboard(text, $btn) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                showCopySuccess($btn);
            }).catch(function() {
                fallbackCopy(text, $btn);
            });
        } else {
            fallbackCopy(text, $btn);
        }
    }

    function fallbackCopy(text, $btn) {
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
        showCopySuccess($btn);
    }

    function showCopySuccess($btn) {
        var originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-yes"></span> Copié !').addClass('button-primary');
        setTimeout(function() {
            $btn.html(originalText).removeClass('button-primary');
        }, 2000);
    }

    function downloadFile(filename, content) {
        var blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

})(jQuery);
