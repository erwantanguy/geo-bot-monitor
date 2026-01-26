(function($) {
    'use strict';

    if (typeof geoBotSettings === 'undefined') {
        return;
    }

    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        
        return new Promise(function(resolve, reject) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                var success = document.execCommand('copy');
                document.body.removeChild(textarea);
                if (success) {
                    resolve();
                } else {
                    reject(new Error('execCommand copy failed'));
                }
            } catch (err) {
                document.body.removeChild(textarea);
                reject(err);
            }
        });
    }

    $(document).ready(function() {
        $('#generate-api-key').on('click', function() {
            var btn = $(this);
            var nonce = btn.data('nonce');
            
            btn.prop('disabled', true).text(geoBotSettings.generating);
            
            $.ajax({
                url: geoBotSettings.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'geo_bot_generate_api_key',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#geo_bot_monitor_api_key').val(response.data.key);
                        $('#copy-api-key').prop('disabled', false);
                    } else {
                        alert(geoBotSettings.errorGenerate);
                    }
                },
                error: function() {
                    alert(geoBotSettings.errorConnection);
                },
                complete: function() {
                    btn.prop('disabled', false).text(geoBotSettings.generateNew);
                }
            });
        });

        $('#copy-api-key').on('click', function() {
            var key = $('#geo_bot_monitor_api_key').val();
            var btn = $('#copy-api-key');
            var originalText = btn.text();
            
            copyToClipboard(key).then(function() {
                btn.text(geoBotSettings.copied);
                setTimeout(function() {
                    btn.text(originalText);
                }, 2000);
            }).catch(function() {
                alert(geoBotSettings.errorCopy);
            });
        });
    });
})(jQuery);
