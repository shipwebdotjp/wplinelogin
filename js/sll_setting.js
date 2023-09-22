const { __ } = wp.i18n;
jQuery(function ($) {
    $("#stabs").tabs({ active: sll_json['active_tab'] });
    $(".sll-color-picker").each(
        function (index) {
            $(this).wpColorPicker({ defaultColor: $(this).attr("data-default-color") });
        }
    );
    $(document).ready(function () {
        $(".sll-multi-select").multiselect({
            selectedList: 5,
            linkInfo: {
                checkAll: { text: __('Check All', 'linelogin'), title: __('Check All', 'linelogin') },
                uncheckAll: { text: __('UnCheck All', 'linelogin'), title: __('UnCheck All', 'linelogin') }
            },
            noneSelectedText: __('Select options', 'linelogin'),
            selectedText: __('# checked', 'linelogin')
        });

    });
    $(".wrap").tooltip();
});