(function ($) {
    $.fn.kvIconInput = function () {
        var $that = $(this);
        var $id = $that.attr('id');
        var $callbackKey = 'iconInputCallback';
        var container = $('#' + $id + '-kvicon');
        container.find('.iconinput-action-picker').on('click', function(e) {
            $('#iconInputModal').modal();
        });
        container.find('.iconinput-action-remove').on('click', function(e) {
            $that.val('');
        });

        window[$callbackKey] = function(icon) {
            $that.val(icon).trigger('change');
            $('#iconInputModal').modal('hide');
        };

        $modalString = '<div class="modal fade" id="iconInputModal" tabindex="-1" role="dialog" aria-labelledby="iconInputModal">' +
            '<div class="modal-dialog modal-lg" role="document">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '<h4 class="modal-title" id="iconInputModalLabel">图标选择</h4>' +
            '</div>' +
            '<div class="modal-body">' +
            '<iframe src="/system/utility/icon?callback=' + $callbackKey +
            '" frameborder="0" style="width: 100%;height: 100%;"></iframe>' +
            '</div></div></div></div>';

        if (!$('#iconInputModal').length) {
            $(document.body).append($($modalString));
        }

        $('#iconInputModal').on('show.bs.modal', function (e) {
            $(this).find('.modal-dialog').width($(window).width()-120).height($(window).height()-120);
            $(this).find('.modal-body').height($(window).height()-200);
        }).on('hide.bs.modal', function(e) {

        });
    };
})(window.jQuery);